<?php
if (!defined('_PS_VERSION_')) exit;
include_once(_PS_MODULE_DIR_ . 'payusdk/lib/Payu_OrderState.php');
class Payusdk extends PaymentModule
{
  private $_html = '';
  private $_postErrors = array();

  public $apiKey;
  public $apiLogin;
  public $merchantId;
  public $accountId;
  public $isTest;
  public $countryCode;

  public function __construct()
  {
      $this->name = 'payusdk';
        $this->displayName = 'PayU SDK';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Saul Morales Pacheco';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.6.0', 'max' => '1.7');

        $config = Configuration::getMultiple(array('APIKEY','APILOGIN','MERCHANTID', 'ACCOUNTID','ISTEST'));
        if (isset($config['APIKEY']))
            $this->apiKey = trim($config['APIKEY']);
        if (isset($config['APILOGIN']))
            $this->apiLogin = trim($config['APILOGIN']);
        if (isset($config['ACCOUNTID']))
            $this->accountId = trim($config['ACCOUNTID']);
        if (isset($config['MERCHANTID']))
            $this->merchantId = trim($config['MERCHANTID']);
        if (isset($config['ISTEST']))
            $this->isTest = $config['ISTEST'];

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->is_eu_compatible = 1;
	    $this->countryCode =  Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        parent::__construct();

        $this->displayName = $this->l('PayU SDK');
        $this->description = $this->l('Accepts payments by PayU');

        $this->confirm_uninstall = $this->l('Are you sure you want to uninstall? You will lose all your settings!');
        if (!isset($this->merchantId) OR !isset($this->apiKey) OR !isset($this->apiLogin) OR !isset($this->accountId))
        $this->warning = $this->l('APIKEY, APILOGIN, ACCOUNTID y MERCHANTID, deben estar configurados para utilizar este módulo correctamente');
      if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
        $this->warning = $this->l('No currency set for this module');

  }

   /**
     * @return bool
     */
    public function install()
    {
        Payu_OrderState::setup();
        return (
            function_exists('curl_version') &&
            parent::install() &&
            in_array('curl', get_loaded_extensions()) &&
            $this->createHooks() &&
            Configuration::updateValue('APIKEY', '') &&
            Configuration::updateValue('APILOGIN', '') &&
            Configuration::updateValue('MERCHANTID', '')  &&
            Configuration::updateValue('ACCOUNTID', '')
        );
    }


  /**
     * @return bool
     */
    private function createHooks()
    {
        $registerStatus = $this->registerHook('header') && $this->registerHook('paymentReturn') && $this->registerHook('updateOrderStatus');
        if (version_compare(_PS_VERSION_, '1.7.0.0 ', '<')) {
            $registerStatus &= $this->registerHook('payment');
        } else {
            $registerStatus &= $this->registerHook('paymentOptions');
        }

      return $registerStatus;
    }

    function uninstall() {
      Payu_OrderState::remove();
      if (!Configuration::deleteByName('APIKEY') OR !Configuration::deleteByName('APILOGIN') OR !Configuration::deleteByName('MERCHANTID') OR !Configuration::deleteByName('ACCOUNTID') OR !Configuration::deleteByName('ISTEST') OR !parent::uninstall())
          return false;
      return true;
    }

    private function _postValidation()
    {
      if (Tools::isSubmit('btnSubmit')) {
        if (!Tools::getValue('merchantId'))
          $this->_postErrors[] = $this->l('\'merchantId\' Campo Requerido.');
        if (!Tools::getValue('accountId'))
          $this->_postErrors[] = $this->l('\'accountId\' Campo Requerido.');
        if (!Tools::getValue('apiKey'))
          $this->_postErrors[] = $this->l('\'apiKey\' Campo Requerido.');
        if (!Tools::getValue('apiLogin'))
          $this->_postErrors[] = $this->l('\'apiLogin\' Campo Requerido.');
      }
    }

    private function _postProcess()
    {
      if (Tools::isSubmit('btnSubmit')) {
        Configuration::updateValue('MERCHANTID', Tools::getValue('merchantId'));
        Configuration::updateValue('ACCOUNTID', Tools::getValue('accountId'));
        Configuration::updateValue('APIKEY', Tools::getValue('apiKey'));
        Configuration::updateValue('APILOGIN', Tools::getValue('apiLogin'));
        Configuration::updateValue('ISTEST', Tools::getValue('isTest'));
        $this->_html.= '<div class="bootstrap"><div class="alert alert-success">'.$this->l('Cambios Aplicados Exitosamente') . '</div></div>';
      }
    }

    private function _displayForm()
    {

      global $cookie;

      $states = Payu_OrderState::getOrderStates();
      $id_os_initial = Configuration::get('PAYUSDK_ORDERSTATE_WAITING');

      $this->_html .= '<b>'.
      $this->l('Este modulo acepta pagos utilizando la plataforma de payU').'</b><br /><br />'.
      $this->l('Si el cliente opta por esta modalidad de pago, el estado del pedido cambia a \'payU Esperando Pago\'.').'<br/>'.
      $this->l('Cuando el sitio payU confirme el pago, el estado del pedido cambia a \'Pago aceptado\'.')."<br/><br/>";

      $this->_html.='<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post" class="half_form">

        <fieldset style="width: 90%; overflow: auto;display:none;">
        <div id="advanced" >
          <div style="float: left;padding:10px;">
            <table cellpadding="0" cellspacing="0" class="table">
            <thead>
              <tr>
                <th style="width: 200px;font-weight: bold;"><p style="display:inline;color:red">Advanced</p> Order States</th>
                <th>Initial State</th>
                <th>Delete On</th>
              </tr>
            </thead>
            <tbody>';

            foreach ($states as $item => $state) {
          $checked = "";
          $checkedorder = "";
          if ($state['id_order_state'] == $id_os_initial) {
            $checked = 'checked=checked';
          }

          if ($state['id_order_state']) {
            $checkedorder = 'checked=checked';
          }

          $this->_html.='.<tr style="background-color: ' . $state['color'] . ';">
            <td>' . $this->l($state['name']) . '</td>
            <td style="text-align:center"><input type="radio" name="id_os_initial" ' . $checked . ' value="' . $state['id_order_state'] . '"/></td>
            <td style="text-align:center"><input type="checkbox" name="id_os_deleteon[]" value="' . $state['id_order_state'] . '" ' . $checkedorder . ' /> </td>
            </tr>';
        }

        if(Tools::getValue('isTest', $this->isTest) == "TRUE") {
            $checked1 = "selected";
            $checked2 = "";
        } else if(Tools::getValue('isTest', $this->isTest) == "FALSE") {
            $checked1 ="";
            $checked2 = "selected";
        }else{
          $checked1 ="selected";
          $checked2 = "";
        }

        $this->_html.='</tbody>
        								</table>
        							</div>
        						</div>
        					</fieldset>
        					<fieldset>
        				<legend>'.utf8_encode("Configuraci&oacute;n payU").'</legend>

                <img src="../modules/payusdk/boton.png"/>

                <table border="0" width="600" cellpadding="0" cellspacing="0" id="form">
        					<tr><td colspan="2">Por favor especifique su accountId, apiKey, apiLogin, merchantId sumninistrados por payU. Estos los encuentra en su cuenta, menu - información técnica.<br /><br /></td></tr>
                  <tr><td width="250" align="justify" style="padding-right:20px;"><b>accountId (El identificador de la cuenta):</b><br></td><td><input type="text" name="accountId" value="' . Tools::htmlentitiesUTF8(Tools::getValue('accountId', $this->accountId)) . '" style="width: 300px;" /></td></tr>
                  <tr><td width="250" >&nbsp;&nbsp;</td></tr>
        					<tr><td width="250" align="justify" style="padding-right:20px;"><b>apiKey:</b><br>.</td><td><input type="text" name="apiKey" value="' . Tools::htmlentitiesUTF8(Tools::getValue('apiKey', $this->apiKey)) . '" style="width: 300px;" /></td></tr>
        					<tr><td width="250" >&nbsp;&nbsp;</td></tr>
                            <tr><td width="250"  align="justify" style="padding-right:20px;"><b>apiLogin</b><br></td><td><input type="text" name="apiLogin" value="' . Tools::htmlentitiesUTF8(Tools::getValue('apiLogin', $this->apiLogin)) . '" style="width: 300px;" /></td></tr>
                    <tr><td width="250" >&nbsp;&nbsp;</td></tr>
                            <tr><td width="250"  align="justify" style="padding-right:20px;"><b>merchantId</b><br></td><td><input type="text" name="merchantId" value="' . Tools::htmlentitiesUTF8(Tools::getValue('merchantId', $this->merchantId)) . '" style="width: 300px;" /></td></tr>
        				    <tr><td width="250" >&nbsp;&nbsp;</td></tr>
                            <tr><td width="250" ><b>Sitio en pruebas</b><br></td>
                            <td><select name="isTest" >
                                    <option value="1" '. $checked1.'>SI</option>
                                    <option value="0" '. $checked2.'>NO</option>
                                </select>
                            </td>
                            </tr>

        				</table>
        			</fieldset>
        	<div style="clear: both;"></div>
        	<br/>
        	<center>
        		<input type="submit" name="btnSubmit" value="' . $this->l('Guardar Cambios') . '" class="button" />
        	</center>
        	<hr />
        </form>';

    }
    public function getContent()
    {
      $this->_html = '<h2>' . $this->displayName . '</h2>';

      if (Tools::isSubmit('btnSubmit')) {
        $this->_postValidation();
        if (!count($this->_postErrors)) {
          $this->_postProcess();
        } else {
          foreach ($this->_postErrors as $err) {
            $this->_html .= '<div class="alert error">' . $err . '</div>';
          }
        }
      } else {
        $this->_html .= '<br/>';
      }

      $this->_displayForm();
      return $this->_html;
    }

    //change status ordeer
    function PaymentSuccess($state,$idorder)
    {
        $this->_Acentarpago($state,$idorder);
    }

    private function _Acentarpago($response,$idorder)
    {

          $state = 'PAYUSDK_OS_REJECTED';
          if ($response == 'ERROR' || $response == 'EXPIRED')
            $state = 'PAYUSDK_OS_FAILED';
          else if ($response == 'DECLINED')
            $state = 'PAYUSDK_OS_REJECTED';
          else if ($response == 'PENDING')
            $state = 'PAYUSDK_OS_PENDING';
          else if ($response == 'APPROVED')
            $state = 'PS_OS_PAYMENT';

            $id_state=(int)Configuration::get($state);

            $order = new Order((int)Order::getOrderByCartId((int)$idorder));
            $current_state = $order->current_state;

            if ($current_state != Configuration::get('PS_OS_PAYMENT'))
            {
              $history = new OrderHistory();
              $history->id_order = (int)$order->id;
			  $history->date_add = date("Y-m-d H:i:s");
              $history->changeIdOrderState((int)Configuration::get($state), (int)$order->id);
              $history->addWithemail(false);
            }
            if ($state != 'PS_OS_PAYMENT')
            {
              foreach ($order->getProductsDetail() as $product)
                StockAvailable::updateQuantity($product['product_id'], $product['product_attribute_id'], + (int)$product['product_quantity'], $order->id_shop);
            }

    }

    function execPayment($cart) {


        if (!$this->active)
            return;
        if (!$this->checkCurrency($cart))
            return;

        global $cookie, $smarty;

        if (isset($_POST['USR_MSG'])) {
            $msgpost = $_POST['USR_MSG'];
        } else {
            $msgpost = '';
        }
        if (isset($_GET['USR_MSG'])) {
            $msgget = $_GET['USR_MSG'];
        } else {
            $msgget = '';
        }

        $transid = $cart->id . "" . time();

        $addressdelivery = new Address(intval($cart->id_address_delivery));
        $addressbilling = new Address(intval($cart->id_address_invoice));

        $iso = Country::getIsoById($addressdelivery->id_country);

        if (Validate::isLoadedObject($addressdelivery) AND Customer::customerHasAddress(intval($cookie->id_customer), intval($cart->id_address_delivery))) {
            $smarty->assign(array(
                'SHIPPING_ADDRESS' => $addressdelivery->address1 . " " . $addressdelivery->address2,
                'SHIPPING_ADDRESS_COUNTRY_CODE' => Country::getIsoById($addressdelivery->id_country)
            ));
        }

        $smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'default_currency' => $cookie->id_currency,
            'currencies' => $this->getCurrency(),
            'total' => $cart->getOrderTotal(true, 3),
            'iva' => $cart->getOrderTotal(true, 3) - $cart->getOrderTotal(false, 3),
            'baseDevolucionIva' => $cart->getOrderTotal(false, 3),
            'accountId' => trim($this->accountId),
            'merchantId' => trim($this->merchantId),
            'apiKey' => trim($this->apiKey),
            'apiLogin' => trim($this->apiLogin),
            'isTest' => $this->isTest,
            'custip' => $this->getIP(),
            'p_billing_email' => $this->context->customer->email,
            'p_billing_name' => $this->context->customer->firstname,
            'p_billing_lastname' => $this->context->customer->lastname,
            'address' => $addressdelivery->address1,
            'address_1' => $addressdelivery->address2,
            'state' => State::getNameById($addressdelivery->id_state),
            'city' => $addressdelivery->city,
            'postal' => $addressdelivery->postcode,
            'phone' => $addressdelivery->phone,
            'msgpost1' => $msgpost,
            'this_path' => $this->_path,
            'sessionid' => md5(session_id().microtime()),
            'ajax' => Context::getContext()->link->getModuleLink('payusdk', "ajax_$iso"),
            'restore' => Context::getContext()->link->getModuleLink('payusdk', 'restore')
        ));

        return $this->display(__FILE__, 'payment_execution.tpl');
    }
    function hookPayment($params) {
      if (!$this->active) return;
	    if(!in_array($this->countryCode, $this->checkCountry()))
	    	return;
      if (!$this->checkCurrency($params['cart'])) return;
      $this->smarty->assign(array(
        'this_path' => $this->_path,
        'this_path_bw' => $this->_path,
        'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
      ));
      return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active)
            return;

	    if(!in_array($this->countryCode, $this->checkCountry()))
		    return;

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = [
            $this->getShowPayment(),
        ];

        return $payment_options;
    }

    public function getShowPayment()
    {
      $modalOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $modalOption->setCallToActionText($this->l(''))
                      ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                      ->setAdditionalInformation($this->context->smarty->fetch('module:payusdk/views/templates/front/payment_onpage.tpl'))
                      ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/logo-small.png'));

        return $modalOption;
    }

    public function hookPaymentReturn($params)
    {
      if (!$this->active) return;

      global $smarty, $cart, $cookie;

      $addressdelivery = new Address(intval($cart->id_address_delivery));
        $addressbilling = new Address(intval($cart->id_address_invoice));
      if (version_compare(_PS_VERSION_, '1.7.0.0 ', '<')){
      $order = $params['objOrder'];
      $value = $params['total_to_pay'];
      $currence = $params['currencyObj'];
      }else{
        $order = $params['order'];
        $value = $params['order']->getOrdersTotalPaid();
        $currence = new Currency($params['order']->id_currency);
      }
      $id_order = $_GET['id_order'];
      $extra1 = $order->id_cart;
      $extra2 = $id_order;
      $valorBaseDevolucion = $order->total_paid_tax_excl;
      $iva = $value - $valorBaseDevolucion;

      if ($iva == 0) $valorBaseDevolucion = 0;

      $currency = $this->getCurrency();
      $idcurrency = $order->id_currency;
      foreach ($currency as $mon) {
        if ($idcurrency == $mon['id_currency']) $currency = $mon['iso_code'];
      }

      $iso = Country::getIsoById($addressdelivery->id_country);

      //si no existe la moneda
      if ($currency == '') $currency = 'COP';

      $refVenta = $order->reference;

      $state = $order->getCurrentState();
      if ($state) {

        $smarty->assign(array(
          'this_path_bw' => $this->_path,
          'total_to_pay' => Tools::displayPrice($value, $currence, false),
          'refVenta' => $refVenta,
          'idorder' => $extra1,
          'extra2' => $extra2,
          'total' => $this->toAmount($value),
          'currency' => $currency,
          'iso' => $iso,
          'iva' => $iva,
          'textCustom' => 'Heelar',
          'baseDevolucionIva' => $valorBaseDevolucion,
          'accountId' => trim($this->accountId),
          'merchantId' => trim($this->merchantId),
          'apiKey' => trim($this->apiKey),
          'apiLogin' => trim($this->apiLogin),
          'isTest' => $this->isTest,
          'custip' => $this->getIP(),
          'custname' => ($cookie->logged ? $cookie->customer_firstname . ' ' . $cookie->customer_lastname : false),
          'p_billing_email' => $this->context->customer->email,
          'p_billing_name' => $this->context->customer->firstname,
          'p_billing_lastname' => $this->context->customer->lastname,
          'address' => $addressdelivery->address1,
          'address_1' => $addressdelivery->address2,
          'country' => $addressdelivery->country,
          'state' => State::getNameById($addressdelivery->id_state),
          'city' => $addressdelivery->city,
          'postal' => $addressdelivery->postcode,
          'phone' => $addressdelivery->phone,
          'sessionid' => md5(session_id().microtime()),
          'ajax' => Context::getContext()->link->getModuleLink('payusdk', "ajax_$iso"),
          'restore' => Context::getContext()->link->getModuleLink('payusdk', 'restore')
          )
        );

      } else {
          $smarty->assign('status', 'failed');
      }
      return $this->display(__FILE__, "payment_$iso.tpl");
    }

    public function hookHeader()
    {
        $process = $this->context->controller->addCSS(($this->_path) . 'css/payusdk.css', 'all');
        $process .= $this->context->controller->addJS(($this->_path) . 'js/payusdk.js', 'all');
	    $process .= $this->context->controller->addJS(($this->_path) . 'js/card.js', 'all');
        return $process;
    }


        /**
     * @param $value
     * @return string
     */
    public function toAmount($value)
    {
        $value = explode('.', $value);
        return $value[0];
    }

        /**
     * @return string
     */
    public function getIP()
    {
        return ($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '::' ||
            !preg_match('/^((?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])$/m',
                $_SERVER['REMOTE_ADDR'])) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency((int)($cart->id_currency));
        $currencies_module = $this->getCurrency((int)$cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function checkCountry()
    {
      return array('AR','CL','CO','MX','PA','PE','BR');
    }

}
