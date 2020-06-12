<?php

namespace Tests\Api;

use Cocolis\Api\Client;

class WebhookClientTest extends CocolisTest
{
  public function testCreate()
  {
    $client = $this->authenticatedClient();
    $webhook = $client->getWebhookClient()->create(['event' => 'ride_published', 'url' => 'https://www.test.com/ride_webhook', 'active' => true]);
    $this->assertNotEmpty($webhook);
    $this->assertInstanceOf('Cocolis\Api\Models\Webhook', $webhook);
    $this->assertNotNull($webhook->id);
    $this->assertEquals($webhook->id, 3);
  }

  public function testUpdate()
  {
    $client = $this->authenticatedClient();
    $webhook = $client->getWebhookClient()->update(['event' => 'offer_accepted', 'url' => 'https://www.test.com/ride_webhook', 'active' => true], '3');
    $this->assertNotEmpty($webhook);
    $this->assertInstanceOf('Cocolis\Api\Models\Webhook', $webhook);
    $this->assertNotNull($webhook->id);
    $this->assertEquals($webhook->id, 3);
    $this->assertEquals($webhook->event, 'offer_accepted');
  }

  public function testGetAll()
  {
    $client = $this->authenticatedClient();
    $webhooks = $client->getWebhookClient()->getAll();
    $this->assertNotEmpty($webhooks);
    $this->assertCount(1, $webhooks);
    $this->assertInstanceOf('Cocolis\Api\Models\Webhook', $webhooks[0]);
  }

  public function testGet()
  {
    $client = $this->authenticatedClient();
    $webhook = $client->getWebhookClient()->get('3');
    $this->assertInstanceOf('Cocolis\Api\Models\Webhook', $webhook);
  }

  public function testRemove()
  {
    $client = $this->authenticatedClient();
    $result = $client->getWebhookClient()->remove('3');
    $this->assertEmpty($result);
  }
}
