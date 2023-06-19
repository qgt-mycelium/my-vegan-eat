<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeTest extends WebTestCase
{
    /* ---------------- properties ---------------- */
    private KernelBrowser $client;

    /* ---------------- setup ---------------- */
    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /* ---------------- tests ---------------- */

    public function testHomepageIsUp(): void
    {
        $this->client->request(Request::METHOD_GET, '/');
        $this->assertResponseIsSuccessful();
    }

    public function testHomepageHasNavbar(): void
    {
        $this->client->request(Request::METHOD_GET, '/');
        $this->assertSelectorExists('nav');
    }

    public function testHomepageHasFooter(): void
    {
        $this->client->request(Request::METHOD_GET, '/');
        $this->assertSelectorExists('footer');
    }

    /* ---------------- teardown ---------------- */
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
