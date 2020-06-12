<?php

/**
 * Cocolis API Class
 * API Documentation: https://doc.cocolis.fr
 * Class Documentation: https://github.com/Cocolis-1/cocolis-php/
 *
 * @author Cocolis
 */

namespace Cocolis\Api\Models;

use Cocolis\Api\Clients\AbstractClient;

class Ride extends AbstractModel
{
  public function getBuyerURL()
  {
    return $this->getBaseURL() . 'rides/buyer/' . $this->buyer_tracking;
  }

  public function getSellerURL()
  {
    return $this->getBaseURL() . 'rides/seller/' . $this->seller_tracking;
  }
}
