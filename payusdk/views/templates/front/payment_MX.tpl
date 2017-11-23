{*
* 2007-2014 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{capture name=path}{l s='payU checkout' mod='payusdk'}{/capture}
<div class="col-xs-12 col-sm-12 col-md-12">
    <div class="wrap">
        <div id="alertpayusdk" class="alert">
            <strong></strong> <p></p>
        </div>
        <div id="validation-payusdk">
            <h1 class="page-heading">{l s='RESUMEN DEL PEDIDO' mod='payusdk'}</h1>
            {assign var='current_step' value='payment'}
            {include file="$tpl_dir./order-steps.tpl"}
            {if $nbProducts <= 0}
                <p class="warning" style="text-align: center; font-size: 16px;">{l s='Your shopping cart is empty.' mod='payusdk'}</p>
            {else}
                <form action="{$link->getModuleLink('payusdk', 'validation', [], true)|escape:'html'}" id="form-payusdk" method="post">
                    <div class="box cheque-box">
                        <div id="preload-payusdk" style="display: none; width: 74px; margin: auto auto;">
                            <img src="{$this_path}preload.gif">
                        </div>
                        <h3 class="page-subheading" style="text-align: center; font-size: 10px;">
                            <img src="{$this_path}boton.png" alt="{l s='Bank wire' mod='bankwire'}"/>
                            <div>
                                {l s='Ha elegido pagar con payU.' mod='payusdk'}
                            </div>
                        </h3>
                        <div>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="border: solid 1px; text-align: center;">
                                        {l s='El importe total de su pedido es' mod='payusdk'}
                                    </td>
                                    <td style="border: solid 1px;text-align: center;">
                                        <span id="amount" class="price">{displayPrice price=$total}</span>
                                        {if $use_taxes == 1}
                                            {l s='(IVA incluído)' mod='payusdk'}
                                        {/if}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: solid 1px; text-align: center;" colspan="2">
                                        <b>{l s='Por favor, confirme su pedido haciendo clic en Pagar con payU' mod='payusdk'}.</b>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <input type="submit"
                           style="background: #F0943E; color: #FFFFFF; font-size: 16px; margin-bottom: 10px; border-radius: 20px;"
                           value="{l s='Pagar con payU' mod='payusdk'}"
                           class="button btn btn-default pull-right"/>
                </form>
            {/if}
        </div>
        <div class="form-container-payusdk">
            <h2>Selecciona:</h2>
            <select name="pay-type" id="pay-type">
                <option value="" selected>Medio de pago</option>
                <option value="credit">Tarjeta de crédito</option>
                <option value="cash">Efectivo</option>
            </select>
            <div id="payusdk-credit">
                <div class="card-wrapper form-group"></div>
                <form id="payusdkform" method="post" action="{$ajax}">
                    <div class="container active">
                        <div class="col-xs-12">
                            <label for="cc_number" class="label">Datos de la tarjeta *</label>
                            <input placeholder="Número de tarjeta" type="tel" name="cc_number" required="" class="form-control">
                        </div>
                    </div>

                    <div class="container" style="padding-top: 15px;">
                        <div class="col-xs-6" >
                            <input placeholder="Titular" type="text" name="cc_name" required="" class="form-control">
                            <input type="hidden" name="cc_type" class="form-payu">
                        </div>
                        <div class="col-xs-3">
                            <input placeholder="MM/YY" type="tel" name="expiry" required="" class="form-control" >
                        </div>
                        <div class="col-xs-3">
                            <input placeholder="CVC" type="number" name="cc_cvc" required="" class="form-control" maxlength="3">
                        </div>


                        <input type="hidden" value="{$accountId}" name="accountId">
                        <input type="hidden" value="{$merchantId}" name="merchantId">
                        <input type="hidden" value="{$apiKey}" name="apiKey">
                        <input type="hidden" value="{$apiLogin}" name="apiLogin">
                        <input type="hidden" value="{$isTest}" name="isTest">
                        <input type="hidden" value="{$custip}" name="custip">
                        <input type="hidden" name="sessionid" value="{$sessionid}">
                        <input type="hidden" value="{$refventa}" name="refventa">
                        <input type="hidden" name="restoreOrder" value="{$restore}">
                        <input type="hidden" name="idorder">
                        <input type="hidden" value="ORDEN DE COMPRA # {$refventa}" name="description">
                        <input type="hidden" value="{$total}" name="total">
                        <input type="hidden" name="p_billing_email" value="{$p_billing_email}">
                        <input type="hidden" value="{$postal}" name="postal">
                        <input type="hidden" value="{$state}" name="state">
                        <input type="hidden" name="city" value="{$city}">
                        <input type="hidden" class="form-payu" id="telephone" name="telephone" placeholder="+573178034732" required="" value="{$phone}">

                    </div>

                    <div class="container">
                        <div class="col-xs-8">
                            <label for="billing_address_1" class="label">Dirección de facturación *</label>
                            <input type="text" class="form-control" id="billing_address_1" name="billing_address_1" placeholder="calle 93B # 18-25" required="" value="{$address}">
                        </div>
                        <div class="col-xs-4">
                            <label for="dni" class="label">Identificación Oficial INE  *</label>
                            <input type="text" class="form-control" id="dni" name="dni" placeholder="1056165896" required="">
                        </div>
                    </div>

                    <div class="container">
                        <div class="col-xs-8">
                            <label for="billing_address_2" class="label">(Opcional complementaria)</label>
                            <input type="text" class="form-control" id="billing_address_2" name="billing_address_2" placeholder="Interior 3 Apto 401" value="{$address_1}">
                        </div>
                        <div class="col-xs-4">
                            <label for="cuotas" class="label">Cantidad de cuotas *</label>
                            <select class="form-control" name="cuotas" style="width: 100% !important">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                                <option value="13">13</option>
                                <option value="14">14</option>
                                <option value="15">15</option>
                                <option value="16">16</option>
                                <option value="17">17</option>
                                <option value="18">18</option>
                                <option value="19">19</option>
                                <option value="20">20</option>
                                <option value="21">21</option>
                                <option value="22">22</option>
                                <option value="23">23</option>
                                <option value="24">24</option>
                                <option value="25">25</option>
                                <option value="26">26</option>
                                <option value="27">27</option>
                                <option value="28">28</option>
                                <option value="29">29</option>
                                <option value="30">30</option>
                                <option value="31">31</option>
                                <option value="32">32</option>
                                <option value="33">33</option>
                                <option value="34">34</option>
                                <option value="35">35</option>
                                <option value="36">36</option>
                            </select>
                        </div>
                    </div>

                    <div class="container">
                        <div class="col-xs-6"><input type="submit" class="form-payu" id="submit_payu" value="Pagar" style="background: #F0943E; color: #FFFFFF; font-size: 16px;" /></div>
                        <div class="col-xs-6">
                            <button type="button" id="restoreOderPayusdk">Cancelar</button>
                        </div>
                        <div id="loader-payusdk" class="opc-overlay" ></div>
                    </div>
                    <div class="container" style="padding-top: 15px;">
                        <div class="col-xs-12 alert alert-danger" id="msj-error"></div>
                    </div>
                </form>
            </div>
            <div id="payusdk-cash">
                <form action="{$ajax}" method="post" id="formCashPayu">
                    <input type="hidden" value="{$accountId}" name="accountId">
                    <input type="hidden" value="{$merchantId}" name="merchantId">
                    <input type="hidden" value="{$apiKey}" name="apiKey">
                    <input type="hidden" value="{$apiLogin}" name="apiLogin">
                    <input type="hidden" value="{$isTest}" name="isTest">
                    <input type="hidden" value="{$custip}" name="custip">
                    <input type="hidden" name="refventa">
                    <input type="hidden" name="restoreOrder" value="{$restore}">
                    <input type="hidden" name="idorder">
                    <input type="hidden" name="description">
                    <input type="hidden" name="total">
                    <input type="hidden" name="p_billing_email" value="{$p_billing_email}">
                    <input type="hidden" name="cc_name" value="{$p_billing_name} {$p_billing_lastname}">
                    <input type="hidden" name="medium">
                    <div class="container">
                        <div class="col-xs-8">
                            <h2 class="title-body">Selecciona el medio de pago</h2>
                            <div class="col-xs-12 alert alert-danger" id="msj-error"></div>
                            <p><img src="{$this_path_bw}img/bancomer.png" class="change-cash gray-scale" data-type="BANCOMER" alt="BANCOMER" style="margin-left:15px; margin-bottom:15px;"><img src="{$this_path_bw}img/oxxo.png" class="change-cash gray-scale" data-type="OXXO" alt="OXXO" style="margin-left:15px; margin-bottom:15px;"><img src="{$this_path_bw}img/seven_eleven.png" class="change-cash gray-scale" data-type="SEVEN_ELEVEN" alt="SEVEN_ELEVEN" style="margin-left:15px; margin-bottom:15px;"></p>
                            <label for="dni" class="label">Documento de identidad *</label>
                            <input type="text" class="form-control" id="dni" name="dni" placeholder="1056165896" required="">
                        </div>
                    </div>
                    <div class="container">
                        <div class="col-xs-6"><input type="submit" class="form-payu" id="submit_payu" value="Pagar" style="background: #F0943E; color: #FFFFFF; font-size: 16px;" /></div>
                        <div class="col-xs-6">
                            <button type="button" id="restoreOderPayusdk">Cancelar</button>
                        </div>
                        <div id="loader-payusdk" class="opc-overlay" ></div>
                    </div>
                </form>
            </div>
            <p style="background:url('https://maf.pagosonline.net/ws/fp?id={$sessionid}80200')"></p>
            <img src="https://maf.pagosonline.net/ws/fp/clear.png?id={$sessionid}80200">
            <script src="https://maf.pagosonline.net/ws/fp/check.js?id={$sessionid}80200"></script>
            <object type="application/x-shockwave-flash" data="https://maf.pagosonline.net/ws/fp/fp.swf?id={$sessionid}80200" width="1" height="1" id="thm_fp">
        </div>
    </div>
</div>
