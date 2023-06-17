<?php

namespace App\Tests\Controller\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegisterTest extends WebTestCase
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

    // test register page is accessible and it's working
    public function testRegisterWorks(): void
    {
        $email = 'john.doe@example.com';

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_register')
        );

        $form = $crawler->filter('form[name="registration"]')->form([
            'registration[email]' => $email,
            'registration[username]' => 'John',
            'registration[password][first]' => 'password',
            'registration[password][second]' => 'password',
        ]);

        $this->client->submit($form);

        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => $email]);

        $this->assertNotNull($user);
        $this->client->followRedirect();
        $this->assertRouteSame('app_login');
        $this->assertSelectorTextContains('body', 'Your account has been created! You can now log in.');
    }

    // test visiting register page while logged in
    public function testVisitingWhileLoggedIn(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'jane.doe@example.com']);
        
        $this->client->loginUser($user);
        
        $this->client->request(
            Request::METHOD_GET, 
            $this->urlGenerator->generate('app_register')
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
            $this->urlGenerator->generate('app_register')
        );

        $form = $crawler->filter('form[name="registration"]')->form($data);

        $this->client->submit($form);
        $this->assertSelectorTextContains('form', $text);
    }

    public function provideSelectors(): \Generator
    {
        yield [
            ['registration[username]' => 'Jane'],
            'This username is already taken.'
        ];
        yield [
            ['registration[password][first]' => 'password'],
            'The values do not match. Repeat Password Register!',
        ];
        yield [
            [
                'registration[password][first]' => 'pass',
                'registration[password][second]' => 'pass',
            ],
            'This value is too short. It should have 6 characters or more.'
        ];
    }

    /* ---------------- teardown ---------------- */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
