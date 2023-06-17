<?php

namespace App\Tests\Controller\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginTest extends WebTestCase
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

    // test login page is accessible and it's working
    public function testLoginWorks(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_login')
        );

        $form = $crawler->filter('form[name="login"]')->form([
            '_username' => 'jane.doe@example.com',
            '_password' => 'password',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertNotNull($this->getTokenStorage()->getToken());
    }

    // test logout page is accessible and it's working
    public function testLogoutWorks(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);

        $this->client->loginUser($user);

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
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);

        $this->client->loginUser($user);

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_login')
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /* ---------------- tests with data providers ---------------- */

    /**
     * @dataProvider provideSelectors
     * @param array<string, string> $data
     * @param string $text
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
            'Invalid credentials.'
        ];
        yield [
            ['_csrf_token' => ''],
            'Invalid CSRF token.'
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
        $this->entityManager = null;
    }
}
