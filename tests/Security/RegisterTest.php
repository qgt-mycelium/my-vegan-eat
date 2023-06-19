<?php

namespace App\Tests\Controller\Security;

use Faker\Factory;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegisterTest extends WebTestCase
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

    // test register page is accessible and it's working
    public function testRegisterWorks(): void
    {
        $faker = Factory::create();
        $email = $faker->email();
        $password = $faker->password();

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_register')
        );

        $form = $crawler->filter('form[name="registration"]')->form([
            'registration[email]' => $email,
            'registration[username]' => $faker->userName(),
            'registration[password][first]' => $password,
            'registration[password][second]' => $password,
        ]);

        $this->client->submit($form);

        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => $email]);

        $this->assertNotNull($user);
        $this->client->followRedirect();
        $this->assertEquals(
            $this->urlGenerator->generate('app_login'),
            $this->client->getRequest()->server->get('REQUEST_URI')
        );
        $this->assertSelectorTextContains('body', 'Your account has been created! You can now log in.');
    }

    // test visiting register page while logged in
    public function testVisitingWhileLoggedIn(): void
    {
        $this->client->loginUser($this->user);

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_register')
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
            $this->urlGenerator->generate('app_register')
        );

        $form = $crawler->filter('form[name="registration"]')->form($data);

        $this->client->submit($form);
        $this->assertSelectorTextContains('form', $text);
    }

    public function provideSelectors(): \Generator
    {
        yield [
            ['registration[password][first]' => 'password'],
            'The values do not match. Repeat Password Register!',
        ];
        yield [
            [
                'registration[password][first]' => 'pass',
                'registration[password][second]' => 'pass',
            ],
            'This value is too short. It should have 6 characters or more.',
        ];
    }

    /* ---------------- teardown ---------------- */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
