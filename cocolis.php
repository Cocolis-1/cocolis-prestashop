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


require_once __DIR__ . '/vendor/autoload.php';

use Cocolis\Api\Client;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Cocolis extends CarrierModule
{
    protected $config_form = false;

    public function __construct()
    {
        global $client;
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
        $client = $this->authenticatedClient();
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
        Configuration::updateValue('COCOLIS_CARRIER_ID', $carrier->id_reference);
        include(dirname(__FILE__) . '/sql/install.php');


        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('updateCarrier');
    }

    public function uninstall()
    {
        Configuration::deleteByName('COCOLIS_LIVE_MODE');
        Configuration::deleteByName('COCOLIS_CARRIER_ID');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitCocolisModule')) == true) {
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
                        'col' => 2,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-move"></i>',
                        'name' => 'COCOLIS_VOLUME',
                        'label' => $this->l('Volume moyen'),
                        'desc' => $this->l("Permet de calculer les frais en l'absence du volume renseigné dans la fiche produit")
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-archive"></i>',
                        'name' => 'COCOLIS_ACCOUNT_ZIP',
                        'label' => $this->l('Votre code postal'),
                        'desc' => $this->l("Pour calculer les frais de livraisons, entrez le code postal de votre entrepôt")
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-terminal"></i>',
                        'desc' => $this->l("Entrez l'app-id qui vous a été fourni"),
                        'name' => 'COCOLIS_ACCOUNT_APPID',
                        'label' => $this->l("ID de l'application"),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'COCOLIS_ACCOUNT_PASSWORD',
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
            'COCOLIS_ACCOUNT_APPID' => Configuration::get('COCOLIS_ACCOUNT_APPID', 'app_id'),
            'COCOLIS_ACCOUNT_PASSWORD' => Configuration::get('COCOLIS_ACCOUNT_PASSWORD', null),
            'COCOLIS_ACCOUNT_ZIP' => Configuration::get('COCOLIS_ACCOUNT_ZIP', null),
            'COCOLIS_VOLUME' => Configuration::get('COCOLIS_VOLUME', 0.25)
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

            $from_zip = Configuration::get('COCOLIS_ACCOUNT_ZIP');

            if (!is_null($address->postcode)) {
                $to_zip = $address->postcode;

                /**
                 * Send the details through the API
                 * Return the price sent by the API
                 */

                
                $client = Client::getClient(); 

                $products = Context::getContext()->cart->getProducts();
                foreach($products as $product){
                    $width = (int) $product['width'];
                    $depth = (int) $product['depth'];
                    $height = (int) $product['height'];

                    if($width == 0 || $depth == 0 || $height == 0)
                        $dimensions += Configuration::get('COCOLIS_VOLUME') * (int) $product['quantity']; // Use the default value of volume for delivery fees
                    else
                        $dimensions += (($width * $depth * $height) / pow(10, 6)) * (int) $product['quantity'];  
                      
                    $total += $product['price'] * (int) $product['quantity'];
                }
                
                if($dimensions < 0.01){
                    $dimensions += 0.01;
                    $dimensions = round($dimensions, 2);
                }else{
                    $dimensions = round($dimensions, 2);
                }
                                    
                $match = $client->getRideClient()->canMatch($from_zip, $to_zip, $dimensions, $total);
                $shipping_cost = ($match->estimated_prices->regular)/100;

            }
        }

        return $shipping_cost;
    }

    public function authenticatedClient()
    {
        $client = Client::create(array(
        'app_id' => Configuration::get('COCOLIS_ACCOUNT_APPID'),
        'password' => Configuration::get('COCOLIS_ACCOUNT_PASSWORD'),
        'live' => Configuration::get('COCOLIS_LIVE_MODE')
        ));
        $client->signIn();
        return $client;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
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

        foreach (Language::getLanguages() as $lang)
            $carrier->delay[$lang['id_lang']] = $this->l('Délai variable');

        if ($carrier->add() == true) {
            @copy(dirname(__FILE__) . '/views/img/carrier_image.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg');
            Configuration::updateValue('MYSHIPPINGMODULE_CARRIER_ID', (int)$carrier->id);
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

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookUpdateCarrier($params)
    {
        /**
         * Not needed since 1.5
         * You can identify the carrier by the id_reference
         */
    }
}
