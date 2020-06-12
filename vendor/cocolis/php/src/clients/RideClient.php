<?php

/**
 * Cocolis API Class
 * API Documentation: https://doc.cocolis.fr
 * Class Documentation: https://github.com/Cocolis-1/cocolis-php/
 *
 * @author Cocolis
 */

namespace Cocolis\Api\Clients;

use Exception;

class RideClient extends AbstractClient
{
  public $_rest_path = 'rides';
  public $_model_class = 'Cocolis\Api\Models\Ride';

  public function mine()
  {
    return $this->hydrate(json_decode($this->getCocolisClient()->callAuthentificated($this->getRestPath('mine'))->getBody(), true)["rides"]);
  }

  public function canMatch(string $zipfrom, string $zipto, float $volume, int $value = null)
  {
    $params = ['from' => ['postal_code' => $zipfrom], 'to' => ['postal_code' => $zipto], 'volume' => $volume];
    if (!empty($value)) {
      $params['content_value'] = $value;
    }

    return $this->hydrate(json_decode(
      $this->getCocolisClient()->callAuthentificated(
        $this->getRestPath('can_match'),
        'POST',
        $params
      )->getBody(),
      true
    ), false);
  }

  public function remove(string $id)
  {
    return $this->notSupported();
  }

  public function update(array $params, string $id)
  {
    return $this->notSupported();
  }

  public function getAll()
  {
    return $this->notSupported();
  }
}
