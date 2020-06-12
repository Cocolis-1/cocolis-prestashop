<?php

namespace Tests\Api;

use Cocolis\Api\Client;
use Cocolis\Api\Models\Ride;
use PHPUnit\Framework\Error\Error;

class RideTest extends CocolisTest
{
  public function testGetBuyerURL()
  {
    $client = new Client();
    $model = $client->getRideClient()->get(1)->getBuyerURL();
    $this->assertNotEmpty($model);
  }

  public function testGetSellerURL()
  {
    $client = new Client();
    $model = $client->getRideClient()->get(1)->getSellerURL();
    $this->assertNotEmpty($model);
  }

  public function testObjectException()
  {
    $this->expectError(Error::class);
    $this->expectErrorMessage('Data is no object!');
    $client = new Client();
    $ride = new Ride('toto', $client);
    $ride->my_key;
  }
}
