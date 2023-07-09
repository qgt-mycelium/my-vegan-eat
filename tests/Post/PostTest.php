<?php

namespace App\Tests\Post;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostTest extends WebTestCase
{
    /* ---------------- properties ---------------- */
    private KernelBrowser $client;
    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $entityManager;

    /* ---------------- setup ---------------- */
    public function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $this->urlGenerator = $container->get('router');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
        $this->entityManager = $entityManager;
    }

    /* ---------------- tests ---------------- */

    public function testPostsPageIsUp(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_posts')
        );

        $this->assertResponseIsSuccessful();
    }

    public function testPostPageIsUp(): void
    {
        /** @var Post $post */
        $post = $this->entityManager->getRepository(Post::class)->findOneBy([]);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_post', ['slug' => $post->getSlug()])
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $post->getTitle());
    }

    public function testPostPageIsUpWithNonExistentPost(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_post', ['slug' => 'non-existent-post'])
        );

        $this->assertResponseStatusCodeSame(404);
    }

    /* ---------------- teardown ---------------- */
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
