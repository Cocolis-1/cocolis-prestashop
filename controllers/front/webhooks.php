<?php
class CocolisWebhooksModuleFrontController extends ModuleFrontController
{
    protected $maintenance = false;

    public function initContent()
    {
    }

    public function postProcess()
    {
<<<<<<< HEAD
        switch (Tools::getValue('event')) {
      case 'ride_published':
        echo('Status changé !');
    }
=======
        $orderid = Tools::getValue('external_id');

        $history = new OrderHistory();
        $history->id_order = $orderid;

        switch (Tools::getValue('event')) {
            case 'ride_published':
                if(empty(Tools::getValue('external_id'))){
                    echo('ID missing for order');
                    exit;
                }
                
                $comment = 'Annonce publiée sur Cocolis.';

                Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."cocolis_order_history` (`order_id`, `comment`, `created_at`, `webhook_params`) VALUES (". (int) $orderid .", '" . $comment .  "', NOW(), '" . json_encode(Tools::getAllValues()) . "')");
                //$history->changeIdOrderState((int) 20, $orderid);
                Logger::addLog('Webhook ride_published intercepté par le module Cocolis pour la commande ' . $orderid);
                
            break;
        }
>>>>>>> 6b7ab4c... Tracking modifications & improvements
        exit;
    }
}
