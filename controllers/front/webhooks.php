<?php
class CocolisWebhooksModuleFrontController extends ModuleFrontController
{
    protected $maintenance = false;

    public function initContent()
    {
    }

    public function postProcess()
    {
        $orderid = Tools::getValue('external_id');
        $event = Tools::getValue('event');

        if (empty($event) || empty($orderid)) {
            echo('Event or order ID missing from Webhook');
            exit;
        }

        $history = new OrderHistory();
        $history->id_order = $orderid;

        switch ($event) {
            case 'offer_accepted':
                $history->changeIdOrderState((int) 4, $orderid); // Expédié
                $history->addWithemail(true);
            break;

            case 'offer_completed':
                $history->changeIdOrderState((int) 5, $orderid); // Livré
                $history->addWithemail(true);
            break;
        }

        Logger::addLog('Webhook ' . $event . ' intercepté par le module Cocolis pour la commande ' . $orderid);
        Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."cocolis_order_history` (`order_id`, `comment`, `created_at`, `webhook_params`) VALUES (". (int) $orderid .", '" . $event .  "', NOW(), '" . json_encode(Tools::getAllValues()) . "')");
        exit;
    }
}
