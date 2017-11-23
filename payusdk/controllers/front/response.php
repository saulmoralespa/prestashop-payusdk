<?php

class PayusdkResponseModuleFrontController extends ModuleFrontController
{
	public function initContent()
    {   
		parent::initContent();

		$this->context = Context::getContext();   

if (isset($_REQUEST['referenceCode']))
	$reference_code = $_REQUEST['referenceCode'];

if (isset($_REQUEST['TX_VALUE']))
	$value = $_REQUEST['TX_VALUE'];

if (isset($_REQUEST['currency']))

	$currency = $_REQUEST['currency'];

if (isset($_REQUEST['transactionState']))
	$transaction_state = $_REQUEST['transactionState'];

$value = explode('.', $value);
$value = $value[0];


if (isset($_REQUEST['polResponseCode']))
	$pol_response_code = $_REQUEST['polResponseCode'];

if (isset($_REQUEST['transactionId']))
	$transaction_id = $_REQUEST['transactionId'];

if (isset($_REQUEST['reference_pol']))
	$reference_pol = $_REQUEST['reference_pol'];

if (isset($_REQUEST['pseBank']))
	$pse_bank = $_REQUEST['pseBank'];

if (isset($_REQUEST['description']))
	$description = $_REQUEST['description'];

if (isset($_REQUEST['lapPaymentMethod']))
	$lap_payment_method = $_REQUEST['lapPaymentMethod'];

	$orderid = explode('-', $reference_code);
	$orderid = $orderid[1];
	$payusdk = new Payusdk;
	switch ($transaction_state) {
		case 4:
		$payusdk->PaymentSuccess('APPROVED',$orderid);
			$messageApproved = 'Transacción aprobada';
			break;
		case 6:
		$payusdk->PaymentSuccess('DECLINED',$orderid);
			$messageApproved = 'Transacción fallida';
			break;
		case 12:
		$payusdk->PaymentSuccess('PENDING',$orderid);
			$messageApproved = 'Transacción pendiente, por favor revisar si el débito fue realizado en el banco.';
			break;
	}

	
Context::getContext()->smarty->assign(
		array(
			'transactionId' => $transaction_id,
			'reference_pol' => $reference_pol,
			'referenceCode' => $reference_code,
			'pseBank' => $pse_bank,
			'value' => $value,
			'currency' => $currency,
			'description' => $description,
			'lapPaymentMethod' => $lap_payment_method,
			'messageApproved' => $messageApproved
		)
	);

        $this->setTemplate('response.tpl');
    }
}
?>