<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProfileTest extends WebTestCase
{
    /* ---------------- properties ---------------- */
    private KernelBrowser $client;
    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $entityManager;
    private User $user;

    /* ---------------- setup ---------------- */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $this->urlGenerator = $container->get('router');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
        $this->entityManager = $entityManager;

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        $this->user = $user;
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
        $this->client->loginUser($this->user);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_profile_index')
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello '.$this->user->getUsername().'!');
    }

    /* ---------------- teardown ---------------- */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
