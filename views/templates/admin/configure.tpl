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

/* The alert message box */
.alert {
	padding: 15px;
	border-radius: 10px;
	background-color: #4caf50;
	color: white;
	margin-bottom: 15px;
	opacity: 1;
	transition: opacity 0.6s; /* 600ms to fade out */
}

.alert-red {
	padding: 15px;
	border-radius: 10px;
	background-color: #f44336;
	color: white;
	margin-bottom: 15px;
	opacity: 1;
	transition: opacity 0.6s; /* 600ms to fade out */
}

.alert-orange {
	padding: 15px;
	border-radius: 10px;
	background-color: #ff9800;
	color: white;
	margin-bottom: 15px;
	opacity: 1;
	transition: opacity 0.6s; /* 600ms to fade out */
}

.alert-gray {
	padding: 15px;
	padding-top: 15px;
	border-radius: 10px;
	background-color: #34495e;
	color: white;
	margin-top: 15px;
	margin-bottom: 15px;
	opacity: 1;
	transition: opacity 0.6s; /* 600ms to fade out */
}

/* The close button */
.closebtn {
	margin-left: 15px;
	color: white;
	font-weight: bold;
	float: right;
	font-size: 22px;
	line-height: 20px;
	cursor: pointer;
	transition: 0.3s;
}

.mt-15 {
	margin-top:15px;
}

/* When moving the mouse over the close button */
.closebtn:hover {
	color: black;
}

.panel-cocolis {
	font-size: 14px;
}
</style>

<script type="text/javascript">
// Get all elements with class="closebtn"
var close = document.getElementsByClassName("closebtn");
var i;

// Loop through all close buttons
for (i = 0; i < close.length; i++) {
  // When someone clicks on a close button
  close[i].onclick = function(){

    // Get the parent of <span class="closebtn"> (<div class="alert">)
    var div = this.parentElement;

    // Set the opacity of div to 0 (transparent)
    div.style.opacity = "0";

    // Hide the div after 600ms (the same amount of milliseconds it takes to fade out)
    setTimeout(function(){ div.style.display = "none"; }, 600);
  }
}
</script>

<div class="panel panel-cocolis">
	<aside id="notifications">
  {if isset($notifications)}
    {block name='notifications_info'}
		{if ($notifications == 'webhook_success')}
    <div class="alert">
			<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
			<p>{l s='Webhooks updated ! All status changes will be sent directly to your marketplace!' mod='cocolis'}</p>
		</div>
		{/if}
		{if ($notifications == 'webhook_already')}
    <div class="alert-red">
			<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
			<p>{l s='Webhooks already updated ! No change of domain was detected, the configuration remains similar.' mod='cocolis'}</p>
		</div>
		{/if}
		{if ($notifications == 'webhook_updated')}
    <div class="alert-orange">
			<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
			<p>{l s='Webhooks updated ! A change of domain has been detected, the configuration has been updated at Cocolis.' mod='cocolis'}</p>
		</div>
		{/if}
    {/block}
  {/if}

	</aside>
	<img src="{$module_dir|escape:'html':'UTF-8'}/logo.png" id="payment-logo" class="pull-right" />
	<p><strong>{l s="👋 Thank you for installing Cocolis - Your collaborative delivery module!" mod='cocolis'}</strong><br /></p>
	<p>
		{l s="You now offer collaborative ❤️, economical 💸 and ecological 🍃 delivery on your site" mod='cocolis'}<br />
		{l s="Your customers will be able to find the best carrier to make their delivery." mod='cocolis'}
		{l s="The module will not be functional as long as the configuration is incomplete!" mod='cocolis'}
	</p>
	<p>
		<br />
		<i class="icon icon-angle-right"></i>
		{l s='You can learn more about the configuration of this module' mod='cocolis'} <a href="https://doc.cocolis.fr/">{l s='here' mod='cocolis'}</a>
	</p>
	<br />
	<div style="margin:0 auto;">
		<button type='submit' name='webhooks' class='myButton' form='module_form'>{l s='Automatically configure webhooks' mod='cocolis'}</button>
	</div>

	{if !$cocolis_is_phone_fill_on_store_address}
		<div class="alert-orange mt-15">
			{l s='You haven\'t filled in your store\'s phone number; it\'s required for Cocolis to function. You can enter it in Store Settings > Contact > Stores > Contact Details' mod='cocolis'}
		</div>
	{else}
		<div class="alert-green mt-15">
			{l s='✅ Store phone number configured' mod='cocolis'}
			✅ Numéro de téléphone de la boutique configuré
		</div>
	{/if}

	{if !$cocolis_is_phone_on_address_mandatory}
		<div class="alert-orange mt-15">
			{l s='You must make the phone number mandatory here: Customers > Addresses > Set the Phone field as required for this section' mod='cocolis'}
			Vous devez rendre le numéro de téléphone obligatoire ici : Clients > Adresses > Définir le champs Téléphone comme requis pour cette section
		</div>
	{else}
		<div class="alert-green mt-15">
			{l s='✅ Phone number mandatary in addresses' mod='cocolis'}
			✅ Numéro de téléphone sur les adresses obligatoire
		</div>
	{/if}

	{if $cocolis_debug_mode}
    <div class="alert-gray mt-15">
			<h6 style="font-weight: bold;">{l s='DEBUG INFORMATIONS' mod='cocolis'}</h6>
				<p>{l s='Cocolis module version' mod='cocolis'} : <b>{$cocolis_module_version}</b></p>
				<p>{l s='PrestaShop shipping ID without insurance' mod='cocolis'} : <b>{$cocolis_carrier_id}</b></p>
				<b>{l s='This information is intended for Cocolis staff, if you don\'t need it, disable the debug mode' mod='cocolis'}</b>
			</div>
	{/if}
</div>
