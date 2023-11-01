<?php

namespace tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ActionControllerTest extends WebTestCase
{
    public function testActions(): void {
        $client = self::createClient();

        $client->request('GET', '/auth/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorExists("input[name=username]");
        $client->request('POST', '/auth/login', [
            "username" => "admin"
        ]);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals("/admin", $client->getResponse()->headers->get("location"));
    }
}