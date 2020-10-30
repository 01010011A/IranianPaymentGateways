<?php
require "bootstrap/autoload.php";

$zarinpal = new Payment(new Zarinpal("MerchantID"));
$zarinpal->setInfo([
    "Amount" =>"",
    "Description" =>"",
    "Email" =>"",
    "Mobile" =>"",
    "CallbackURL" =>""
]);
$zarinpal->pay();

$mellat = new Payment(new Mellat("terminalId","userName","userPassword"));
$mellat->setInfo([
    "amount" =>"",
    "callBackUrl" =>"",
    "additionalData" =>"",
    "payerId" =>""
]);
$mellat->pay();