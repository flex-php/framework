<?php

namespace tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PageControllerTest extends WebTestCase
{
    public function testSimpleLayouts(): void
    {
        $client = self::createClient();

        $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorExists("body");
        $this->assertSelectorExists("main.main");
        $this->assertSelectorExists("main.main > h1");
        $this->assertEquals("Home", $client->getCrawler()->filter("main.main > h1")->text());

        $client->request('GET', '/about');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorExists("body");
        $this->assertSelectorExists("main.main");
        $this->assertSelectorExists("section.about");
        $this->assertSelectorExists("main.main > section.about > h1");
        $this->assertEquals("About Us", $client->getCrawler()->filter("main.main > section.about > h1")->text());
    }

    public function testParamsPages(): void
    {
        $client = self::createClient();

        $client->request('GET', '/partner/John');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorExists("body");
        $this->assertSelectorExists("#partnerName");
        $this->assertEquals("John", $client->getCrawler()->filter("#partnerName")->text());
    }

    public function testMiddlewares(): void
    {
        $client = self::createClient();

        $client->followRedirects();
        $client->request('GET', '/admin/dashboard');
        $this->assertEquals("/auth/login", $client->getRequest()->getPathInfo());
    }

}