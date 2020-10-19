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

<div class="panel">
<<<<<<< Updated upstream
=======
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
>>>>>>> Stashed changes
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
</div>
