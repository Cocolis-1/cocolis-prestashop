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
 * Widen the webhook_params column, then fetch the webhook signing secret and fix the webhook URLs
 * registered at Cocolis.
 *
 * @param Cocolis $module
 *
 * @return bool
 */
function upgrade_module_1_1_1($module)
{
    // Webhook payloads longer than 255 characters were truncated
    $altered = Db::getInstance()->execute(
        'ALTER TABLE `' . _DB_PREFIX_ . 'cocolis_order_history` MODIFY `webhook_params` TEXT'
    );
    if (!$altered) {
        return false;
    }

    if (!Configuration::get('COCOLIS_APPID') || !Configuration::get('COCOLIS_PASSWORD')) {
        return true;
    }

    try {
        $module->registerWebhooks();
    } catch (Exception $e) {
        // Never block the upgrade: the merchant can still register the webhooks from the configuration page
        PrestaShopLogger::addLog(
            'Cocolis: webhooks could not be updated during the upgrade to 1.1.1: ' . $e->getMessage(),
            3,
            null,
            'Module',
            (int) $module->id
        );
    }

    return true;
}
