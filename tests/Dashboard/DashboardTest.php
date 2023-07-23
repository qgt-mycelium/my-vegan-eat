<?php

namespace App\Tests\Dashboard;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DashboardTest extends WebTestCase
{
    /* ---------------- properties ---------------- */
    private KernelBrowser $client;
    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $entityManager;

    /* ---------------- setup ---------------- */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $this->urlGenerator = $container->get('router');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
        $this->entityManager = $entityManager;
    }

    /* ---------------- tests ---------------- */

    // test dashboard page is redirecting to login page when not logged in
    public function testDashboardRedirectsWhenNotLoggedIn(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_dashboard')
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertEquals(
            $this->urlGenerator->generate('app_login'),
            $this->client->getRequest()->server->get('REQUEST_URI')
        );
    }

    // test dashboard page is forbidden when logged in as user (not admin)
    public function testDashboardForbiddenWhenLoggedInAsUser(): void
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test_password']);

        $this->client->loginUser($user);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_dashboard')
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    // test dashboard page is accessible and it's working when logged in as admin
    public function testDashboardWorksWhenLoggedInAsAdmin(): void
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test_admin']);

        $this->client->loginUser($user);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_dashboard')
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
