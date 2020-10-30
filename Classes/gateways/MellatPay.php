<?php
class Mellat implements Ipayment{
    const CLIENT_NAMESPACE = "http://interfaces.core.sw.bps.com";
    const CLIENT_URL='https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl';
    const WSDL = false;

    private $_info;
    private $_terminal_id;
    private $_username;
    private $_password;

    public function __construct($terminal_id,$username,$password)
    {
        $this->nusoap_client = new nusoap_client(self::CLIENT_URL, self::WSDL);  
        $this->_terminal_id=$terminal_id;
        $this->_username=$username;
        $this->_password=$password;
 
    }

    /**
     * setInfo
     *
     * @param  mixed 
     * @return void
     */
    function setInfo(array $info){
        $this->_info=$info;
        $this->_info["terminalId"]=$this->_terminal_id;
        $this->_info["userName"]=$this->_username;
        $this->_info["userPassword"]=$this->_username;
        $this->_info["localDate"]=date('Ymd');
        $this->_info["localTime"]=date('Gis');
        $this->_info["orderId"]=time();
        if(!isset($info["payerId"])){
            $this->_info["payerId"]="";
        }
        if(!isset($info["additionalData"])){
            $this->_info["additionalData"]="";
        }
    }

    function pay(){
        $result =$this->nusoap_client->call('bpPayRequest', $this->_info, self::CLIENT_NAMESPACE);
        //-- بررسی وجود خطا
        if ($this->nusoap_client->fault){
            //-- نمایش خطا
            echo "There was a problem connecting to Bank";
            exit;
        } 
        else{
            $err = $this->nusoap_client->getError();
        }

        if ($err)
        {
            //-- نمایش خطا
            echo "Error : ". $err;
            exit;
        }else{
            $res = explode (',',$result);
            $ResCode = $res[0];
            if ($ResCode == "0")
            {
                //-- انتقال به درگاه پرداخت
                echo '<form name="myform" action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat" method="POST">
                <input type="hidden" id="RefId" name="RefId" value="'. $res[1] .'">
                </form>
                <script type="text/javascript">window.onload = formSubmit; function formSubmit() { document.forms[0].submit(); }</script>';
                exit;
            }else{
                //-- نمایش خطا
                echo "Error : ". $result;
                exit;
            }
        }
    }

    public function verify(){
        if ($_POST['ResCode'] == '0') {
            //--پرداخت در بانک باموفقیت بوده

            $orderId = $_POST['SaleOrderId']; // Order ID
            $verifySaleOrderId = $_POST['SaleOrderId'];
            $verifySaleReferenceId = $_POST['SaleReferenceId'];
            
            $parameters = array(
            'terminalId' => $this->_terminal_id,
            'userName' => $this->_username,
            'userPassword' => $this->_username,
            'orderId' => $orderId,
            'saleOrderId' => $verifySaleOrderId,
            'saleReferenceId' => $verifySaleReferenceId);
            // Call the SOAP method
            $result = $this->nusoap_client->call('bpVerifyRequest', $parameters, self::CLIENT_NAMESPACE);
            if($result == '0') {
                //-- وریفای به درستی انجام شد٬ درخواست واریز وجه
                // Call the SOAP method
                $result = $this->nusoap_client->call('bpSettleRequest', $parameters, self::CLIENT_NAMESPACE);
                if($result == '0') {
                    //-- تمام مراحل پرداخت به درستی انجام شد.
                    //-- آماده کردن خروجی
                    echo 'The transaction was successful';
                } else {
                    //-- در درخواست واریز وجه مشکل به وجود آمد. درخواست بازگشت وجه داده شود.
                    $this->nusoap_client->call('bpReversalRequest', $parameters, self::CLIENT_NAMESPACE); 
                    echo 'Error : '. $result;
                }
            } else {
                //-- وریفای به مشکل خورد٬ نمایش پیغام خطا و بازگشت زدن مبلغ
                $this->nusoap_client->call('bpReversalRequest', $parameters,self::CLIENT_NAMESPACE);
                echo 'Error : '. $result;
            }
        }else {
            //-- پرداخت با خطا همراه بوده
            echo 'Error : '. $_POST['ResCode'];
        }
    }

}