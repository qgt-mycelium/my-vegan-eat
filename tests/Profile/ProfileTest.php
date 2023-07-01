<?php

namespace App\Tests\Profile;

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
            $this->urlGenerator->generate('app_profile')
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertEquals(
            $this->urlGenerator->generate('app_login'),
            $this->client->getRequest()->server->get('REQUEST_URI')
        );
    }

    // test profile page is accessible and it's working when logged in
    public function testProfileWorksWhenLoggedIn(): void
    {
        $this->client->loginUser($this->user);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_profile')
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello '.$this->user->getUsername().'!');
    }

    // test can account information
    public function testCanUpdateAccountInformation(): void
    {
        $this->client->loginUser($this->user);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_profile')
        );

        $this->assertSelectorExists('form[name="profile"]');
        $form = $crawler->filter('form[name="profile"]')->form([
            'profile[username]' => 'new_username',
            'profile[email]' => 'new_email@myveganeat.com',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects($this->urlGenerator->generate('app_profile'));
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div[role="alert"]', 'Your profile has been updated!');
    }

    // test can't change password when old password is wrong
    public function testCantChangePasswordWhenOldPasswordIsWrong(): void
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test_change_password']);
        $this->client->loginUser($user);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_profile')
        );

        $this->assertSelectorExists('form[name="change_password"]');
        $form = $crawler->filter('form[name="change_password"]')->form([
            'change_password[oldPassword]' => 'wrong_password',
            'change_password[newPassword][first]' => 'new_password',
            'change_password[newPassword][second]' => 'new_password',
        ]);

        $this->client->submit($form);
        $this->assertSelectorTextContains('form[name="change_password"]', 'Wrong value for your current password');
    }

    // test can change password
    public function testCanChangePassword(): void
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'test_change_password']);
        $this->client->loginUser($user);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_profile')
        );

        $this->assertSelectorExists('form[name="change_password"]');
        $form = $crawler->filter('form[name="change_password"]')->form([
            'change_password[oldPassword]' => 'password',
            'change_password[newPassword][first]' => 'new_password',
            'change_password[newPassword][second]' => 'new_password',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects($this->urlGenerator->generate('app_profile'));
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div[role="alert"]', 'Your password has been updated!');
    }

    /* ---------------- teardown ---------------- */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
