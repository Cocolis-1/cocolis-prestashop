<?php

/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

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
                echo json_encode(['success' => true]);
                break;

            case 'offer_completed':
                $history->changeIdOrderState((int) 5, $orderid); // Livré
                $history->addWithemail(true);
                echo json_encode(['success' => true]);
                break;
        }

        Logger::addLog('Webhook ' . $event . ' intercepté par le module Cocolis pour la commande ' . $orderid);
        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "cocolis_order_history` 
            (`order_id`, `comment`, `created_at`, `webhook_params`) 
            VALUES (" . (int) $orderid . ", '" . $event .  "', NOW(), '" . json_encode(Tools::getAllValues()) . "')");
        exit;
    }
}
