<?php

namespace App\Tests\Post;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommentTest extends WebTestCase
{
    /* ---------------- properties ---------------- */
    private KernelBrowser $client;
    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $entityManager;
    private User $user;

    /* ---------------- setup ---------------- */
    public function setUp(): void
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

    // test if it's possible to add a comment to a post
    public function testAddCommentToPost(): void
    {
        $this->client->loginUser($this->user);

        /** @var Post $post */
        $post = $this->entityManager->getRepository(Post::class)->findOneBy([]);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_post', ['slug' => $post->getSlug()])
        );

        $form = $crawler->filter('form[name="comment"]')->form([
            'comment[content]' => 'This is a test comment',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'Your comment has been saved. It will be subject to moderation as soon as possible.');
    }

    // test if it's possible to add a comment to a post without being logged in
    public function testAddCommentToPostWithoutBeingLoggedIn(): void
    {
        /** @var Post $post */
        $post = $this->entityManager->getRepository(Post::class)->findOneBy([]);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_post', ['slug' => $post->getSlug()])
        );

        $this->assertSelectorNotExists('form[name="comment"]');
    }

    // test if it's possible to add a comment to a post without content
    public function testAddCommentToPostWithoutContent(): void
    {
        $this->client->loginUser($this->user);

        /** @var Post $post */
        $post = $this->entityManager->getRepository(Post::class)->findOneBy([]);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_post', ['slug' => $post->getSlug()])
        );

        $form = $crawler->filter('form[name="comment"]')->form([
            'comment[content]' => '',
        ]);

        $this->client->submit($form);

        $this->assertSelectorTextContains('body', 'Your comment could not be saved. Please check the form.');
    }

    // test if it's possible to delete a comment
    public function testDeleteComment(): void
    {
        $this->client->loginUser($this->user);

        /** @var Post $post */
        $post = current($this->entityManager->getRepository(Post::class)->findPopularOrderedByMostLiked());

        $comment = (new Comment())
            ->setContent('This is a test comment')
            ->setAuthor($this->user)
            ->setPost($post)
            ->setIsPublished(true)
        ;

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_post', ['slug' => $post->getSlug()])
        );

        $form = $crawler->filter('form[name="comment_delete"]')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'Your comment has been successfully deleted');
    }
}
