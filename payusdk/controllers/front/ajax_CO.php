<?php
date_default_timezone_set('America/Bogota');
include_once(_PS_MODULE_DIR_ . 'payusdk/lib/payU/PayU.php');
class Payusdkajax_COModuleFrontController extends ModuleFrontController
{
public function initContent()
{

    $response = array('status' => false);

    $payusdk = new Payusdk;

    if(isset($_POST['isTest'])){
       $params = array();
       $params = $_POST;
        $test = (boolean)$params['isTest'];
        if ($test) {
           $urlPay = 'https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi';
            $urlReport = 'https://sandbox.payulatam.com/reports-api/4.0/service.cgi';
            $subsUrl = 'https://sandbox.payulatam.com/payments-api/rest/v4.3/';
        }else{
            $urlPay = 'https://api.payulatam.com/payments-api/4.0/service.cgi';
            $urlReport = 'https://payulatam.com/reports-api/4.0/service.cgi';
            $subsUrl = 'https://payulatam.com/payments-api/rest/v4.3/';
        }
    }else{
       die('Not params received');
       $response = array('status' => false, 'message' => 'Parametros no recibidos');
    $json = Tools::jsonEncode($response);
    $this->ajaxDie($json);   
    }

    PayU::$apiKey = $params['apiKey'];
    PayU::$apiLogin = $params['apiLogin'];
    PayU::$merchantId = $params['merchantId'];
    PayU::$language = SupportedLanguages::ES;
    PayU::$isTest = $test;
    Environment::setPaymentsCustomUrl($urlPay);
    Environment::setReportsCustomUrl($urlReport); 
    Environment::setSubscriptionsCustomUrl($subsUrl);  

    $value = $params['total'];
    $orderid = $params['idorder'];

    if (isset($params['expiry'])) {

      $billing_address_2 = ($params["billing_address_2"] !='')?$params["billing_address_2"]:$params["billing_address_1"];
      $state = ($params["state"] !='') ? $params["state"]:$params["city"];

      $año = date('Y');
      $lenaño = substr($año, 0,2); 

    $expires = str_replace(' ', '', $params['expiry']);
    $expire = explode('/', $expires);
    $mes = $expire[0];

    if (strlen($mes) == 1) {
        $mes = '0' . $mes;     
    }
  
  $yearFinal =  strlen($expire[1]) == 4 ? $expire[1] :  $lenaño . substr($expire[1], -2);

    $datecaduce = $yearFinal . "/" . $mes;

    $cc_type = $params["cc_type"];

    $cc_number = str_replace(' ', '', $params['cc_number']);
    $parameters = array(
  //Ingrese aquí el identificador de la cuenta.
  PayUParameters::ACCOUNT_ID => $params['accountId'],
  //Ingrese aquí el código de referencia.
  PayUParameters::REFERENCE_CODE => $params['refventa'] . time(),
  //Ingrese aquí la descripción.
  PayUParameters::DESCRIPTION => $params['description'],

  // -- Valores --
  //Ingrese aquí el valor.        
  PayUParameters::VALUE => $value,
  //Ingrese aquí la moneda.
  PayUParameters::CURRENCY => "COP",

  // -- Comprador 
  //Ingrese aquí el nombre del comprador.
  PayUParameters::BUYER_NAME => $params['cc_name'],
  //Ingrese aquí el email del comprador.
  PayUParameters::BUYER_EMAIL => $params['p_billing_email'],
  //Ingrese aquí el teléfono de contacto del comprador.
  PayUParameters::BUYER_CONTACT_PHONE => $params['telephone'],
  //Ingrese aquí el documento de contacto del comprador.
  PayUParameters::BUYER_DNI => $params['dni'],
  //Ingrese aquí la dirección del comprador.
  PayUParameters::BUYER_STREET => $params['billing_address_1'],
  PayUParameters::BUYER_STREET_2 => $billing_address_2,
  PayUParameters::BUYER_CITY => $params['city'],
  PayUParameters::BUYER_STATE => $state,
  PayUParameters::BUYER_COUNTRY => 'CO',
  PayUParameters::BUYER_POSTAL_CODE => $params['postal'],
  PayUParameters::BUYER_PHONE => $params['telephone'],

  // -- pagador --
  //Ingrese aquí el nombre del pagador.
  PayUParameters::PAYER_NAME => "APPROVED",
  //Ingrese aquí el email del pagador.
  PayUParameters::PAYER_EMAIL => $params['p_billing_email'],
  //Ingrese aquí el teléfono de contacto del pagador.
  PayUParameters::PAYER_CONTACT_PHONE => $params['telephone'],
  //Ingrese aquí el documento de contacto del pagador.
  PayUParameters::PAYER_DNI => $params['dni'],
  //Ingrese aquí la dirección del pagador.
  PayUParameters::PAYER_STREET => $params['billing_address_1'],
  PayUParameters::PAYER_STREET_2 => $billing_address_2,
  PayUParameters::PAYER_CITY => $params['city'],
  PayUParameters::PAYER_STATE => $state,
  PayUParameters::PAYER_COUNTRY => 'CO',
  PayUParameters::PAYER_POSTAL_CODE => $params['postal'],
  PayUParameters::PAYER_PHONE => $params['telephone'],

  // -- Datos de la tarjeta de crédito -- 
  //Ingrese aquí el número de la tarjeta de crédito
  PayUParameters::CREDIT_CARD_NUMBER => $cc_number,
  //Ingrese aquí la fecha de vencimiento de la tarjeta de crédito
  PayUParameters::CREDIT_CARD_EXPIRATION_DATE => $datecaduce,
  //Ingrese aquí el código de seguridad de la tarjeta de crédito
  PayUParameters::CREDIT_CARD_SECURITY_CODE => $params['cc_cvc'],
  //Ingrese aquí el nombre de la tarjeta de crédito
  //VISA||MASTERCARD||AMEX||DINERS
  PayUParameters::PAYMENT_METHOD => $cc_type,

  //Ingrese aquí el número de cuotas.
  PayUParameters::INSTALLMENTS_NUMBER => "1",
  //Ingrese aquí el nombre del pais.
  PayUParameters::COUNTRY => PayUCountries::CO,

  //Session id del device.
  PayUParameters::DEVICE_SESSION_ID => $params['sessionid'],
  //IP del pagadador
  PayUParameters::IP_ADDRESS => $params['custip'],
  //Cookie de la sesión actual.
  PayUParameters::PAYER_COOKIE => md5($params['sessionid']),
  //Cookie de la sesión actual.        
  PayUParameters::USER_AGENT => $_SERVER['HTTP_USER_AGENT']
  );

    try{
    //solicitud de autorización y captura
$response = PayUPayments::doAuthorizationAndCapture($parameters);
    //$link = $this->context->link->getPageLink('history', true);

  
if ($response->code != "SUCCESS") {
  $estatus = array(
    'status' => false, 
    'tipo' => $cc_type,
    'message' => 'Error en el proceso, le informaremos via email',
    'transactionId' => ""
  ); 
    $json = Tools::jsonEncode($estatus);
    $this->ajaxDie($json);
}
if ($response->transactionResponse->state=="APPROVED") {
  $estatus = array(
    'status' => true, 
    'message' => 'El pago ha sido recibido exitosamente', 
    'estado' => "Aprobado",
    'tipo' => $cc_type,
    'transactionId' => $response->transactionResponse->transactionId,
    'description' => $params['description']
  );
    $payusdk->PaymentSuccess('APPROVED',$orderid);   
    $json = Tools::jsonEncode($estatus);
    $this->ajaxDie($json);
}elseif($response->transactionResponse->state=="PENDING") {
    $payusdk->PaymentSuccess('PENDING',$orderid);
    $estatus = array(
    'status' => true, 
    'message' => 'El pago se encuentra en estado pendiente', 
    'estado' => "Pendiente",
    'tipo' => $cc_type,
    'transactionId' => "",
    'description' => $params['description']
  );
    $json = Tools::jsonEncode($estatus);
    $this->ajaxDie($json);  
 }elseif($response->transactionResponse->state=="DECLINED") {
  $payusdk->PaymentSuccess('DECLINED',$orderid);
  $code = $response->transactionResponse->responseCode;
  $estatus = array(
    'status' => false, 
    'estado' => "Declinada",
    'tipo' => $cc_type,
    'message' => 'Transacción rechazada code: ' . $code,
    'transactionId' => "",
    'description' => $params['description']
  );
  $json = Tools::jsonEncode($estatus);
    $this->ajaxDie($json);
 }elseif($response->transactionResponse->state=="EXPIRED") {
  $payusdk->PaymentSuccess('EXPIRED',$orderid);
  $code = $response->transactionResponse->responseCode;
  $estatus = array(
    'status' => false, 
    'estado' => "Vencida",
    'tipo' => $cc_type,
    'message' => 'Transacción expirada code: ' . $code,
    'transactionId' => "",
    'description' => $params['description']
  );
  $json = Tools::jsonEncode($estatus);
    $this->ajaxDie($json);   

    }
  }catch(PayUException $ex){
     $estatus = array(
       'status' => false, 
       'estado' => "Error",
       'tipo' => $cc_type,
       'message' => $ex->getMessage(),
       'transactionId' => "",
       'description' => $params['description']
     );
      $json = Tools::jsonEncode($estatus);
      $this->ajaxDie($json); 
      }
  }elseif (isset($params['medium'])) {
      //expire order day
      $daily = '1';
      $parameters = array(
  //Ingrese aquí el identificador de la cuenta.
  PayUParameters::ACCOUNT_ID => $params['accountId'],
  //Ingrese aquí el código de referencia.
  PayUParameters::REFERENCE_CODE => $params['refventa'] . time(),
  //Ingrese aquí la descripción.
  PayUParameters::DESCRIPTION => $params['description'],
  PayUParameters::VALUE => $value,
  //Ingrese aquí la moneda.
  PayUParameters::CURRENCY => "COP",
  
  //Ingrese aquí el email del comprador.
  PayUParameters::BUYER_EMAIL => $params['p_billing_email'],
  //Ingrese aquí el nombre del pagador.
  PayUParameters::PAYER_NAME => $params['cc_name'],
  //Ingrese aquí el documento de contacto del pagador.
  PayUParameters::PAYER_DNI=> $params['dni'],

  //Ingrese aquí el nombre del método de pago
  PayUParameters::PAYMENT_METHOD => $params['medium'],
   
  //Ingrese aquí el nombre del pais.
  PayUParameters::COUNTRY => PayUCountries::CO,
  
  //Ingrese aquí la fecha de expiración.
  PayUParameters::EXPIRATION_DATE => $this->_dateExpire(),
  //IP del pagadador
  PayUParameters::IP_ADDRESS => $params['custip']   
  );

  try{
  $response = PayUPayments::doAuthorizationAndCapture($parameters);

  if ($response->transactionResponse->responseCode == 'DECLINED_TEST_MODE_NOT_ALLOWED') {
    $payusdk->PaymentSuccess('DECLINED',$orderid);
    $estatus = array(
    'status' => false, 
    'estado' => "Declinada",
    'tipo' => $params['medium'],
    'message' => 'Transacción declinada por estado modo de prueba',
    'transactionId' => "",
    'description' => $params['description']
  );
  $json = Tools::jsonEncode($estatus);
    $this->ajaxDie($json);
  }elseif ($response->transactionResponse->responseCode == 'PENDING_TRANSACTION_CONFIRMATION') {
    $payusdk->PaymentSuccess('PENDING',$orderid);
    $estatus = array(
    'status' => true, 
    'estado' => "Pendiente",
    'tipo' => $params['medium'],
    'message' => 'Transacción pendiente de pago',
    'transactionId' => $response->transactionResponse->transactionId,
    'description' => $params['description'],
    'urlhtml' => $response->transactionResponse->extraParameters->URL_PAYMENT_RECEIPT_HTML,
    'urlpdf' => $response->transactionResponse->extraParameters->URL_PAYMENT_RECEIPT_PDF
  );
  $json = Tools::jsonEncode($estatus);
    $this->ajaxDie($json);
    }
  }catch(PayUException $ex){
     $estatus = array(
       'status' => false, 
       'estado' => "Error",
       'tipo' => 'Efectivo',
       'message' => $ex->getMessage(),
       'description' => $params['description']
     );
      $json = Tools::jsonEncode($estatus);
      $this->ajaxDie($json); 
      }    

    }elseif(isset($params['psebanks'])){
      $parameters = array(
      //Ingrese aquí el identificador de la cuenta.
      PayUParameters::PAYMENT_METHOD => "PSE",
      //Ingrese aquí el nombre del pais.
      PayUParameters::COUNTRY => PayUCountries::CO,
    );
    
    try {
      $estatus = array('status' => true);
      $array=PayUPayments::getPSEBanks($parameters);
      $banks=$array->banks;
      $arrayMerge = array_merge($banks, $estatus);
      $json = Tools::jsonEncode($arrayMerge);
      $this->ajaxDie($json); 

    } catch (PayUException $e) {
      $estatus = array('status' => false);

    }
    }else{
    $parameters = array(
  //Ingrese aquí el identificador de la cuenta.
  PayUParameters::ACCOUNT_ID => $params['accountId'],
  //Ingrese aquí el código de referencia.
  PayUParameters::REFERENCE_CODE => $params['refventa'] . time() . "-$orderid",
  //Ingrese aquí la descripción.
  PayUParameters::DESCRIPTION => $params['description'],
  
  // -- Valores --
        //Ingrese aquí el valor de la transacción.
        PayUParameters::VALUE => $value,
  //Ingrese aquí la moneda.
  PayUParameters::CURRENCY => "COP",
  
  //Ingrese aquí el email del comprador.
  PayUParameters::BUYER_EMAIL => $params['p_billing_email'],
  //Ingrese aquí el nombre del pagador.
  PayUParameters::PAYER_NAME => $params['cc_name'],
  //Ingrese aquí el email del pagador.
  PayUParameters::PAYER_EMAIL => $params['p_billing_email'],
  //Ingrese aquí el teléfono de contacto del pagador.
  PayUParameters::PAYER_CONTACT_PHONE=> $params['telephone'],
       
  // -- infarmación obligatoria para PSE --
  //Ingrese aquí el código pse del banco.
  PayUParameters::PSE_FINANCIAL_INSTITUTION_CODE => $params['pse'],
  //Ingrese aquí el tipo de persona (N natural o J jurídica)
  PayUParameters::PAYER_PERSON_TYPE => $params['typepserson'],
  //Ingrese aquí el documento de contacto del pagador.
  PayUParameters::PAYER_DNI => $params['dni'],
  //Ingrese aquí el tipo de documento del pagador: CC, CE, NIT, TI, PP,IDC, CEL, RC, DE.
  PayUParameters::PAYER_DOCUMENT_TYPE => "CC",

  //Ingrese aquí el nombre del método de pago
  PayUParameters::PAYMENT_METHOD => "PSE",
   
  //Ingrese aquí el nombre del pais.
  PayUParameters::COUNTRY => PayUCountries::CO,
  
  //IP del pagadador
  PayUParameters::IP_ADDRESS => $params['custip'],
  //Cookie de la sesión actual.
  PayUParameters::PAYER_COOKIE => md5($params['sessionid']),
  //Cookie de la sesión actual.        
  PayUParameters::USER_AGENT => $_SERVER['HTTP_USER_AGENT'],
  
  //Página de respuesta a la cual será redirigido el pagador.     
  PayUParameters::RESPONSE_URL => Context::getContext()->link->getModuleLink('payusdk', 'response')
  
  );
    try{
  $response = PayUPayments::doAuthorizationAndCapture($parameters);

  if ($response->transactionResponse->responseCode == 'DECLINED_TEST_MODE_NOT_ALLOWED') {
    $payusdk->PaymentSuccess('DECLINED',$orderid);
    $estatus = array(
    'status' => false, 
    'estado' => "Declinada",
    'tipo' => $params['pse'],
    'message' => 'Transacción declinada por estado modo de prueba',
    'transactionId' => "",
    'description' => $params['description']
  );
  $json = Tools::jsonEncode($estatus);
    $this->ajaxDie($json);
  }elseif ($response->transactionResponse->responseCode == 'PENDING_TRANSACTION_CONFIRMATION') {
    $payusdk->PaymentSuccess('PENDING',$orderid);
    $estatus = array(
    'status' => true, 
    'estado' => "Pendiente",
    'tipo' => $params['pse'],
    'message' => 'Transacción pendiente de pago',
    'transactionId' => $response->transactionResponse->transactionId,
    'description' => $params['description'],
    'urlbank' => $response->transactionResponse->extraParameters->BANK_URL
  );
  $json = Tools::jsonEncode($estatus);
    $this->ajaxDie($json);
    }
  }catch(PayUException $ex){
     $estatus = array(
       'status' => false, 
       'estado' => "Error",
       'tipo' => 'Efectivo',
       'message' => $ex->getMessage(),
       'description' => $params['description']
     );
      $json = Tools::jsonEncode($estatus);
      $this->ajaxDie($json); 
      } 
    }
  }
  private function _dateExpire($day = '1'){
  $fecha = date('Y-m-d H:i:s');
  $nuevafecha = strtotime ( "+$day day" , strtotime ( $fecha ) ) ;
  $nuevafecha = date ( 'Y-m-d H:i:s' , $nuevafecha );
  $nuevafecha = str_replace(' ', 'T', $nuevafecha);
  return $nuevafecha;
  }
}