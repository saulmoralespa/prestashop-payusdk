<?php
class PayusdkValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		global $smarty;
		$cart = $this->context->cart;
		$response = array('status' => false);
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'payusdk')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		$mailVars = array();
		$this->module->validateOrder($cart->id, Payu_OrderState::getInitialState(), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
		$id_order = $this->module->currentOrder;
		$response = array('status' => true, 'id_order' => $id_order);
		$json = Tools::jsonEncode($response);
    	$this->ajaxDie($json);
	}
}