<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    private $client = null;
    private $container = null; 

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = static::getContainer();
    }
    
    public function testVisitingWhileLoggedIn(): void
    {
        $userRepository = $this->container->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);

        $this->client->loginUser($testUser);

        $this->client->request('GET', $this->container->get('router')->generate('app_profile_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello Jane!');
    }

    public function testVisitingWhenNotLoggedIn(): void
    {
        $this->client->request('GET', $this->container->get('router')->generate('app_profile_index'));
        $this->client->followRedirect();
        $this->assertRouteSame('app_login');
    }
}