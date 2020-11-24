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
.tracking {
  padding-bottom: 20px;
}

.donetracking {
  color: #00c9b7;
}

.progresstracking {
  color: #f7c347;
}

.waitingtracking {
  color: #b2bec3;
}

.errortracking {
  color: #c0392b;
}

.card-cocolis{
  display: block;
  box-shadow: 2px 2px 8px 0 rgba(0,0,0,.2);
  background-color: #ffffff;
  border-radius: 5px;
  padding: 30px;
  margin-top: 1%;
  margin-bottom: 2%;
}
</style>

<script src="https://kit.fontawesome.com/c73bb87b99.js" crossorigin="anonymous"></script>

<div class="card-cocolis" id="cocolis">
  <h2 style="text-align:center; padding-right: 32px;"><b>Suivi de votre  livraison</b> <b class="font-weight-bold" style="color:#0069d8;">Cocolis</b><h2>
  <div class="d-flex">
    <h4 style="text-align:center;">Référence : <b class="font-weight-bold" style="color:#0069d8;">{$tracking|escape:'html_all':'utf-8'}</b></h4>
  </div>
  <div class="container" style="margin-top: 30px;">
        {if ($actual_state == "")}
          <div class="waitingtracking">
            <h4 class="tracking"><i class="far fa-dot-circle"></i> En attente de la création de l'annonce</h4>
          </div>
        {/if}

        {if ($actual_state == "ride_published")}
          <div class="progresstracking">
            <h4 class="tracking"><i class="far fa-clock"></i> Le {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} - Attente des disponibilités de l'acheteur</h4>
            <h4 class="tracking"><i class="far fa-clock"></i> Le {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} - Recherche d'un transporteur</h4>
          </div>
          <div class="waitingtracking">
            <h4 class="tracking"><i class="far fa-dot-circle"></i> En cours de livraison</h4>
            <h4 class="tracking"><i class="far fa-dot-circle"></i> Livré</h4>
          </div>
          <h4>Vous pouvez accéder à l'annonce : <a href="{$ridelink|escape:'html_all':'utf-8'}">ici</a></h4>
        {/if}

        {if ($actual_state == "ride_expired")}
          <div class="donetracking">
            <h4 class="tracking"><i class="far fa-clock"></i> Le {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} - Attente des disponibilités de l'acheteur</h4>
          </div>
          <div class="errortracking">
            <h4 class="tracking"><i class="far fa-clock"></i> Recherche d'un transporteur expiré.</h4>
            <h4 class="tracking" style="color:black;">Rapprochez-vous de notre support pour d'amples informations.</h4>
          </div>
          <div class="waitingtracking">
            <h4 class="tracking"><i class="far fa-dot-circle"></i> En cours de livraison (annulé)</h4>
            <h4 class="tracking"><i class="far fa-dot-circle"></i> Livré (annulé)</h4>
          </div>
        {/if}

        {if ($actual_state == "offer_accepted")}
          <div class="donetracking">
            <h4 class="tracking"><i class="far fa-check-circle"></i> Le {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} - Attente des disponibilités de l'acheteur</h4>
            <h4 class="tracking"><i class="far fa-check-circle"></i> Le {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} - Recherche d'un transporteur</h4>
          </div>
          <div class="progresstracking">
            <h4 class="tracking"><i class="far fa-clock"></i> Le {$order_cocolis[1]['created_at']|date_format:"%d/%m/%Y"} - En cours de livraison</h4>
          </div>
          <div class="waitingtracking">
            <h4 class="tracking"><i class="far fa-dot-circle"></i> Livré</h4>
          </div>
        {/if}

        {if ($actual_state == "offer_cancelled")}
          <div class="donetracking">
            <h4 class="tracking"><i class="far fa-check-circle"></i> Le {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} - Attente des disponibilités de l'acheteur</h4>
            <h4 class="tracking"><i class="far fa-check-circle"></i> Le {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} - Recherche d'un transporteur</h4>
          </div>
          <div class="errortracking">
            <h4 class="tracking"><i class="far fa-times-circle"></i> En cours de livraison</h4>
            <h4 class="tracking" style="color:black;">La livraison est annulée avec le transporteur. <br>Vous devez choisir un nouveau transporteur sur votre page de suivi (ci-dessous).</h4>
          </div>
          <div class="waitingtracking">
            <h4 class="tracking"><i class="far fa-dot-circle"></i> Livré (retardé)</h4>
          </div>
        {/if}

        {if ($actual_state == "offer_completed")}
          <div class="donetracking">
            <h4 class="tracking"><i class="far fa-check-circle"></i> Le {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} - Attente des disponibilités de l'acheteur</h4>
            <h4 class="tracking"><i class="far fa-check-circle"></i> Le {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} - Recherche d'un transporteur</h4>
            <h4 class="tracking"><i class="far fa-check-circle"></i> Le {$order_cocolis[1]['created_at']|date_format:"%d/%m/%Y"} - En cours de livraison</h4>
            <h4 class="tracking"><i class="far fa-check-circle"></i> Le {$order_cocolis[2]['created_at']|date_format:"%d/%m/%Y"} - Livré</h4>
          </div>
        {/if}

        <h4 style="padding-top: 30px;">Lien vers la page de suivi Cocolis : <a href="{$buyerURL|escape:'html_all':'utf-8'}">ici</a></h4>
      </div>
</div>