<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProfileTest extends WebTestCase
{
    /* ---------------- properties ---------------- */
    private KernelBrowser $client;
    private UrlGeneratorInterface $urlGenerator;
    private $entityManager; /* @phpstan-ignore-line */

    /* ---------------- setup ---------------- */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $this->urlGenerator = $container->get('router');
        $this->entityManager = $container->get('doctrine')->getManager();
    }
    
    /* ---------------- tests ---------------- */

    // test profile page is redirecting to login page when not logged in
    public function testProfileRedirectsWhenNotLoggedIn(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_profile_index')
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertRouteSame('app_login');
    }

    // test profile page is accessible and it's working when logged in
    public function testProfileWorksWhenLoggedIn(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);

        $this->client->loginUser($user);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_profile_index')
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello Jane!');
    }

    /* ---------------- teardown ---------------- */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}