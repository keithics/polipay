<?php
/* ASCII FTW!
 __        __         _         _   _   _             _           
 \ \      / /   ___  | |__     | \ | | (_)  _ __     (_)   __ _   
  \ \ /\ / /   / _ \ | '_ \    |  \| | | | | '_ \    | |  / _` |  
   \ V  V /   |  __/ | |_) |   | |\  | | | | | | |   | | | (_| |  
    \_/\_/     \___| |_.__/    |_| \_| |_| |_| |_|  _/ |  \__,_|  
                                                   |__/           
  __  __           _       _   _                                  
 |  \/  |   ___   | |__   (_) | |   ___                           
 | |\/| |  / _ \  | '_ \  | | | |  / _ \                          
 | |  | | | (_) | | |_) | | | | | |  __/                          
 |_|  |_|  \___/  |_.__/  |_| |_|  \___|                          

  2013 Keith Levi Lumanog @polipayTM
  More info @ http://www.polipayments.com/developer

    date_default_timezone_set('Asia/Manila');

    $poli = new Polipay();
    echo '<pre>';
    print_r($poli);
    echo '</pre>';

*/


class Polipay
{

    private $authCode;
    private $mechantCode;
    private $currencyCode = 'AUD';
    private $checkourUrl = 'http://webninjamobile.com/checkout';
    private $homepage = 'http://webninjamobile.com';
    private $notificationUrl = 'http://webninjamobile.com/notification';
    private $successUrl = 'http://webninjamobile.com/success';
    private $timeOut = 1000;
    private $failUrl = 'http://webninjamobile.com/fail/';

    public function __construct($debug = true)
    {

        $this->authCode = $debug ? '<DEBUG MERCHANT CODE>' : '<LIVE MERCHANT CODE>';
        $this->mechantCode = $debug ? '<DEBUG MERCHANT CODE>' : '<LIVE MERCHANT CODE>';
    }

    function pay($amount)
    {

        $url = "https://merchantapi.apac.paywithpoli.com/MerchantAPIService.svc/Xml/transaction/initiate"; //Set url to the initiate endpoint. Check carefully.
        $xml_builder = '<?xml version="1.0" encoding="utf-8"?>
    <InitiateTransactionRequest 
    xmlns="http://schemas.datacontract.org/2004/07/Centricom.POLi.Services.MerchantAPI.Contracts"
    xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
        <AuthenticationCode>' . $this->authCode . '</AuthenticationCode>
        <Transaction xmlns:a="http://schemas.datacontract.org/2004/07/Centricom.POLi.Services.MerchantAPI.DCO">
        <a:CurrencyAmount>' . $amount . '</a:CurrencyAmount>
        <a:CurrencyCode>' . $this->currencyCode . '</a:CurrencyCode>
        <a:MerchantCheckoutURL>' . $this->checkourUrl . '</a:MerchantCheckoutURL>
        <a:MerchantCode>' . $this->mechantCode . '</a:MerchantCode>
        <a:MerchantData>MerchantDataAssociatedWithTransaction</a:MerchantData>
        <a:MerchantDateTime>' . date("Y-m-d\TH:i:s") . '</a:MerchantDateTime>
        <a:MerchantHomePageURL>' . $this->homepage . '</a:MerchantHomePageURL>
        <a:MerchantRef>MerchantReferenceAssociateWithTransaction</a:MerchantRef>
        <a:MerchantReferenceFormat>MerchantReferenceFormat</a:MerchantReferenceFormat>
        <a:NotificationURL>' . $this->notificationUrl . '</a:NotificationURL>
        <a:SelectedFICode i:nil="true" />
        <a:SuccessfulURL>' . $this->successUrl . '</a:SuccessfulURL>
        <a:Timeout>' . $this->timeOut . '</a:Timeout>
        <a:UnsuccessfulURL>' . $this->failUrl . '</a:UnsuccessfulURL>
        <a:UserIPAddress>' . $_SERVER["REMOTE_ADDR"] . '</a:UserIPAddress>
        </Transaction>
      </InitiateTransactionRequest>';
        //Check it carefully. Date formatting, link formatting.

        $ch = curl_init($url); //Start a cURL on the url

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //SSL related
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //SSL related

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:  text/xml')); //Set the request type to xml.
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        curl_setopt($ch, CURLOPT_POST, 1); //Turn on post data
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_builder); //Set the post data to the xml
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch); //Execute cURL, save to response

        curl_close($ch); //Save system resources by closing the cURL

        $xml = new SimpleXMLElement($response); //Save the response as XML

        //$namespaces = $xml->getNamespaces(TRUE);
        //var_dump($namespaces);

//        header('Content-Type: text/xml');
//        echo $response; die;


        $xml->registerXPathNamespace('', 'http://schemas.datacontract.org/2004/07/Centricom.POLi.Services.MerchantAPI.Contracts');
        $xml->registerXPathNamespace('a', 'http://schemas.datacontract.org/2004/07/Centricom.POLi.Services.MerchantAPI.DCO'); //This is the important one.
        $xml->registerXPathNamespace('i', 'http://www.w3.org/2001/XMLSchema-instance'); //These allow accessing of xpathing on the xml

        $data = array();
        foreach ($xml->xpath('//a:NavigateURL') as $value) {
            $data['url'] = $value;
        }
        foreach ($xml->xpath('//a:TransactionToken') as $value) {
            $data['token'] = $value;
        }

        foreach ($xml->xpath('//a:Message') as $value) {
            $data['error'] = (String) $value;
        }


        return $data;
    }
}