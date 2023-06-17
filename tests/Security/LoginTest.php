<?php

namespace App\Tests\Controller\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginTest extends WebTestCase
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

    // test login page is accessible and it's working
    public function testLoginWorks(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_login')
        );

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test_password']);

        $form = $crawler->filter('form[name="login"]')->form([
            '_username' => $user->getEmail(),
            '_password' => 'password',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertNotNull($this->getTokenStorage()->getToken());
    }

    // test logout page is accessible and it's working
    public function testLogoutWorks(): void
    {
        $this->client->loginUser($this->user);

        $this->assertNotNull($this->getTokenStorage()->getToken());

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_logout')
        );

        $this->assertNull($this->getTokenStorage()->getToken());
    }

    // test login page is not accessible when logged in
    public function testLoginWhenLoggedIn(): void
    {
        $this->client->loginUser($this->user);

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_login')
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /* ---------------- tests with data providers ---------------- */

    /**
     * @dataProvider provideSelectors
     *
     * @param array<string, string> $data
     */
    public function testAssertSelectorTextContains(array $data, string $text): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_login')
        );

        $form = $crawler->filter('form[name="login"]')->form($data);

        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', $text);
    }

    public function provideSelectors(): \Generator
    {
        yield [
            [],
            'Invalid credentials.',
        ];
        yield [
            ['_csrf_token' => ''],
            'Invalid CSRF token.',
        ];
    }

    /* ---------------- get token storage ---------------- */
    private function getTokenStorage(): TokenStorageInterface
    {
        return $this->client->getContainer()->get('security.token_storage');
    }

    /* ---------------- teardown ---------------- */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
