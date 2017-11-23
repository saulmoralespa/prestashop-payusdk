<?php
/**
 * @since 1.5.0
 */
class PayusdkPaymentModuleFrontController extends ModuleFrontController
{
        public $ssl = true;
        public $display_column_left = false;
        public $display_column_right = false;

        /**
	 * @see FrontController::initContent()
	 */
        public function initContent()
        {
                parent::initContent();

                $cart = $this->context->cart;
                $addressdelivery = new Address(intval($cart->id_address_delivery));
                $addressbilling = new Address(intval($cart->id_address_invoice));


		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');

	        $total = $cart->getOrderTotal(true, Cart::BOTH);
	        $totalend = $this->module->toAmount($total);

                $this->context->smarty->assign(array(
                        'nbProducts' => $cart->nbProducts(),
                        'cust_currency' => $cart->id_currency,
                        'currencies' => $this->module->getCurrency((int)$cart->id_currency),
						'total' => $totalend,
                        'refventa' => uniqid(),
                        'iva' => $cart->getOrderTotal(true, Cart::BOTH) - $cart->getOrderTotal(false, Cart::BOTH),
                        'baseDevolucionIva' => $cart->getOrderTotal(false, Cart::BOTH),
                        'isoCode' => $this->context->language->iso_code,
                        'accountId' => trim($this->module->accountId),
                        'merchantId' => trim($this->module->merchantId),
			            'apiKey' => trim($this->module->apiKey),
			            'apiLogin' => trim($this->module->apiLogin),
			            'isTest' => $this->module->isTest,
                        'this_path' => $this->module->getPathUri(),
                        'this_path_bw' => $this->module->getPathUri(),
                        'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
                        'custip' => $this->module->getIP(),
                        'p_billing_email' => $this->context->customer->email,
                        'p_billing_name' => $this->context->customer->firstname,
                        'p_billing_lastname' => $this->context->customer->lastname,
                        'address' => $addressdelivery->address1,
                        'address_1' => $addressdelivery->address2,
                        'state' => State::getNameById($addressdelivery->id_state),
                        'city' => $addressdelivery->city,
                        'postal' => $addressdelivery->postcode,
                        'phone' => $addressdelivery->phone,
                        'sessionid' => md5(session_id().microtime()),
                        'ajax' => Context::getContext()->link->getModuleLink('payusdk', "ajax_{$this->module->countryCode}"),
                        'restore' => Context::getContext()->link->getModuleLink('payusdk', 'restore')
                ));

                $this->setTemplate("payment_{$this->module->countryCode}.tpl");
        }
}
?>
