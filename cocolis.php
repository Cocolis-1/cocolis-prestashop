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

class Cocolis extends CarrierModule
{
    /**
     * Hooks used by the module. The order tab uses displayAdminOrderTabShip/ContentShip up to
     * PrestaShop 1.7.6 and displayAdminOrderTabLink/TabContent from 1.7.7: both are registered,
     * each PrestaShop version only triggers the ones it knows.
     */
    public static $hooks = [
        'actionPaymentConfirmation',
        'actionCarrierUpdate',
        'actionFrontControllerSetMedia',
        'displayOrderDetail',
        'displayAdminOrderTabShip',
        'displayAdminOrderContentShip',
        'displayAdminOrderTabLink',
        'displayAdminOrderTabContent',
    ];

    /**
     * Hooks registered by older versions of the module without any matching method
     */
    public static $obsolete_hooks = [
        'header',
        'backOfficeHeader',
        'displayBackOfficeHeader',
        'updateCarrier',
        'moduleRoutes',
        'displayAdminOrderContentOrder',
        'displayAdminOrderTabOrder',
        'displayAdminOrder',
        'termsAndConditions',
    ];

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

    public static function getWebhookUrl($event)
    {
        return Tools::getShopDomainSsl(true) . '/index.php?fc=module&module=cocolis&controller=webhooks&event=' . $event;
    }

    public static function isPhoneOnAddressMandatory()
    {
        $requiredFields = AddressFormat::getFieldsRequired();
        return in_array('phone', $requiredFields) || in_array('phone_mobile', $requiredFields);
    }

    public function __construct()
    {
        $this->name = 'cocolis';
        $this->tab = 'shipping_logistics';
        $this->version = '1.4.0';
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

        $filter = defined('PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE') ? constant('PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE') : 4;
        $carriers = Carrier::getCarriers(Context::getContext()->language->id, true, false, false, null, $filter);

        // Saving id carrier list
        $id_carrier_list = array();
        foreach ($carriers as $carrier) {
            $id_carrier_list[] .= $carrier['id_carrier'];
        }

        if (!in_array((int)(Configuration::get('COCOLIS_CARRIER_ID')), $id_carrier_list) || is_null(Configuration::get('COCOLIS_CARRIER_ID'))) {
            $carrier = $this->addCarrier();
            $this->addZones($carrier);
            $this->addGroups($carrier);
            $this->addRanges($carrier);
        }

        Configuration::updateValue('COCOLIS_LIVE_MODE', false);
        include(dirname(__FILE__) . '/sql/install.php');


        return parent::install() && $this->registerHook(self::$hooks);
    }

    public function uninstall()
    {
        Configuration::deleteByName('COCOLIS_LIVE_MODE');
        Configuration::deleteByName('COCOLIS_DEBUG_MODE');
        Configuration::deleteByName('COCOLIS_HEIGHT');
        Configuration::deleteByName('COCOLIS_WIDTH');
        Configuration::deleteByName('COCOLIS_LENGTH');

        Configuration::deleteByName('COCOLIS_ADDRESS');
        Configuration::deleteByName('COCOLIS_ZIP');
        Configuration::deleteByName('COCOLIS_CITY');
        Configuration::deleteByName('COCOLIS_COUNTRY');

        Configuration::deleteByName('COCOLIS_CARRIER_ID');
        Configuration::deleteByName('COCOLIS_CARRIER_ASSURANCE_ID');
        Configuration::deleteByName('COCOLIS_WEBHOOK_SECRET');

        $this->deleteCarriers();

        include(dirname(__FILE__) . '/sql/uninstall.php');

        // Hooks are unregistered by the parent
        return parent::uninstall();
    }

    /**
     * Soft delete the carriers created by the module, so that past orders keep their carrier.
     */
    protected function deleteCarriers()
    {
        $carriers = Carrier::getCarriers(Context::getContext()->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
        foreach ($carriers as $row) {
            if ($row['external_module_name'] === $this->name) {
                $carrier = new Carrier((int) $row['id_carrier']);
                $carrier->deleted = true;
                $carrier->update();
            }
        }
    }

    public function hookActionCarrierUpdate($params)
    {
        $id_carrier_old = (int) $params['id_carrier'];
        $id_carrier_new = (int) $params['carrier']->id;
        if ($id_carrier_old === (int) Configuration::get('COCOLIS_CARRIER_ID')) {
            Configuration::updateValue('COCOLIS_CARRIER_ID', $id_carrier_new);
        }
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
        // The configuration page is rendered once by getContent(), with all its template variables
        $this->context->smarty->assign(array('notifications' => 'webhook_' . $type));
    }


    /**
     * Secret used by Cocolis to sign the webhooks it sends, fetched from the API on first use.
     */
    public function getWebhookSigningSecret()
    {
        $secret = (string) Configuration::get('COCOLIS_WEBHOOK_SECRET');
        if ($secret === '') {
            try {
                $secret = $this->refreshWebhookSigningSecret();
            } catch (Exception $e) {
                PrestaShopLogger::addLog('[Module Cocolis] Unable to fetch the webhook signing secret: ' . $e->getMessage(), 3);
            }
        }

        return $secret;
    }

    public function refreshWebhookSigningSecret()
    {
        $response = $this->authenticatedClient()->callAuthentificated('applications/webhook_signing_secret', 'GET');
        $json = json_decode($response->text(), true);
        $secret = isset($json['data']['webhook_signing_secret']) ? (string) $json['data']['webhook_signing_secret'] : '';
        Configuration::updateValue('COCOLIS_WEBHOOK_SECRET', $secret);

        return $secret;
    }

    /**
     * Check the X-Cocolis-Signature header: "t=<timestamp>,v1=<HMAC-SHA256 of '<timestamp>.<raw body>'>".
     */
    public function isValidWebhookSignature($body, $header)
    {
        $parts = [];
        foreach (explode(',', (string) $header) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) === 2) {
                $parts[$pair[0]] = $pair[1];
            }
        }

        if (empty($parts['t']) || empty($parts['v1']) || abs(time() - (int) $parts['t']) > 300) {
            return false;
        }

        $secret = $this->getWebhookSigningSecret();
        if ($secret === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $parts['t'] . '.' . $body, $secret), $parts['v1']);
    }

    /**
     * Register the webhooks at Cocolis, or update them when their URL changed.
     *
     * @return string pending|already|updated|success
     */
    public function registerWebhooks()
    {
        $this->refreshWebhookSigningSecret();

        $client = $this->authenticatedClient();
        $webhooks = $client->getWebhookClient()->getAll();
        $status = 'pending';

        if (!empty($webhooks)) {
            foreach ($webhooks as $webhook) {
                $url = self::getWebhookUrl($webhook->event);
                if ($webhook->url === $url) {
                    if ($status === 'pending') {
                        $status = 'already';
                    }
                } else {
                    // Domain changed
                    $client->getWebhookClient()->update(
                        [
                            'event' => $webhook->event,
                            'url' => $url,
                            'active' => true
                        ],
                        $webhook->id
                    );
                    $status = 'updated';
                }
            }
        } else {
            $events = ['ride_published', 'ride_expired', 'offer_accepted', 'offer_cancelled', 'offer_completed'];
            foreach ($events as $event) {
                // Previously /cocolis/webhooks&event=blabla but not working with all versions
                $client->getWebhookClient()->create([
                    'event' => $event,
                    'url' => self::getWebhookUrl($event),
                    'active' => true
                ]);
            }
            $status = 'success';
        }

        return $status;
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
            // The webhook button belongs to the settings form: save the credentials typed in first
            $this->postProcess();

            if (!$this->hasCredentials()) {
                $this->redirectWithNotifications('missing_credentials');
            } else {
                try {
                    $status = $this->registerWebhooks();

                    if ($status == 'already') {
                        $this->redirectWithNotifications('already');
                    } elseif ($status == 'updated') {
                        $this->redirectWithNotifications('updated');
                    } elseif ($status == 'success') {
                        $this->redirectWithNotifications('success');
                    }
                } catch (\Cocolis\Api\Errors\UnauthorizedException $e) {
                    $this->redirectWithNotifications('invalid_credentials');
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('[Module Cocolis] Unable to configure the webhooks: ' . $e->getMessage(), 3);
                    $this->redirectWithNotifications('api_error');
                }
            }
        } elseif (((bool)Tools::isSubmit('submitCocolisModule')) == true) {
            $this->postProcess();
            $this->checkCredentials();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign(
            array(
                'cocolis_is_phone_fill_on_store_address' => (bool)Configuration::get('PS_SHOP_PHONE'),
                'cocolis_is_phone_on_address_mandatory' => (bool)$this->isPhoneOnAddressMandatory(),
                'cocolis_debug_mode' => Configuration::get('COCOLIS_DEBUG_MODE'),
                'cocolis_carrier_id' => Configuration::get('COCOLIS_CARRIER_ID'),
                'cocolis_module_version' => $this->version,
                'cocolis_uses_former_credentials' => !$this->usesApiKey() && $this->hasCredentials(),
                'cocolis_api_keys_url' => $this->getApiKeysUrl()
            )
        );

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Check the saved credentials against the Cocolis API, and fetch the webhook signing secret.
     */
    protected function checkCredentials()
    {
        if (!$this->hasCredentials()) {
            return;
        }

        try {
            $this->refreshWebhookSigningSecret();
        } catch (\Cocolis\Api\Errors\UnauthorizedException $e) {
            $this->redirectWithNotifications('invalid_credentials');
            return;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('[Module Cocolis] Unable to check the credentials: ' . $e->getMessage(), 3);
            return;
        }

        $this->checkAccountCanMatch();
    }

    /**
     * Run a test quotation (can_match) so account-level issues, such as a missing
     * Cocolis subscription, surface on the configuration page instead of silently
     * hiding the Cocolis carrier at checkout.
     */
    protected function checkAccountCanMatch()
    {
        $zip = (string) $this->getZip();
        if ($zip === '') {
            // The module is not fully configured yet: the missing shipping
            // address has its own warning on the configuration page.
            return;
        }

        try {
            // 0.1 m3 and a 100 euros declared value: the goal is only to surface
            // account-level errors, not to get a realistic quotation.
            $this->authenticatedClient()->getRideClient()->canMatch($zip, $zip, 0.1, 10000);
        } catch (\Cocolis\Api\Errors\UnprocessableEntityException $e) {
            $json = json_decode($e->getMessage(), true);
            $code = isset($json['code']) ? (string) $json['code'] : '';

            if ($code === 'missing_subscription') {
                $this->redirectWithNotifications('missing_subscription');
            } else {
                PrestaShopLogger::addLog('[Module Cocolis] The test quotation failed: ' . $e->getMessage(), 3);
                $this->redirectWithNotifications('can_match_error');
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('[Module Cocolis] Unable to run the test quotation: ' . $e->getMessage(), 3);
        }
    }

    /**
     * Page of the Cocolis account where the API keys are created.
     */
    protected function getApiKeysUrl()
    {
        return (Configuration::get('COCOLIS_LIVE_MODE') ? 'https://www.cocolis.fr' : 'https://sandbox.cocolis.fr') . '/api-keys';
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
                                'label' => $this->l('Production mode enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Production mode disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Debug mode'),
                        'name' => 'COCOLIS_DEBUG_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('In case of problems you can activate this option to help the Cocolis development team to solve anomalies'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Debug mode enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Debug mode disabled')
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
                        'type' => 'password',
                        'name' => 'COCOLIS_API_KEY',
                        'label' => $this->l('API key'),
                        'desc' => $this->l('Create an API key in your Cocolis account, on the API keys page. Leave the field empty to keep the saved key.'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-terminal"></i>',
                        'desc' => $this->l('Former connection method, used only when no API key is saved.'),
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
            'COCOLIS_DEBUG_MODE' => Configuration::get('COCOLIS_DEBUG_MODE', false),
            'COCOLIS_API_KEY' => Configuration::get('COCOLIS_API_KEY', null),
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
            $value = Tools::getValue($key);

            // The secret fields are always rendered empty: an empty value keeps the saved secret
            if (in_array($key, array('COCOLIS_API_KEY', 'COCOLIS_PASSWORD')) && $value === '') {
                continue;
            }

            Configuration::updateValue($key, $value);
        }

        // The credentials or the mode may have changed: fetch the signing secret again on next use
        Configuration::deleteByName('COCOLIS_WEBHOOK_SECRET');
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        if ($this->id_carrier !== (int)(Configuration::get('COCOLIS_CARRIER_ID'))) {
            return false;
        }

        try {
            $dimensions = 0;
            $total = 0;
            $id_address_delivery = Context::getContext()->cart->id_address_delivery;
            $address = new Address($id_address_delivery);

            $cart_hash = hash('md5', Context::getContext()->cart->id . $id_address_delivery . $this->getZip() . $address->postcode . $this->version);
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
                        $product_ids_2[] = (int)$product['id_product'] . (int)$product['cart_quantity'];
                    }
                }

                if (empty($products)) {
                    $products = Context::getContext()->cart->getProducts();
                    $product_ids = array();
                    foreach ($products as $product) {
                        $product_ids[] = (int)$product['id_product'] . (int)$product['cart_quantity'];
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
                            $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['cart_quantity'];
                        } else { // Use the default value of volume for delivery fees
                            $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['cart_quantity'];
                        }

                        $total += $product['price'] * (int) $product['cart_quantity'];
                    }

                    if ($dimensions < 0.01) {
                        $dimensions += 0.01;
                    }

                    $dimensions = round($dimensions, 2);

                    $total = Context::getContext()->cart->getOrderTotal(true, 4);

                    $match = $client->getRideClient()->canMatch($from_zip, $to_zip, $dimensions, $total * 100);
                    if ($match->result == false) {
                        return false;
                    }

                    $shipping_cost = ($match->estimated_prices->regular) / 100;

                    $products = Context::getContext()->cart->getProducts();
                    $product_ids = array();
                    foreach ($products as $product) {
                        $product_ids[] = (int)$product['id_product'] . (int)$product['cart_quantity'];
                    }
                    $product_ids = serialize($product_ids);
                    Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "cocolis_cart` SET products = '" . $product_ids . "', cost = '" . $shipping_cost . "' WHERE hash_cart = '" . $cart_hash . "'");
                }
            }

            $sql = new DbQuery();
            $sql->from("cocolis_cart");
            $sql->select('cost');
            $sql->where('hash_cart= "' . $cart_hash . '"');
            $shipping_cost = Db::getInstance()->getValue($sql);

            if (Configuration::get('COCOLIS_DEBUG_MODE')) {
                PrestaShopLogger::addLog('[Module Cocolis] Valeurs : SHIPPING COST : ' . $shipping_cost, 1);
            }

            if ($shipping_cost == 0) {
                $shipping_cost = false;
            }

            return $shipping_cost;
        } catch (Exception $e) {
            if (Configuration::get('COCOLIS_DEBUG_MODE')) {
                PrestaShopLogger::addLog('[Module Cocolis] Erreur : ' . $e->getMessage(), 3);
            }

            return false;
        }
    }

    public function authenticatedClient()
    {
        // The API key is sent with each request: no sign in needed
        if ($this->usesApiKey()) {
            return Client::create(array(
                'api_key' => Configuration::get('COCOLIS_API_KEY'),
                'live' => Configuration::get('COCOLIS_LIVE_MODE')
            ));
        }

        $client = Client::create(array(
            'app_id' => Configuration::get('COCOLIS_APPID'),
            'password' => Configuration::get('COCOLIS_PASSWORD'),
            'live' => Configuration::get('COCOLIS_LIVE_MODE')
        ));
        $client->signIn();
        return $client;
    }

    /**
     * The API key replaces the former application ID and password, which are still supported.
     */
    public function usesApiKey()
    {
        return (string) Configuration::get('COCOLIS_API_KEY') !== '';
    }

    public function hasCredentials()
    {
        return $this->usesApiKey() || (Configuration::get('COCOLIS_APPID') && Configuration::get('COCOLIS_PASSWORD'));
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    /**
     * Javascript Debug
     * @param array $params
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->addJS($this->_path . 'views/js/debug.js');
    }

    /**
     * Onglet de suivi Cocolis (PrestaShop < 1.7.7)
     */
    public function hookDisplayAdminOrderTabShip($params)
    {
        if ($this->isCocolisOrder($params['order'])) {
            return $this->display(__FILE__, '/views/templates/hook/tab_ship.tpl');
        }
    }

    public function hookDisplayAdminOrderContentShip($params)
    {
        $order = $params['order'];
        if ($this->isCocolisOrder($order)) {
            $this->context->smarty->assign('module_dir', $this->_path);
            $this->context->smarty->assign($this->getTrackingVars($order, 'seller'));

            return $this->display(__FILE__, '/views/templates/hook/content_ship.tpl');
        }
    }

    /**
     * Onglet de suivi Cocolis (PrestaShop >= 1.7.7)
     */
    public function hookDisplayAdminOrderTabLink($params)
    {
        if ($this->isCocolisOrder(new Order((int) $params['id_order']))) {
            return $this->display(__FILE__, '/views/templates/hook/tab_link.tpl');
        }
    }

    public function hookDisplayAdminOrderTabContent($params)
    {
        $order = new Order((int) $params['id_order']);
        if ($this->isCocolisOrder($order)) {
            $this->context->smarty->assign('module_dir', $this->_path);
            $this->context->smarty->assign('cocolis_tab_link', true);
            $this->context->smarty->assign($this->getTrackingVars($order, 'seller'));

            return $this->display(__FILE__, '/views/templates/hook/content_ship.tpl');
        }
    }

    public function hookDisplayOrderDetail($params)
    {
        $order = $params['order'];
        if ($this->isCocolisOrder($order)) {
            $this->context->smarty->assign($this->getTrackingVars($order, 'buyer'));

            return $this->display(__FILE__, '/views/templates/hook/content_ship_front.tpl');
        }
    }

    protected function isCocolisOrder($order)
    {
        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
        $carrier = new Carrier($orderCarrier->id_carrier);

        return $carrier->external_module_name == $this->name;
    }

    /**
     * Template variables of the tracking block, for the seller (back office) or the buyer (front office).
     */
    protected function getTrackingVars($order, $audience)
    {
        $sql = new DbQuery();
        $sql->from("cocolis_order_history");
        $sql->select('*');
        $sql->orderby('id');
        $sql->where('order_id = ' . (int) $order->id);
        $results = Db::getInstance()->executeS($sql);

        $last = empty($results) ? null : end($results);
        $state = $last ? $last['comment'] : false;

        $ride = $last ? $this->fetchRideFromWebhookParams($last['webhook_params']) : null;
        if (!$ride) {
            return array(
                'ridelink' => '#', 'order_cocolis' => $results, 'actual_state' => $state,
                'tracking' => '#', 'buyerURL' => '#', 'sellerURL' => '#'
            );
        }

        $base_url = Configuration::get('COCOLIS_LIVE_MODE') ? 'https://cocolis.fr' : 'https://sandbox.cocolis.fr';

        return array(
            'ridelink' => $base_url . '/ride-public/' . $ride->slug . '-' . $ride->id,
            'order_cocolis' => $results,
            'actual_state' => $state,
            'tracking' => $audience === 'seller' ? $ride->seller_tracking : $ride->buyer_tracking,
            'buyerURL' => $ride->getBuyerURL(),
            'sellerURL' => $ride->getSellerURL()
        );
    }

    /**
     * Load the Cocolis ride referenced by the last webhook of an order, or null when unavailable.
     */
    protected function fetchRideFromWebhookParams($webhook_params)
    {
        $params = json_decode((string) $webhook_params, true);
        // For offer events, resource_id is the offer ID: ride_id is always the ride
        if (!empty($params['ride_id'])) {
            $ride_id = $params['ride_id'];
        } elseif (!empty($params['resource_id']) && isset($params['resource_type']) && $params['resource_type'] === 'ride') {
            $ride_id = $params['resource_id'];
        } else {
            return null;
        }

        try {
            return $this->authenticatedClient()->getRideClient()->get((string) $ride_id);
        } catch (Exception $e) {
            // Never break the order page because the Cocolis API is unavailable
            PrestaShopLogger::addLog('[Module Cocolis] Unable to load ride ' . (int) $ride_id . ': ' . $e->getMessage(), 2);

            return null;
        }
    }

    public function hookActionPaymentConfirmation($params)
    {
        // Exceptions thrown in hooks are hidden outside debug mode: log them so that a missing ride is visible
        try {
            $this->createRide((int) $params['id_order']);
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[Module Cocolis] Unable to create the ride of order ' . (int) $params['id_order'] . ': ' . $e->getMessage(),
                3
            );
        }
    }

    protected function createRide($id_order)
    {
        $order = new Order($id_order);
        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
        $carrier = new Carrier($orderCarrier->id_carrier);

        if ($carrier->external_module_name == "cocolis") {
            $client = $this->authenticatedClient();

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

            $cart = new Cart($order->id_cart);
            $products = $cart->getProducts();
            $dimensions = 0;

            $customer = new Customer($cart->id_customer);

            $arrayproducts = [];

            $arrayname = [];

            $phone = $this->getPhone();
            if ($phone == null) {
                // Never interrupt the payment: the shop phone is configured in Store settings > Contact > Stores
                PrestaShopLogger::addLog('[Module Cocolis] Missing shop phone number, the ride of order ' . (int) $id_order . ' was not created', 3);

                return;
            }

            foreach ($products as $product) {
                $width = (int) $product['width'];
                $depth = (int) $product['depth'];
                $height = (int) $product['height'];

                if ($width == 0 || $depth == 0 || $height == 0) {
                    $width = Configuration::get('COCOLIS_WIDTH');
                    $depth = Configuration::get('COCOLIS_LENGTH');
                    $height = Configuration::get('COCOLIS_HEIGHT');
                    $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['cart_quantity'];
                } else { // Use the default value of volume for delivery fees
                    $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['cart_quantity'];
                }

                array_push($arrayname, $product['name']);
                $ride_object = [
                    "title" => $product['name'],
                    "qty" => $product['cart_quantity'],
                    "height" => (int) $height,
                    "width" => (int) $width,
                    "length" => (int) $depth,
                ];

                $weight_class = $this->getWeightClass((float) $product['weight']);
                if ($weight_class) {
                    $ride_object["weight"] = $weight_class;
                }

                $image_url = $this->getProductImageUrl((int) $product['id_product']);
                if ($image_url) {
                    $ride_object["photo_urls"] = [$image_url];
                }

                array_push($arrayproducts, $ride_object);
            }

            $images = [];
            foreach ($order->getProducts() as $product) {
                $image_url = $this->getProductImageUrl((int) $product['product_id']);
                if ($image_url) {
                    array_push($images, $image_url);
                }
            }
            // Cocolis keeps at most 5 photos per ride
            $images = array_slice($images, 0, 5);

            // Many shops only ask for the mobile phone
            $to_phone = !empty($address->phone) ? $address->phone : $address->phone_mobile;

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
                "to_pickup_date" => $to_date,
                "is_passenger" => false,
                "is_packaged" => true,
                "price" => (int) round($order->total_shipping_tax_incl * 100),
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
                    "to_contact_phone" => $to_phone
                ],
            ];

            $params['content_value'] = (int) round(($order->total_paid - $order->total_shipping_tax_incl) * 100);

            $client = $client->getRideClient();
            $client->create($params);

            // Clear cache cart
            Db::getInstance()->execute("TRUNCATE TABLE `" . _DB_PREFIX_ . "cocolis_cart`");
        }
    }

    protected function getProductImageUrl($id_product)
    {
        $cover = Product::getCover($id_product);
        if (empty($cover)) {
            return null;
        }

        $image = new Image($cover['id_image']);

        return Tools::getShopDomainSsl(true) . _THEME_PROD_DIR_ . $image->getExistingImgPath() . ".jpg";
    }

    /**
     * Cocolis weight class of a product, or null when the weight is unknown.
     */
    protected function getWeightClass($weight)
    {
        if ($weight <= 0) {
            return null;
        }

        $weight_kg = strtolower((string) Configuration::get('PS_WEIGHT_UNIT')) === 'g' ? $weight / 1000 : $weight;
        if ($weight_kg < 5) {
            return 'less_than_5_kg';
        }
        if ($weight_kg <= 30) {
            return 'between_5_and_30_kg';
        }
        if ($weight_kg <= 50) {
            return 'between_30_and_50_kg';
        }
        if ($weight_kg <= 100) {
            return 'between_50_and_100_kg';
        }

        return 'more_than_100_kg';
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

        // PrestaShop 9 compatibility: add zones with prices to ranges
        $zones = Zone::getZones();
        foreach ($zones as $zone) {
            Db::getInstance()->insert(
                'carrier_zone',
                array(
                    'id_carrier' => (int)$carrier->id,
                    'id_zone' => (int)$zone['id_zone']
                ),
                false,
                true,
                Db::ON_DUPLICATE_KEY
            );

            // Add price for range_price
            Db::getInstance()->insert(
                'delivery',
                array(
                    'id_carrier' => (int)$carrier->id,
                    'id_range_price' => (int)$range_price->id,
                    'id_range_weight' => null,
                    'id_zone' => (int)$zone['id_zone'],
                    'price' => 0
                ),
                false,
                true,
                Db::ON_DUPLICATE_KEY
            );
        }
    }

    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }
    }
}
