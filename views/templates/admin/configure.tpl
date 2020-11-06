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
	padding-top: 20px;
	border-radius: 10px;
  background-color: #4caf50;
  color: white;
  margin-bottom: 15px;
	opacity: 1;
  transition: opacity 0.6s; /* 600ms to fade out */
}

.alert-red {
  padding: 15px;
	padding-top: 20px;
	border-radius: 10px;
  background-color: #f44336;
  color: white;
  margin-bottom: 15px;
	opacity: 1;
  transition: opacity 0.6s; /* 600ms to fade out */
}

.alert-orange {
  padding: 15px;
	padding-top: 20px;
	border-radius: 10px;
  background-color: #ff9800;
  color: white;
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

/* When moving the mouse over the close button */
.closebtn:hover {
  color: black;
}
</style>

<script>
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

<div class="panel">
	<aside id="notifications">
  {if isset($notifications)}
    {block name='notifications_info'}
		{if ($notifications == 'webhook_success')}
    <div class="alert">
			<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
			<p><b>Webhooks configurés !</b> Tous les changements de status seront envoyés directement sur votre marketplace !</p>
		</div>
		{/if}
		{if ($notifications == 'webhook_already')}
    <div class="alert-red">
			<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
			<p><b>Webhooks déjà configurés !</b> Aucun changement de domaine n'a été détecté, la configuration reste similaire.</p>
		{/if}
		{if ($notifications == 'nophone')}
    <div class="alert-red">
			<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
			<p><b>Numéro de téléphone portable manquant !</b> Vous devez configurer votre boutique afin de fournir un numéro de téléphone valide. </br>Rendez vous dans <b>Paramètres de la boutique > Contact > Magasins</b> et fournissez un numéro de téléphone <b>portable</b>. La commande reste inchangée.</p>
		{/if}
		{if ($notifications == 'webhook_updated')}
    <div class="alert-orange">
			<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
			<p><b>Webhooks mis à jour !</b> Un changement de domaine a été détecté, la configuration a été mis à jour chez Cocolis.</p>
		{/if}
    {/block}
  {/if}

	</aside>
	<img src="{$module_dir|escape:'html':'UTF-8'}/logo.png" id="payment-logo" class="pull-right" />
	<h2><strong>{l s="👋  Merci d'avoir installé Cocolis - Votre module de livraison collaborative !" mod='cocolis'}</strong><br /></h2>
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
