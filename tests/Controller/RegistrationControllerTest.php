<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private $client = null;
    private $container = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = static::getContainer();
    }

    public function testRegisterComplete(): void
    {
        $crawler = $this->client->request('GET', $this->container->get('router')->generate('app_register'));

        $form = $crawler->filter('button[type="submit"]')->form([
            'registration[email]' => 'john.doe@example.com',
            'registration[username]' => 'John',
            'registration[password][first]' => 'password',
            'registration[password][second]' => 'password',
        ]);

        $this->client->submit($form);
        $userRepository = $this->container->get('doctrine')->getManager()->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'john.doe@example.com']);
        $this->assertNotNull($testUser);
        $this->client->followRedirect();
        $this->assertRouteSame('app_login');
        $this->assertSelectorTextContains('body', 'Your account has been created! You can now log in.');
    }

    public function testVisitingWhileLoggedIn(): void
    {
        $userRepository = $this->container->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);
        $this->client->loginUser($testUser);
        $this->client->request('GET', $this->container->get('router')->generate('app_register'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRegisterWithSameUsername(): void
    {
        $crawler = $this->client->request('GET', $this->container->get('router')->generate('app_register'));

        $form = $crawler->filter('button[type="submit"]')->form([
            'registration[email]' => 'janette.doe@example.com',
            'registration[username]' => 'Jane',
            'registration[password][first]' => 'password',
            'registration[password][second]' => 'password',
        ]);

        $this->client->submit($form);
        $this->assertSelectorTextContains('form', 'This username is already taken.');
    }

    public function testRegisterWithSmallPassword(): void
    {
        $crawler = $this->client->request('GET', $this->container->get('router')->generate('app_register'));

        $form = $crawler->filter('button[type="submit"]')->form([
            'registration[email]' => 'jonathan.doe@example.com',
            'registration[username]' => 'Jonathan',
            'registration[password][first]' => 'pass',
            'registration[password][second]' => 'pass',
        ]);

        $this->client->submit($form);
        $this->assertSelectorTextContains('form', 'This value is too short. It should have 6 characters or more.');
    }
}
