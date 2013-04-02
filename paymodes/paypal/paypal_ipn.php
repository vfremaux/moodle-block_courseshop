<?php

// get the context back

/**
* this process implements the IPN handler for Paypal asynchronous returns.
* It has no reference to block id.
*/

    include '../../../../config.php';
	require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paypal/paypal.class.php';
	require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paypal/ipn_lib.php';
	include_once $CFG->dirroot.'/blocks/courseshop/shop/lib.php';

	// Setup trace

	debug_trace('Success Controller : IPN Paypal return');

	// Keep eventual intruders out

    if (empty($_POST) or !empty($_GET)) {
        error("Sorry, you can not use the script that way.");
    }
   
	$blockinstance = null;
	$payhandler = new courseshop_paymode_paypal($blockinstance);
	$payhandler->process_ipn();
	
?>