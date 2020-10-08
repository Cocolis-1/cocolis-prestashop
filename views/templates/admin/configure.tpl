{*
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
*}

<style>
.myButton {
	background-color: #ffffff;
	border:1px solid #00aff0;
	border-radius:11px;
	display:inline-block;
	color:#00aff0;
	font-family:Arial;
	font-size:16px;
	font-weight:bold;
	padding:13px 40px;
	text-decoration:none;
}

.myButton:active {
	position:relative;
	top:1px;
}
</style>


<div class="panel">

	<img src="{$module_dir|escape:'html':'UTF-8'}/logo.png" id="payment-logo" class="pull-right" />
	<h2><strong>{l s="🥳 Merci d'avoir installé Cocolis - Votre module de livraison collaborative !" mod='cocolis'}</strong><br /></h2>
	<h2>
		{l s="Vous proposez maintenant sur votre site une livraison collaborative ❤️, économique 💸 et écologique 🍃" mod='cocolis'}<br />
		{l s="Vos clients pourront ainsi trouver le meilleur transporteur pour effectuer leur livraison." mod='cocolis'}
	</h2>
	<h2>
		<br />
		<i class="icon icon-angle-right"></i> 
		{l s='Vous pouvez en savoir plus sur la configuration de ce module' mod='cocolis'} <a href="https://doc.cocolis.fr/">ici.</a>
	</h2>
	<br />
	<div style="margin:0 auto;">
		<button type='submit' name='webhooks' class='myButton' form='module_form'>Configurer automatiquement les Webhooks</button>
	</div>
</div>
