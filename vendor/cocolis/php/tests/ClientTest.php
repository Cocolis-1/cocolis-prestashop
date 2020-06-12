<?php

namespace Tests\Api;

use Cocolis\Api\Client;
use InvalidArgumentException;
use Tests\Api\CocolisTest;

class ClientTest extends CocolisTest
{
  public function testEmptyClient()
  {
    $client = Client::getClient(array(
      'app_id' => 'e0611906',
      'password' => 'sebfie',
      'live' => false
    ));
    $this->assertNotEmpty($client);
  }


  public function testContent()
  {
    $this->assertEquals('https://sandbox-api.cocolis.fr/api/v1/', Client::API_SANDBOX);
    $this->assertEquals('https://api.cocolis.fr/api/v1/', Client::API_PROD);
  }

  public function testClient()
  {
    $client = Client::create(array(
      'app_id' => 'e0611906',
      'password' => 'sebfie',
      'live' => false
    ));
    $this->assertNotEmpty($client);
  }


  public function testSignInWithWrongCredentials()
  {
    $this->expectException(\GuzzleHttp\Exception\ClientException::class);

    $client = Client::create(array(
      'app_id' => 'e0611906',
      'password' => 'notsebfie',
      'live' => false
    ));
    $result = $client->signIn();
    $this->assertEquals($result, false);
  }


  public function testStaticCreate()
  {
    $client = Client::create(array(
      'app_id' => 'e0611906',
      'password' => 'sebfie',
      'live' => false
    ));
    $result = $client->signIn();
    $this->assertEquals(array(
      'access-token' => 'pMoVproO2Zky5ts0uV2EBQ',
      'client' => 'OtCOLIScZXQX50rfO2WL1A',
      'expiry' => '1591002082',
      'uid' => 'e0611906'
    ), $result);
  }

  public function testAppIdException()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Key app_id is missing');
    Client::create(array(
      'app_id' => '',
      'password' => 'test'
    ));
  }

  public function testAuthInfo()
  {
    $this->assertEquals(array(
      'access-token' => 'pMoVproO2Zky5ts0uV2EBQ',
      'client' => 'OtCOLIScZXQX50rfO2WL1A',
      'expiry' => '1591002082',
      'uid' => 'e0611906'
    ), Client::getCurrentAuthInfo());
  }

  public function testPasswordException()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Key password is missing');
    Client::create(array(
      'app_id' => 'test',
      'password' => ''
    ));
  }

  public function testTokenValid()
  {
    $client = Client::create(array(
      'app_id' => 'e0611906',
      'password' => 'sebfie',
      'live' => false
    ));
    $result = $client->validateToken(["uid" => "e0611906", "access-token" => "Jy64iEiJ4vUgtp8TqhkTkQ", "client" => "HLSmEW1TIDqsSMiwuKjnQg", "expiry" => "1590748027"]);
    if (gettype($result) == 'boolean') {
      $this->assertEquals($result, false);
    } else {
      $this->assertNotEmpty($result);
    }
  }

  public function testTokenNoArgs()
  {
    $client = Client::create(array(
      'app_id' => 'e0611906',
      'password' => 'sebfie',
      'live' => false
    ));
    // Clear auth
    $client->setAuth(null);

    // No arguments
    $this->expectException(InvalidArgumentException::class);
    $client->validateToken();
  }

  public function testTokenInvalid()
  {
    $client = Client::create(array(
      'app_id' => 'e0611906',
      'password' => 'sebfie',
      'live' => false
    ));

    $this->expectException(\GuzzleHttp\Exception\ClientException::class);

    // Invalid params
    $result = $client->validateToken(["uid" => "e0611906", "access-token" => "thisisnotavalidtoken", "client" => "HLSmEW1TIDqsSMiwuKjnQg", "expiry" => "1590748027"]);
    $this->assertEquals($result, false);
  }

  public function testTokenNoAuth()
  {
    $client = Client::create(array(
      'app_id' => 'e0611906',
      'password' => 'sebfie',
      'live' => false
    ));
    $client->signIn();
    $result = $client->validateToken();
    $this->assertNotEmpty($result);
  }
}
