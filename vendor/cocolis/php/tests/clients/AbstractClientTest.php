<?php

namespace Tests\Api;

use Cocolis\Api\Client;
use Exception;

class AbstractClientTest extends CocolisTest
{
  public function testHydrateArray()
  {
    $client = new Client();
    $rides = $client->getRideClient()->hydrate(array(
      array("name" => 'toto'),
      array("name" => 'tata')
    ));
    $this->assertCount(2, $rides);
    $this->assertInstanceOf('Cocolis\Api\Models\Ride', $rides[0]);
  }

  public function testHydrateHash()
  {
    $client = new Client();
    $ride = $client->getRideClient()->hydrate(array(
     "name" => "toto"
    ));
    $this->assertInstanceOf('Cocolis\Api\Models\Ride', $ride);
  }
}
