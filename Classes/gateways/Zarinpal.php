<?php
class Zarinpal implements Ipayment{
    const CLIENT_URL='https://www.zarinpal.com/pg/services/WebGate/wsdl';
    const WSDL = 'wsdl';

    private $_merchant_id;

    private $info;

    function __construct($merchent_id)
    {
        $this->nusoap_client = new nusoap_client(self::CLIENT_URL, self::WSDL);   
        $this->nusoap_client->soap_defencoding = 'UTF-8';
        $this->_merchant_id=$merchent_id;
    }

    function setInfo(array $info){
        $this->info=$info;
        $this->info["MerchantID"]=$this->_merchant_id;
    }

    function pay(){
        $result = $this->nusoap_client->call('PaymentRequest', [$this->info]);
        //Redirect to URL You can do it also by creating a form
        if ($result['Status'] == 100) {
            header('Location: https://www.zarinpal.com/pg/StartPay/'.$result['Authority']);
        } else {
            echo'ERR: '.$result['Status'];
        }
    }

    function verify(){
        $Authority = $_GET['Authority'];
    
        if ($_GET['Status'] == 'OK') {
            $result = $this->nusoap_client->call('PaymentVerification', [
                [
                    'MerchantID'     => $this->_merchant_id,
                    'Authority'      => $Authority,
                    'Amount'         => $this->info["Amount"],
                ],
            ]);
    
            if ($result['Status'] == 100) {
                echo 'Transation success. RefID:'.$result['RefID'];
            } else {
                echo 'Transation failed. Status:'.$result['Status'];
            }
        } else {
            echo 'Transaction canceled by user';
        }
    }


}





