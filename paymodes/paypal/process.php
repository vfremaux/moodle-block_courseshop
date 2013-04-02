<?php

//Get DATA param string from Payal API and redirect to shop

include '../../../../config.php';
require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paypal/paypal.class.php';
require_once $CFG->dirroot.'/blocks/courseshop/shop/lib.php';

$blockinstance = null;
$payhandler = new courseshop_paymode_paypal($blockinstance);
$payhandler->process();

?>