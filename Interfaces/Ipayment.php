<?php
interface Ipayment{
    public function pay();
    public function setInfo(array $info);
    public function verify();
}