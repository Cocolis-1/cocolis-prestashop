<?php

/**
 * Cocolis API Class
 * API Documentation: https://doc.cocolis.fr
 * Class Documentation: https://github.com/Cocolis-1/cocolis-php/
 *
 * @author Cocolis
 */

namespace Cocolis\Api\Clients;

use Cocolis\Api\Clients\AbstractClient;

class WebhookClient extends AbstractClient
{
  public $_rest_path = 'applications/webhooks';
  public $_model_class = 'Cocolis\Api\Models\Webhook';
}
