<?php

/// get all input parms
include '../../../../config.php';
require_once $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/systempay.class.php';
require_once $CFG->dirroot.'/blocks/courseshop/shop/lib.php';

/// Setup trace

    courseshop_trace('SystemPay Autoresponse (IPN) : Open systempaybacksession');

/// Keep out casual intruders 

    if (empty($_POST) or !empty($_GET)) {
        error("Sorry, you can not use the script that way. POST values are expected.");
    }

// we cannot know yet which block instanceplays as infomation is in the mercanet
// cryptic answer. Process_ipn() decodes cryptic answer and get this context information to 
// go further.

	$blockinstance = null;
	$payhandler = new courseshop_paymode_systempay($blockinstance);
	
	/// check we expect a payment
	
	if ($_POST['vads_operation_type'] != 'CREDIT'){
		courseshop_trace("SystemPay IPN : Unsupported DEBIT operation : Operation was ".$_POST['vads_operation_type']);
		die;
	}

	/// check request validity
	
	$certificate = ($CFG->block_courseshop_test) ? @$CFG->block_courseshop_systempay_test_certificate : @$CFG->block_courseshop_systempay_prod_certificate ;
	$expected = $payhandler->generate_sign($_POST, $certificate);
	if ($expected == $_POST['signature']){	
		$payhandler->process_ipn();
	} else {
		courseshop_trace("SystemPay IPN : Invalid request signature");
		die;
	}

 