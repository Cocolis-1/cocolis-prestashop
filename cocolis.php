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

require_once dirname(__FILE__) . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

use Cocolis\Api\Client;
use PrestaShop\PrestaShop\Core\Checkout\TermsAndConditions;

class Cocolis extends CarrierModule
{
    protected $config_form = false;
    public $id_carrier;

    public static function getName()
    {
        return Configuration::get('PS_SHOP_NAME');
    }

    public static function getAddress()
    {
        return Configuration::get('COCOLIS_ADDRESS');
    }

    public static function getZip()
    {
        return Configuration::get('COCOLIS_ZIP');
    }

    public static function getCity()
    {
        return Configuration::get('COCOLIS_CITY');
    }

    public static function getCountry()
    {
        return "FR";
    }

    public static function getPhone()
    {
        return Configuration::get('PS_SHOP_PHONE');
    }

    public function __construct()
    {
        $this->name = 'cocolis';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.4';
        $this->author = 'Cocolis';
        $this->need_instance = 1;
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();
        $this->displayName = $this->l('Cocolis');
        $this->description = $this->l("Utilisez cocolis.fr comme mode de livraison. 
            Spécialisé dans la livraison communautaire, cocolis vous permettra d'envoyer des colis hors format.");

        $this->confirmUninstall = $this->l('Our delivery service will no longer be available on your site.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
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

        if (is_null(Configuration::get('COCOLIS_CARRIER_ID')) || null == (Configuration::get('COCOLIS_CARRIER_ASSURANCE_ID'))) {
            $carrier = $this->addCarrier();
            $this->addZones($carrier);
            $this->addGroups($carrier);
            $this->addRanges($carrier);
        }

        if (is_null(Configuration::get('COCOLIS_CARRIER_ASSURANCE_ID')) || null == (Configuration::get('COCOLIS_CARRIER_ASSURANCE_ID'))) {
            $carrier_insurance = $this->addCarrierInsurance();
            $this->addZones($carrier_insurance);
            $this->addGroups($carrier_insurance);
            $this->addRanges($carrier_insurance);
        }

        Configuration::updateValue('COCOLIS_LIVE_MODE', false);
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
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('termsAndConditions') &&
            $this->registerHook('displayOrderDetail');
    }

    public function uninstall()
    {
        Configuration::deleteByName('COCOLIS_LIVE_MODE');
        Configuration::deleteByName('COCOLIS_HEIGHT');
        Configuration::deleteByName('COCOLIS_WIDTH');
        Configuration::deleteByName('COCOLIS_LENGTH');

        Configuration::deleteByName('COCOLIS_ADDRESS');
        Configuration::deleteByName('COCOLIS_ZIP');
        Configuration::deleteByName('COCOLIS_CITY');
        Configuration::deleteByName('COCOLIS_COUNTRY');
        //Configuration::deleteByName('COCOLIS_CARRIER_ID');
        //Configuration::deleteByName('COCOLIS_CARRIER_ASSURANCE_ID');

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
            $webhooks = $client->getWebhookClient()->getAll();
            $status = 'pending';

            if (!empty($webhooks)) {
                foreach ($webhooks as $webhook) {
                    if (strpos($webhook->url, Tools::getShopDomainSsl(true)) !== false) {
                        $status = 'already';
                    } else {
                        $client->getWebhookClient()->update(
                            [
                                'event' => $webhook->event,
                                'url' => Tools::getShopDomainSsl(true) . '/index.php?fc=module&module=cocolis&controller=webhooks&event=' . $webhook->event,
                                'active' => true
                            ],
                            $webhook->id
                        );
                        $status = 'updated';
                    }
                }
            } else {
                // Previously /cocolis/webhooks&event=blabla but not working with all versions
                $client->getWebhookClient()->create([
                    'event' => 'ride_published',
                    'url' => Tools::getShopDomainSsl(true) . '/index.php?fc=module&module=cocolis&controller=webhooks&event=ride_published',
                    'active' => true
                ]);
                $client->getWebhookClient()->create([
                    'event' => 'ride_expired',
                    'url' => Tools::getShopDomainSsl(true) . '/index.php?fc=module&module=cocolis&controller=webhooks&event=ride_expired',
                    'active' => true
                ]);
                $client->getWebhookClient()->create([
                    'event' => 'offer_accepted',
                    'url' => Tools::getShopDomainSsl(true) . '/index.php?fc=module&module=cocolis&controller=webhooks&event=offer_accepted',
                    'active' => true
                ]);
                $client->getWebhookClient()->create([
                    'event' => 'offer_cancelled',
                    'url' => Tools::getShopDomainSsl(true) . '//index.php?fc=module&module=cocolis&controller=webhooks&event=offer_cancelled',
                    'active' => true
                ]);
                $client->getWebhookClient()->create([
                    'event' => 'offer_completed',
                    'url' => Tools::getShopDomainSsl(true) . '/index.php?fc=module&module=cocolis&controller=webhooks&event=offer_completed',
                    'active' => true
                ]);
                $status = 'success';
            }

            if ($status == 'already') {
                $this->redirectWithNotifications('already');
            } elseif ($status == 'updated') {
                $this->redirectWithNotifications('updated');
            } elseif ($status == 'success') {
                $this->redirectWithNotifications('success');
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
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Production mode'),
                        'name' => 'COCOLIS_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in development (sandbox) or production mode?'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enable production mode')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disable production mode')
                            )
                        ),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_WIDTH',
                        'label' => $this->l('Average width (in cm)'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_HEIGHT',
                        'label' => $this->l('Average height (in cm)'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_LENGTH',
                        'label' => $this->l('Average length (in cm)'),
                        'desc' => $this->l("Permet de calculer les frais en l'absence du volume renseigné 
                            dans la fiche produit")
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'COCOLIS_ADDRESS',
                        'label' => $this->l('Your address'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_ZIP',
                        'label' => $this->l('Your postal code'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_CITY',
                        'label' => $this->l('Your city'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'COCOLIS_COUNTRY',
                        'label' => $this->l('Your country'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-terminal"></i>',
                        'desc' => $this->l("Enter the app-id provided to you"),
                        'name' => 'COCOLIS_APPID',
                        'label' => $this->l("Application ID"),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'COCOLIS_PASSWORD',
                        'label' => $this->l('Password'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
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
        try {
            $dimensions = 0;
            $total = 0;
            $id_address_delivery = Context::getContext()->cart->id_address_delivery;
            $address = new Address($id_address_delivery);

            $cart_hash = hash('md5', Context::getContext()->cart->id . $id_address_delivery . $this->getZip() . $address->postcode);
            $cache = false;

            if (Context::getContext()->customer->logged == true) {
                if (
                    is_null(Configuration::get('COCOLIS_HEIGHT'))
                    || is_null(Configuration::get('COCOLIS_WIDTH')) || is_null(Configuration::get('COCOLIS_LENGTH'))
                ) {
                    return false;
                }

                $from_zip = $this->getZip();

                $sql = new DbQuery();
                $sql->from("cocolis_cart");
                $sql->select('products');
                $sql->where('hash_cart= "' . $cart_hash . '"');
                $products = Db::getInstance()->getValue($sql);

                if (!empty($products)) {
                    $product_ids = unserialize($products);

                    $product_ids_2 = array();
                    foreach (Context::getContext()->cart->getProducts() as $product) {
                        $product_ids_2[] = (int)$product['id_product'] . (int)$product['quantity'];
                    }
                }

                if (empty($products)) {
                    $products = Context::getContext()->cart->getProducts();
                    $product_ids = array();
                    foreach ($products as $product) {
                        $product_ids[] = (int)$product['id_product'] . (int)$product['quantity'];
                    }
                    $product_ids = serialize($product_ids);
                    Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "cocolis_cart` 
                (`hash_cart`, `products`, `cost`) VALUES ('" . $cart_hash . "', '" . $product_ids .  "', 0)");
                } elseif (
                    !empty(array_diff($product_ids_2, $product_ids)) ||
                    !empty(array_diff($product_ids, $product_ids_2))
                ) {
                    $cache = false;
                } else {
                    $cache = true;
                }

                if (!is_null($address->postcode) && $cache == false) {
                    $to_zip = $address->postcode;

                    /**
                     * Send the details through the API
                     * Return the price sent by the API
                     */

                    $products = Context::getContext()->cart->getProducts();

                    $client = $this->authenticatedClient();
                    foreach ($products as $product) {
                        $width = (int) $product['width'];
                        $depth = (int) $product['depth'];
                        $height = (int) $product['height'];

                        if ($width == 0 || $depth == 0 || $height == 0) {
                            $width = Configuration::get('COCOLIS_WIDTH');
                            $depth = Configuration::get('COCOLIS_LENGTH');
                            $height = Configuration::get('COCOLIS_HEIGHT');
                            $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['quantity'];
                        } else { // Use the default value of volume for delivery fees
                            $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['quantity'];
                        }

                        $total += $product['price'] * (int) $product['quantity'];
                    }

                    if ($dimensions < 0.01) {
                        $dimensions += 0.01;
                    }

                    $dimensions = round($dimensions, 2);

                    $total = Context::getContext()->cart->getOrderTotal(true, 4);

                    $match = $client->getRideClient()->canMatch($from_zip, $to_zip, $dimensions, $total * 100);
                    if ($match->result == false) return false;

                    $shipping_cost = ($match->estimated_prices->regular) / 100;

                    if ($total >= 500) {
                        if (isset($match->estimated_prices->with_insurance)) {
                            $shipping_cost_insurance = ($match->estimated_prices->with_insurance) / 100;
                        } else {
                            $shipping_cost_insurance = 0;
                        }
                    } else {
                        $shipping_cost_insurance = 0;
                    }

                    $products = Context::getContext()->cart->getProducts();
                    $product_ids = array();
                    foreach ($products as $product) {
                        $product_ids[] = (int)$product['id_product'] . (int)$product['quantity'];
                    }
                    $product_ids = serialize($product_ids);
                    Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "cocolis_cart` 
                SET products = '" . $product_ids . "', cost = '" . $shipping_cost . "', 
                cost_insurance = '" . $shipping_cost_insurance . "' WHERE hash_cart = '" . $cart_hash . "'");
                }
            }

            $sql = new DbQuery();
            $sql->from("cocolis_cart");
            $sql->select('cost');
            $sql->where('hash_cart= "' . $cart_hash . '"');
            $shipping_cost = Db::getInstance()->getValue($sql);

            $sql = new DbQuery();
            $sql->from("cocolis_cart");
            $sql->select('cost_insurance');
            $sql->where('hash_cart= "' . $cart_hash . '"');
            $shipping_cost_insurance = Db::getInstance()->getValue($sql);

            if ($shipping_cost_insurance == 0) {
                $shipping_cost_insurance = false;
            }

            if ($this->id_carrier == (int)(Configuration::get('COCOLIS_CARRIER_ID'))) {
                return $shipping_cost;
            }

            if ($this->id_carrier == (int)(Configuration::get('COCOLIS_CARRIER_ASSURANCE_ID'))) {
                return $shipping_cost_insurance;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
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

    public function hookDisplayOrderDetail($params)
    {
        $order = $params['order'];
        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
        $carrier = new Carrier($orderCarrier->id_carrier);

        if ($carrier->external_module_name == "cocolis") {
            $sql = new DbQuery();
            $sql->from("cocolis_order_history");
            $sql->select('*');
            $sql->orderby('id');
            $sql->where('order_id =' . $order->getIdOrderCarrier());
            $results = Db::getInstance()->executes($sql);

            $sql = new DbQuery();
            $sql->from("cocolis_order_history");
            $sql->select('comment');
            $sql->orderby('id DESC');
            $sql->where('order_id =' . $order->getIdOrderCarrier());
            $state = Db::getInstance()->getValue($sql);

            $sql = new DbQuery();
            $sql->from("cocolis_order_history");
            $sql->select('webhook_params');
            $sql->orderby('id DESC');
            $sql->where('order_id =' . $order->getIdOrderCarrier());
            $webhook = Db::getInstance()->getValue($sql);

            $array = json_decode($webhook, true);

            $resource_id = $array['resource_id'];
            if (!empty($resource_id)) {
                $client = $this->authenticatedClient();
                $client = $client->getRideClient();
                $ride = $client->get($resource_id);
                $slug = $ride->slug;

                $link = Configuration::get('COCOLIS_LIVE_MODE') ? 'https://cocolis.fr/ride-public/' .
                    $slug : 'https://sandbox.cocolis.fr/ride-public/' . $slug;

                $this->context->smarty->assign(array(
                    'ridelink' => $link, 'order_cocolis' => $results, 'actual_state' => $state,
                    'tracking' => $ride->buyer_tracking, 'buyerURL' => $ride->getBuyerURL(),
                    'sellerURL' => $ride->getSellerURL()
                ));
            } else {
                $this->context->smarty->assign(array(
                    'ridelink' => '#', 'order_cocolis' => $results, 'actual_state' => $state,
                    'tracking' => '#', 'buyerURL' => '#',
                    'sellerURL' => '#'
                ));
            }
            return $this->display(__FILE__, '/views/templates/hook/content_ship_front.tpl');
        }
    }

    public function hookDisplayAdminOrderContentShip($params)
    {
        $order = $params['order'];
        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
        $carrier = new Carrier($orderCarrier->id_carrier);

        if ($carrier->external_module_name == "cocolis") {
            $sql = new DbQuery();
            $sql->from("cocolis_order_history");
            $sql->select('*');
            $sql->orderby('id');
            $sql->where('order_id =' . $order->getIdOrderCarrier());
            $results = Db::getInstance()->executes($sql);

            $sql = new DbQuery();
            $sql->from("cocolis_order_history");
            $sql->select('comment');
            $sql->orderby('id DESC');
            $sql->where('order_id =' . $order->getIdOrderCarrier());
            $state = Db::getInstance()->getValue($sql);

            $sql = new DbQuery();
            $sql->from("cocolis_order_history");
            $sql->select('webhook_params');
            $sql->orderby('id DESC');
            $sql->where('order_id =' . $order->getIdOrderCarrier());
            $webhook = Db::getInstance()->getValue($sql);

            $array = json_decode($webhook, true);

            $resource_id = $array['resource_id'];
            if (!empty($resource_id)) {
                $client = $this->authenticatedClient();
                $client = $client->getRideClient();
                $ride = $client->get($resource_id);
                $slug = $ride->slug;

                $link = Configuration::get('COCOLIS_LIVE_MODE') ? 'https://cocolis.fr/ride-public/' .
                    $slug . '-' . $resource_id : 'https://sandbox.cocolis.fr/ride-public/' . $slug . '-' . $resource_id;

                $this->context->smarty->assign(array(
                    'ridelink' => $link, 'order_cocolis' => $results, 'actual_state' => $state,
                    'tracking' => $ride->seller_tracking, 'buyerURL' => $ride->getBuyerURL(),
                    'sellerURL' => $ride->getSellerURL()
                ));
            } else {
                $this->context->smarty->assign(array(
                    'ridelink' => '#', 'order_cocolis' => $results, 'actual_state' => $state,
                    'tracking' => '#', 'buyerURL' => '#',
                    'sellerURL' => '#'
                ));
            }
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

            $from_composed_address = $this->getAddress() . ', '
                . $this->getZip() . ' ' . $this->getCity();

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

            $phone = $this->getPhone();
            if ($phone == null) {
                echo ('<p style="color:red;">[Module Cocolis] 
                <b>Missing cell phone number !</b> 
                You must configure your store to provide your phone number.
                </br>Go in <b>Store settings > Contact > Stores</b> 
                and provide your phone number. The order remains unchanged.</p>');
                exit;
            }

            foreach ($products as $product) {
                $width = (int) $product['width'];
                $depth = (int) $product['depth'];
                $height = (int) $product['height'];

                if ($width == 0 || $depth == 0 || $height == 0) {
                    $width = Configuration::get('COCOLIS_WIDTH');
                    $depth = Configuration::get('COCOLIS_LENGTH');
                    $height = Configuration::get('COCOLIS_HEIGHT');
                    $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['quantity'];
                } else { // Use the default value of volume for delivery fees
                    $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['quantity'];
                }

                array_push($arrayname, $product['name']);
                array_push($arrayproducts, [
                    "title" => $product['name'],
                    "qty" => $product['cart_quantity'],
                    "height" => (int) $height,
                    "width" => (int) $width,
                    "length" => (int) $depth,
                ]);
            }

            $images = [];

            $products = $order->getProducts();

            foreach ($products as $product) {
                $id_product = $product['product_id'];
                $id_image = Product::getCover($id_product);
                if (sizeof($id_image) > 0) {
                    $image = new Image($id_image['id_image']);
                    // get image full URL
                    $image_url = Tools::getShopDomainSsl(true)
                        . _THEME_PROD_DIR_ . $image->getExistingImgPath() . ".jpg";
                    array_push($images, $image_url);
                }
            }

            if ($orderCarrier->id_carrier == (int)(Configuration::get('COCOLIS_CARRIER_ASSURANCE_ID'))) {
                $insurance = true;
            } else {
                $insurance = false;
            }

            if ($insurance == true) {
                $birthday = new DateTime($customer->birthday);

                $params = [
                    "description" => "Livraison de la commande : " . implode(", ", $arrayname) . "",
                    "external_id" => $id_order,
                    "from_address" => $from_composed_address,
                    "from_postal_code" => $this->getZip(),
                    "to_address" => $composed_address,
                    "to_postal_code" => $address->postcode,
                    "from_is_flexible" => false,
                    "from_pickup_date" => $from_date,
                    "from_need_help" => true,
                    "to_is_flexible" => false,
                    "to_need_help" => true,
                    "with_insurance" => $insurance,
                    "to_pickup_date" => $to_date,
                    "is_passenger" => false,
                    "is_packaged" => true,
                    "price" => (int) $order->total_shipping_tax_incl * 100,
                    "volume" => $dimensions,
                    "environment" => "objects",
                    "photo_urls" => $images,
                    "rider_extra_information" => "Livraison de la commande :  "
                        . implode(", ", $arrayname),
                    "ride_objects_attributes" => $arrayproducts,
                    "ride_delivery_information_attributes" => [
                        "from_address" => $this->getAddress(),
                        "from_postal_code" => $this->getZip(),
                        "from_city" => $this->getCity(),
                        "from_country" => 'FR',
                        "from_contact_email" => Configuration::get('PS_SHOP_EMAIL'),
                        "from_contact_phone" => $phone,
                        "from_contact_name" => $this->getName(),
                        "from_extra_information" => 'Vendeur MarketPlace',
                        "to_address" => $address->address1,
                        "to_postal_code" => $address->postcode,
                        "to_city" => $address->city,
                        "to_country" => 'FR',
                        "to_contact_name" => $customer->firstname . ' ' . $customer->lastname,
                        "to_contact_email" => $customer->email,
                        "to_contact_phone" => $address->phone,
                        "insurance_firstname" => $customer->firstname,
                        "insurance_lastname" =>  $customer->lastname,
                        "insurance_address" => $address->address1,
                        "insurance_postal_code" => $address->postcode,
                        "insurance_city" => $address->city,
                        "insurance_country" => "FR",
                        "insurance_birthdate" => $birthday->format('c')
                    ],
                ];
            } else {
                $params = [
                    "description" => "Commande envoyée via module PrestaShop du partenaire",
                    "external_id" => $id_order,
                    "from_address" => $from_composed_address,
                    "from_postal_code" => $this->getZip(),
                    "to_address" => $composed_address,
                    "to_postal_code" => $address->postcode,
                    "from_is_flexible" => false,
                    "from_pickup_date" => $from_date,
                    "from_need_help" => true,
                    "to_is_flexible" => false,
                    "to_need_help" => true,
                    "with_insurance" => $insurance,
                    "to_pickup_date" => $to_date,
                    "is_passenger" => false,
                    "is_packaged" => true,
                    "price" => (int) $order->total_shipping_tax_incl * 100,
                    "volume" => $dimensions,
                    "environment" => "objects",
                    "photo_urls" => $images,
                    "rider_extra_information" => "Livraison de la commande :  "
                        . implode(", ", $arrayname),
                    "ride_objects_attributes" => $arrayproducts,
                    "ride_delivery_information_attributes" => [
                        "from_address" => $this->getAddress(),
                        "from_postal_code" => $this->getZip(),
                        "from_city" => $this->getCity(),
                        "from_country" => 'FR',
                        "from_contact_email" => Configuration::get('PS_SHOP_EMAIL'),
                        "from_contact_phone" => $phone,
                        "from_contact_name" => $this->getName(),
                        "from_extra_information" => 'Vendeur MarketPlace',
                        "to_address" => $address->address1,
                        "to_postal_code" => $address->postcode,
                        "to_city" => $address->city,
                        "to_country" => 'FR',
                        "to_contact_name" => $customer->firstname . ' ' . $customer->lastname,
                        "to_contact_email" => $customer->email,
                        "to_contact_phone" => $address->phone
                    ],
                ];
            }

            $client = $client->getRideClient();
            $client->create($params);

            // Clear cache cart
            Db::getInstance()->execute("TRUNCATE TABLE `" . _DB_PREFIX_ . "cocolis_cart`");
        }
    }

    public function hookTermsAndConditions($params)
    {
        $customTerms = [];

        if ($params['cart']->id_carrier == (int)(Configuration::get('COCOLIS_CARRIER_ASSURANCE_ID'))) {
            $max_value = 0;
            $objCart = new Cart($params['cart']->id);
            $total = $objCart->getOrderTotal(true, 4);

            // Maximal cost insurance
            if ($total <= 1500) {
                $max_value = 1500;
            } elseif ($total <= 3000) {
                $max_value = 3000;
            } else {
                $max_value = 5000;
            }

            $terms = new TermsAndConditions();
            $terms->setIdentifier('custom1');
            $terms->setText("Je confirme que j’ai lu les <span onclick=\"window.open('https://www.cocolis.fr/static/docs/notice_information_COCOLIS_AO.pdf', '_blank')\" style=\"color: #2fb5d2; cursor:pointer;\">conditions d'assurance</span> et que je choisis l’assurance complémentaire jusqu'à " . $max_value . " €.");
            $customTerms[] = $terms;
        }

        return $customTerms;
    }

    protected function addCarrier()
    {
        $carrier = new Carrier();

        $carrier->name = $this->l('Cocolis delivery');
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Between 2 and 4 weeks');
        }

        if ($carrier->add() == true) {
            @copy(
                dirname(__FILE__) . '/views/img/carrier_image.jpg',
                _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg'
            );
            Configuration::updateValue('COCOLIS_CARRIER_ID', (int)$carrier->id);
            return $carrier;
        }

        return false;
    }

    protected function addCarrierInsurance()
    {
        $carrier = new Carrier();

        $carrier->name = $this->l('Cocolis delivery with insurance');
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Between 2 and 4 weeks');
        }

        if ($carrier->add() == true) {
            @copy(
                dirname(__FILE__) . '/views/img/carrier_image.jpg',
                _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg'
            );
            Configuration::updateValue('COCOLIS_CARRIER_ASSURANCE_ID', (int)$carrier->id);
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
}
