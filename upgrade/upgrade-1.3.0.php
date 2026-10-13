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
 * Remove the CustomerFormatter override installed up to 1.1.x.
 *
 * @param Cocolis $module
 *
 * @return bool
 */
function upgrade_module_1_3_0($module)
{
    upgrade_module_1_3_0_remove_customer_formatter_override($module);

    return true;
}

/**
 * The override made the birthdate required at sign up, and breaks the checkout on PrestaShop 9.
 * PrestaShop keeps the overrides of a module when it is upgraded, and removeOverride() needs the
 * module override file to know what to remove: restore the former file while removing the override.
 * A failure is logged and does not stop the upgrade.
 *
 * @param Cocolis $module
 */
function upgrade_module_1_3_0_remove_customer_formatter_override($module)
{
    $shop_override = _PS_OVERRIDE_DIR_ . 'classes/form/CustomerFormatter.php';
    if (!is_file($shop_override) || strpos((string) file_get_contents($shop_override), 'module: ' . $module->name) === false) {
        return;
    }

    $override_dir = $module->getLocalPath() . 'override/classes/form/';
    $created_dirs = array();
    foreach (array($module->getLocalPath() . 'override/classes/', $override_dir) as $dir) {
        if (!is_dir($dir) && @mkdir($dir)) {
            $created_dirs[] = $dir;
        }
    }

    $former_override = $override_dir . 'CustomerFormatter.php';
    $removed = false;
    try {
        if (@copy(dirname(__FILE__) . '/former-overrides/CustomerFormatter.php', $former_override)) {
            $removed = $module->removeOverride('CustomerFormatter');
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog('[Module Cocolis] Unable to remove the CustomerFormatter override: ' . $e->getMessage(), 2);
    } finally {
        @unlink($former_override);
        foreach (array_reverse($created_dirs) as $dir) {
            @rmdir($dir);
        }
    }

    // removeOverride() keeps the class when only the former "use" statement is left in it
    if ($removed && is_file($shop_override)) {
        $code = preg_replace('/^\s*use\s+[^;]+;\s*$/m', '', (string) file_get_contents($shop_override));
        if (preg_match('/^<\?(?:php)?\s*class\s+CustomerFormatter\s+extends\s+CustomerFormatterCore\s*\{\s*\}\s*$/i', $code)) {
            @unlink($shop_override);
            Tools::generateIndex();
        }
    }

    if (!$removed) {
        PrestaShopLogger::addLog(
            '[Module Cocolis] The override/classes/form/CustomerFormatter.php override of the former Cocolis versions could not be removed: delete its Cocolis methods manually.',
            2
        );
    }
}
