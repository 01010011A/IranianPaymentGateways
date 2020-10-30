<?php
class Payment{
    private $gateway;
    function __construct(Ipayment $gateway)
    {
        $this->gateway=$gateway;
    }

    function setInfo($info){
        $this->gateway->setInfo($info);
    }
    function pay(){
        $this->gateway->pay();
    }
    function verify(){
        $this->gateway->verify();
    }
}