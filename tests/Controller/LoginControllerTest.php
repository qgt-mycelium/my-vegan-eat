<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    private $client = null;
    private $container = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = static::getContainer();
    }

    public function testLoginWithBadCredentials(): void
    {
        $crawler = $this->client->request('GET', $this->container->get('router')->generate('app_login'));

        $form = $crawler->filter('button[type="submit"]')->form([
            '_username' => 'jane.doe@exemple.com',
            '_password' => 'password',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertRouteSame('app_login');
        $this->assertSelectorTextContains('body', 'Invalid credentials.');
    }

    public function testLoginWithInvalidCsrfToken(): void
    {
        $crawler = $this->client->request('GET', $this->container->get('router')->generate('app_login'));

        $form = $crawler->filter('button[type="submit"]')->form([
            '_username' => 'jane.doe@example.com',
            '_password' => 'password',
            '_csrf_token' => ''
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertRouteSame('app_login');
        $this->assertSelectorTextContains('body', 'Invalid CSRF token.');
    }

    public function testLoginComplete(): void
    {
        $crawler = $this->client->request('GET', $this->container->get('router')->generate('app_login'));

        $form = $crawler->filter('button[type="submit"]')->form([
            '_username' => 'jane.doe@example.com',
            '_password' => 'password',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertNotNull($this->container->get('security.token_storage')->getToken());
    }

    public function testVisitingWhileLoggedIn(): void
    {
        $userRepository = $this->container->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);
        $this->client->loginUser($testUser);
        $this->client->request('GET', $this->container->get('router')->generate('app_login'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testLogout(): void
    {
        $userRepository = $this->container->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);
        $this->client->loginUser($testUser);
        $this->assertNotNull($this->container->get('security.token_storage')->getToken());
        $this->client->request('GET', $this->container->get('router')->generate('app_logout'));
        $this->assertNull($this->container->get('security.token_storage')->getToken());
    }
}
