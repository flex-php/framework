<?php

namespace tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RouteControllerTest extends WebTestCase
{
    public function testSimpleApi(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/methods');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('GET', $client->getResponse()->getContent());

        $client->request('POST', '/api/methods');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('POST', $client->getResponse()->getContent());

        $client->request('PUT', '/api/methods');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('PUT', $client->getResponse()->getContent());

        $client->request('DELETE', '/api/methods');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}