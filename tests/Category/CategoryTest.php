<?php

namespace App\Tests\Category;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CategoryTest extends WebTestCase
{
    /* ---------------- properties ---------------- */
    private KernelBrowser $client;
    private UrlGeneratorInterface $urlGenerator;

    /* ---------------- setup ---------------- */
    public function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $this->urlGenerator = $container->get('router');
    }

    /* ---------------- tests ---------------- */

    public function testCategoriesPageIsUp(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_categories')
        );

        $this->assertResponseIsSuccessful();
    }

    public function testCategoryPageIsUp(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_category', ['slug' => 'recipes'])
        );

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('.container a[href*="/categories/"]')->count());
    }

    public function testCategoryPageIsUpWithNonExistentCategory(): void
    {
        $crawler = $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('app_category', ['slug' => 'non-existent-category'])
        );

        $this->assertResponseStatusCodeSame(404);
    }

    /* ---------------- teardown ---------------- */
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
