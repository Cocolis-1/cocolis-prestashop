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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Register the order tab hooks of PrestaShop 1.7.7+ and unregister the hooks without any method,
 * which PrestaShop refuses in debug mode. Remove the carrier of the former insurance offer.
 *
 * @param Cocolis $module
 *
 * @return bool
 */
function upgrade_module_1_2_0($module)
{
    foreach (Cocolis::$obsolete_hooks as $hook) {
        $module->unregisterHook($hook);
    }

    upgrade_module_1_2_0_delete_insurance_carrier($module);

    return $module->registerHook(Cocolis::$hooks);
}

/**
 * Soft delete the module carriers other than the main one (the insurance carrier created up to 1.1.0),
 * so that past orders keep their carrier. Nothing is deleted when the main carrier is unknown.
 *
 * @param Cocolis $module
 */
function upgrade_module_1_2_0_delete_insurance_carrier($module)
{
    $main_carrier = new Carrier((int) Configuration::get('COCOLIS_CARRIER_ID'));
    if (Validate::isLoadedObject($main_carrier) && !$main_carrier->deleted) {
        $carriers = Carrier::getCarriers((int) Configuration::get('PS_LANG_DEFAULT'), false, false, false, null, Carrier::ALL_CARRIERS);
        foreach ($carriers as $row) {
            if ($row['external_module_name'] === $module->name && (int) $row['id_carrier'] !== (int) $main_carrier->id) {
                $carrier = new Carrier((int) $row['id_carrier']);
                $carrier->deleted = true;
                $carrier->update();
            }
        }
    }

    Configuration::deleteByName('COCOLIS_CARRIER_ASSURANCE_ID');
}
