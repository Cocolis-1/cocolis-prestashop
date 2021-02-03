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
</style>

<script src="https://kit.fontawesome.com/c73bb87b99.js" crossorigin="anonymous"></script>

<div class="tab-pane" id="cocolis">
    <h4 class="visible-print">{l s='Cocolis Status' mod='cocolis'}</h4>
    <img src="{$module_dir|escape:'html':'UTF-8'}/logo.png" id="payment-logo" class="pull-left" />
    <h2 style="text-align:center; padding-right: 32px;"><b>{l s='Track your delivery' mod='cocolis'}</b> <b
            class="font-weight-bold" style="color:#0069d8;"> Cocolis</b>
        <h2>
            <div class="d-flex">
                <h4 style="text-align:center;">{l s='Tracking' mod='cocolis'} <b class="font-weight-bold"
                        style="color:#0069d8;">{$tracking}</b></h4>
            </div>
            <div class="container" style="margin-top: 30px;">
                {if ($actual_state == "")}
                    <div class="waitingtracking">
                        <h4 class="tracking"><i class="far fa-dot-circle"></i>
                            {l s="Waiting for buyer's availability" mod='cocolis'}</h4>
                    </div>
                {/if}

                {if ($actual_state == "ride_published")}
                    <div class="donetracking">
                        <h4 class="tracking"><i class="far fa-clock"></i>
                            {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} -
                            {l s="Waiting for buyer's availability" mod='cocolis'}</h4>
                    </div>
                    <div class="progresstracking">
                        <h4 class="tracking"><i class="far fa-clock"></i>
                            {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} -
                            {l s='Search for a carrier' mod='cocolis'}</h4>
                    </div>
                    <div class="waitingtracking">
                        <h4 class="tracking"><i class="far fa-dot-circle"></i> {l s='In delivering' mod='cocolis'}</h4>
                        <h4 class="tracking"><i class="far fa-dot-circle"></i> {l s='Delivered' mod='cocolis'}</h4>
                    </div>
                    <h4>{l s='You can access the ride:' mod='cocolis'} <a href="{$ridelink}">{l s='here' mod='cocolis'}</a>
                    </h4>
                {/if}

                {if ($actual_state == "ride_expired")}
                    <div class="donetracking">
                        <h4 class="tracking"><i class="far fa-clock"></i>
                            {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} -
                            {l s="Waiting for buyer's availability" mod='cocolis'}</h4>
                    </div>
                    <div class="errortracking">
                        <h4 class="tracking"><i class="far fa-clock"></i>
                            {l s='Searching for an expired carrier.' mod='cocolis'}</h4>
                        <h4 class="tracking" style="color:black;">
                            {l s='Contact our support for more information.' mod='cocolis'}</h4>
                    </div>
                    <div class="waitingtracking">
                        <h4 class="tracking"><i class="far fa-dot-circle"></i>
                            {l s='In delivering (cancelled)' mod='cocolis'}</h4>
                        <h4 class="tracking"><i class="far fa-dot-circle"></i> {l s='Delivered (cancelled)' mod='cocolis'}
                        </h4>
                    </div>
                {/if}

                {if ($actual_state == "offer_accepted")}
                    <div class="donetracking">
                        <h4 class="tracking"><i class="far fa-check-circle"></i>
                            {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} -
                            {l s="Waiting for buyer's availability" mod='cocolis'}</h4>
                        <h4 class="tracking"><i class="far fa-check-circle"></i>
                            {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} -
                            {l s='Search for a carrier' mod='cocolis'}</h4>
                    </div>
                    <div class="progresstracking">
                        <h4 class="tracking"><i class="far fa-clock"></i>
                            {$order_cocolis[1]['created_at']|date_format:"%d/%m/%Y"} - {l s='In delivering' mod='cocolis'}
                        </h4>
                    </div>
                    <div class="waitingtracking">
                        <h4 class="tracking"><i class="far fa-dot-circle"></i> {l s='Delivered' mod='cocolis'}</h4>
                    </div>
                {/if}

                {if ($actual_state == "offer_cancelled")}
                    <div class="donetracking">
                        <h4 class="tracking"><i class="far fa-check-circle"></i>
                            {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} -
                            {l s="Waiting for buyer's availability" mod='cocolis'}</h4>
                        <h4 class="tracking"><i class="far fa-check-circle"></i>
                            {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} -
                            {l s='Search for a carrier' mod='cocolis'}</h4>
                    </div>
                    <div class="errortracking">
                        <h4 class="tracking"><i class="far fa-times-circle"></i> {l s='In delivering' mod='cocolis'}</h4>
                        <h4 class="tracking" style="color:black;">
                            {l s='The delivery is canceled with the carrier. You must choose a new carrier on your tracking page (below).' mod='cocolis'}
                        </h4>
                    </div>
                    <div class="waitingtracking">
                        <h4 class="tracking"><i class="far fa-dot-circle"></i> {l s='Delivered (late)' mod='cocolis'}</h4>
                    </div>
                {/if}

                {if ($actual_state == "offer_completed")}
                    <div class="donetracking">
                        <h4 class="tracking"><i class="far fa-check-circle"></i>
                            {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} -
                            {l s="Waiting for buyer's availability" mod='cocolis'}</h4>
                        <h4 class="tracking"><i class="far fa-check-circle"></i>
                            {$order_cocolis[0]['created_at']|date_format:"%d/%m/%Y"} -
                            {l s='Search for a carrier' mod='cocolis'}</h4>
                        <h4 class="tracking"><i class="far fa-check-circle"></i>
                            {$order_cocolis[1]['created_at']|date_format:"%d/%m/%Y"} - {l s='In delivering' mod='cocolis'}
                        </h4>
                        <h4 class="tracking"><i class="far fa-check-circle"></i>
                            {$order_cocolis[2]['created_at']|date_format:"%d/%m/%Y"} - {l s='Delivered' mod='cocolis'}</h4>
                    </div>
                {/if}

                <h4 style="padding-top: 30px;">{l s='Link to the Cocolis tracking page:' mod='cocolis'} <a
                        href="{$sellerURL}">{l s='here' mod='cocolis'}</a></h4>
            </div>
</div>