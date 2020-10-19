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


require("vendor/autoload.php");

use Cocolis\Api\Client;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Cocolis extends CarrierModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'cocolis';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'Cocolis';
        $this->need_instance = 1;
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Cocolis');
        $this->description = $this->l('Utilisez cocolis.fr comme mode de livraison. Spécialisé dans la livraison communautaire, cocolis vous permettra d\'envoyer des colis hors format.');

        $this->confirmUninstall = $this->l('Notre service de livraison ne sera plus disponible sur votre site.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */

    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $carrier = $this->addCarrier();
        $this->addZones($carrier);
        $this->addGroups($carrier);
        $this->addRanges($carrier);
        Configuration::updateValue('COCOLIS_LIVE_MODE', false);
        Configuration::updateValue('COCOLIS_VOLUME', 0.5);
        Configuration::updateValue('COCOLIS_HEIGHT', 50);
        Configuration::updateValue('COCOLIS_WIDTH', 100);
        Configuration::updateValue('COCOLIS_LENGTH', 100);

        Configuration::updateValue('COCOLIS_ADDRESS', 'Renseigner une adresse');
        Configuration::updateValue('COCOLIS_ZIP', 75000);
        Configuration::updateValue('COCOLIS_CITY', "Paris");
        Configuration::updateValue('COCOLIS_COUNTRY', "France");

        Configuration::updateValue('COCOLIS_CARRIER_ID', $carrier->id_reference);
        include(dirname(__FILE__) . '/sql/install.php');


        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('updateCarrier') &&
            $this->registerHook('actionPaymentConfirmation') &&
            $this->registerHook('moduleRoutes') &&
            $this->registerHook('displayAdminOrderContentShip') &&
            $this->registerHook('displayAdminOrderContentOrder') &&
            $this->registerHook('displayAdminOrderTabShip') &&
            $this->registerHook('displayAdminOrderTabOrder') &&
            $this->registerHook('displayAdminOrder');
    }

    public function uninstall()
    {
        Configuration::deleteByName('COCOLIS_LIVE_MODE');
        Configuration::deleteByName('COCOLIS_CARRIER_ID');
        Configuration::deleteByName('COCOLIS_WEBHOOK_ID');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
    * Redirects to redirect_after link.
    *
    * @see $redirect_after
    */
    protected function redirect()
    {
        Tools::redirectLink($this->redirect_after);
    }

    public function redirectWithNotifications($type)
    {
        $this->context->smarty->assign(array('notifications' => 'webhook_' . $type));
        return $this->display(__FILE__, "views/templates/admin/configure.tpl");
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */

        if (((bool)Tools::isSubmit('webhooks')) == true) {
            $client = $this->authenticatedClient();
            if (!Configuration::get('COCOLIS_WEBHOOK_ID')) {
                $id_webhooks = [];
                $webhook = $client->getWebhookClient()->create(['webhook' => ['event' => 'ride_published', 'url' => Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=ride_published', 'active' => true]]);
                array_push($id_webhooks, $webhook->id);
                $webhook = $client->getWebhookClient()->create(['webhook' => ['event' => 'ride_expired', 'url' => Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=ride_expired', 'active' => true]]);
                array_push($id_webhooks, $webhook->id);
                $webhook = $client->getWebhookClient()->create(['webhook' => ['event' => 'offer_accepted', 'url' => Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=offer_accepted', 'active' => true]]);
                array_push($id_webhooks, $webhook->id);
                $webhook = $client->getWebhookClient()->create(['webhook' => ['event' => 'offer_cancelled', 'url' => Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=offer_cancelled', 'active' => true]]);
                array_push($id_webhooks, $webhook->id);
                $webhook = $client->getWebhookClient()->create(['webhook' => ['event' => 'offer_completed', 'url' => Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=offer_completed', 'active' => true]]);
                array_push($id_webhooks, $webhook->id);
                Configuration::updateValue('COCOLIS_WEBHOOK_ID', serialize($id_webhooks));
                $this->redirectWithNotifications('success');
            } elseif ($client->getWebhookClient()->get(unserialize(Configuration::get('COCOLIS_WEBHOOK_ID'))[0])->url != Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=ride_published') {
                $config = unserialize(Configuration::get('COCOLIS_WEBHOOK_ID'));
                $client->getWebhookClient()->update(['webhook' => ['event' => 'ride_published', 'url' => Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=ride_published', 'active' => true]], $config[0]);
                $client->getWebhookClient()->update(['webhook' => ['event' => 'ride_expired', 'url' => Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=ride_expired', 'active' => true]], $config[1]);
                $client->getWebhookClient()->update(['webhook' => ['event' => 'offer_accepted', 'url' => Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=offer_accepted', 'active' => true]], $config[2]);
                $client->getWebhookClient()->update(['webhook' => ['event' => 'offer_cancelled', 'url' => Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=offer_cancelled', 'active' => true]], $config[3]);
                $client->getWebhookClient()->update(['webhook' => ['event' => 'offer_completed', 'url' => Tools::getShopDomainSsl(true) . '/cocolis/webhooks?event=offer_completed', 'active' => true]], $config[4]);
                $this->redirectWithNotifications('updated');
            } else {
                $this->redirectWithNotifications('already');
            }
        } elseif (((bool)Tools::isSubmit('submitCocolisModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCocolisModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Paramètres'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Mode production'),
                        'name' => 'COCOLIS_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Utiliser ce module en mode développement (sandbox) ou production ?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Activer le mode production')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Désactiver le mode production')
                            )
                        ),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-move"></i>',
                        'name' => 'COCOLIS_VOLUME',
                        'label' => $this->l('Volume moyen'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_WIDTH',
                        'label' => $this->l('Largeur moyenne'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_HEIGHT',
                        'label' => $this->l('Hauteur moyenne'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_LENGTH',
                        'label' => $this->l('Longueur moyenne'),
                        'desc' => $this->l("Permet de calculer les frais en l'absence du volume renseigné dans la fiche produit")
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'COCOLIS_ADDRESS',
                        'label' => $this->l('Votre adresse'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_ZIP',
                        'label' => $this->l('Votre code postal'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_CITY',
                        'label' => $this->l('Votre ville'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_COUNTRY',
                        'label' => $this->l('Votre pays'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-terminal"></i>',
                        'desc' => $this->l("Entrez l'app-id qui vous a été fourni"),
                        'name' => 'COCOLIS_APPID',
                        'label' => $this->l("ID de l'application"),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'COCOLIS_PASSWORD',
                        'label' => $this->l('Password'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Enregister'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'COCOLIS_LIVE_MODE' => Configuration::get('COCOLIS_LIVE_MODE', true),
            'COCOLIS_APPID' => Configuration::get('COCOLIS_APPID', 'app_id'),
            'COCOLIS_PASSWORD' => Configuration::get('COCOLIS_PASSWORD', null),
            'COCOLIS_ZIP' => Configuration::get('COCOLIS_ZIP', null),
            'COCOLIS_VOLUME' => Configuration::get('COCOLIS_VOLUME', 0.25),
            'COCOLIS_HEIGHT' => Configuration::get('COCOLIS_HEIGHT', null),
            'COCOLIS_WIDTH' => Configuration::get('COCOLIS_WIDTH', null),
            'COCOLIS_LENGTH' => Configuration::get('COCOLIS_LENGTH', null),
            'COCOLIS_ADDRESS' => Configuration::get('COCOLIS_ADDRESS', null),
            'COCOLIS_CITY' => Configuration::get('COCOLIS_CITY', null),
            'COCOLIS_COUNTRY' => Configuration::get('COCOLIS_COUNTRY', null)
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        $dimensions = 0;
        $total = 0;
        if (Context::getContext()->customer->logged == true) {
            $id_address_delivery = Context::getContext()->cart->id_address_delivery;
            $address = new Address($id_address_delivery);

            $from_zip = Configuration::get('COCOLIS_ZIP');

            if (!is_null($address->postcode)) {
                $to_zip = $address->postcode;

                /**
                 * Send the details through the API
                 * Return the price sent by the API
                 */

                $client = $this->authenticatedClient();

                $products = Context::getContext()->cart->getProducts();
                foreach ($products as $product) {
                    $width = (int) $product['width'];
                    $depth = (int) $product['depth'];
                    $height = (int) $product['height'];

                    if ($width == 0 || $depth == 0 || $height == 0) {
                        $dimensions += Configuration::get('COCOLIS_VOLUME') * (int) $product['quantity'];
                    } // Use the default value of volume for delivery fees
                    else {
                        $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['quantity'];
                    }

                    $total += $product['price'] * (int) $product['quantity'];
                }

                if ($dimensions < 0.01) {
                    $dimensions += 0.01;
                }

                $dimensions = round($dimensions, 2);

                try {
                    $match = $client->getRideClient()->canMatch($from_zip, $to_zip, $dimensions, $total);
                } catch (GuzzleHttp\Exception\ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();
                    var_dump($responseBodyAsString);
                    exit;
                }
                $shipping_cost = ($match->estimated_prices->regular) / 100;
            }
        }

        return $shipping_cost;
    }

    public function authenticatedClient()
    {
        $client = Client::create(array(
            'app_id' => Configuration::get('COCOLIS_APPID'),
            'password' => Configuration::get('COCOLIS_PASSWORD'),
            'live' => Configuration::get('COCOLIS_LIVE_MODE')
        ));
        $client->signIn();
        return $client;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    /**
     * Onglet de suivi Cocolis
     */

    public function hookDisplayAdminOrderTabShip($params)
    {
        $order = $params['order'];
        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
        $carrier = new Carrier($orderCarrier->id_carrier);

        if ($carrier->external_module_name == "cocolis") {
            return $this->display(__FILE__, '/views/templates/hook/tab_ship.tpl');
        }
    }
    public function hookDisplayAdminOrderContentShip($params)
    {
        $order = $params['order'];
        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
        $carrier = new Carrier($orderCarrier->id_carrier);

        if ($carrier->external_module_name == "cocolis") {
            return $this->display(__FILE__, '/views/templates/hook/content_ship.tpl');
        }
    }

    public function hookActionPaymentConfirmation($params)
    {
        $id_order = (int) $params['id_order'];
        $order = new Order($id_order);
        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
        $carrier = new Carrier($orderCarrier->id_carrier);

        $client = $this->authenticatedClient();

        if ($carrier->external_module_name == "cocolis") {
            $address = new Address($order->id_address_delivery);
            $composed_address = $address->address1 . ', ' . $address->postcode . ' ' . $address->city;

            $from_date = new DateTime('NOW');
            $from_date->setTimeZone(new DateTimeZone("Europe/Paris"));

            $to_date = new DateTime('NOW');
            $to_date  = $to_date->add(new DateInterval('P21D'));
            $to_date->setTimeZone(new DateTimeZone("Europe/Paris"));

            $from_date = $from_date->format('c');
            $to_date = $to_date->format('c');

            $cart = new Cart($params['cart']->id);
            $products = $cart->getProducts();
            $dimensions = 0;

            $customer = new Customer($cart->id_customer);

            $arrayproducts = [];

            $arrayname = [];

            $phone = Configuration::get('PS_SHOP_PHONE');
            if ($phone == null) {
                echo('<p style="color:red;">[Module Cocolis] <b>Numéro de téléphone portable manquant !</b> Vous devez configurer votre boutique afin de fournir un numéro de téléphone valide. </br>Rendez vous dans <b>Paramètres de la boutique > Contact > Magasins</b> et fournissez un numéro de téléphone <b>portable</b>. La commande reste inchangée.</p>');
                exit;
            }

            foreach ($products as $product) {
                $width = (int) $product['width'];
                $depth = (int) $product['depth'];
                $height = (int) $product['height'];

                if ($width == 0 || $depth == 0 || $height == 0) {
                    $dimensions += Configuration::get('COCOLIS_VOLUME') * (int) $product['quantity'];
                } // Use the default value of volume for delivery fees
                else {
                    $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['quantity'];
                }

                array_push($arrayname, $product['name']);
                array_push($arrayproducts, [
                    "title" => $product['name'],
                    "qty" => $product['cart_quantity'],
                    "height" => 50, //TODO  A CHANGER
                    "width" => 100,
                    "length" => 100,
                ]);
            }


            $params = [
                "description" => "Commande envoyée via module PrestaShop du partenaire",
                "from_address" => "Carcassonne",
                "to_address" => $composed_address,
                "from_lat" => 43.212498, //TODO
                "to_lat" => 43.599120, //TODO
                "from_lng" => 2.350351, //TODO
                "to_lng" => 1.444391, //TODO
                "from_is_flexible" => true,
                "from_pickup_date" => $from_date,
                "to_is_flexible" => true,
                "to_pickup_date" => $to_date,
                "is_passenger" => false,
                "is_packaged" => true,
                "price" => (int) $order->total_shipping_tax_incl * 100,
                "volume" => $dimensions,
                "environment" => "objects",
                "rider_extra_information" => "Bonjour, Je souhaite envoyer les objets suivants : " . implode(", ", $arrayname) . '. Merci !' . " Achat effectué sur une marketplace",
                "photos" => [],
                "ride_objects_attributes" => $arrayproducts,
                "ride_delivery_information_attributes" => [
                    "from_address" => Configuration::get('COCOLIS_ADDRESS'),
                    "from_postal_code" => Configuration::get('COCOLIS_ZIP'),
                    "from_city" => Configuration::get('COCOLIS_CITY'),
                    "from_country" => 'FR', //TODO
                    "from_contact_email" => Configuration::get('PS_SHOP_EMAIL'),
                    "from_contact_phone" => $phone,
                    "from_contact_name" => Configuration::get('PS_SHOP_NAME'),
                    "from_extra_information" => 'Vendeur MarketPlace',
                    "to_address" => $address->address1,
                    "to_postal_code" => $address->postcode,
                    "to_city" => $address->city,
                    "to_country" => 'FR',
                    "to_contact_name" => $customer->firstname . ' ' . $customer->lastname,
                    "to_contact_email" => $customer->email,
                    "to_contact_phone" => $address->phone
                ]
            ];

            $client = $client->getRideClient();
            try {
                $client->create(['ride' => $params]);
            } catch (GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
                $responseBodyAsString = $response->getBody()->getContents();
                var_dump($responseBodyAsString);
                exit;
            }
            //TODO add Ride with 30 minutes delay
        }
    }
    public function hookModuleRoutes($params)
    {
        //URL without Rewrite : http://localhost:8084/index.php?fc=module&module=cocolis&controller=webhooks&id_lang=1
        return array(
            'module-cocolis-webhooks' => array(
                'rule' => 'cocolis/webhooks{/:event}',
                'controller' => 'webhooks',
                'keywords' => array(
                    'event' =>   array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'event'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'cocolis',
                    'controller' => 'webhooks',
                )
            )
        );
    }

    protected function addCarrier()
    {
        $carrier = new Carrier();

        $carrier->name = $this->l('Livraison collaborative Cocolis');
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Délai variable');
        }

        if ($carrier->add() == true) {
            @copy(dirname(__FILE__) . '/views/img/carrier_image.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg');
            Configuration::updateValue('COCOLIS_CARRIER_ID', (int)$carrier->id);
            return $carrier;
        }

        return false;
    }

    protected function addGroups($carrier)
    {
        $groups_ids = array();
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        $carrier->setGroups($groups_ids);
    }

    protected function addRanges($carrier)
    {
        $range_price = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();

        $range_weight = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0';
        $range_weight->delimiter2 = '10000';
        $range_weight->add();
    }

    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }
    }


    public function hookUpdateCarrier($params)
    {
        /**
         * Not needed since 1.5
         * You can identify the carrier by the id_reference
         */
    }
}
