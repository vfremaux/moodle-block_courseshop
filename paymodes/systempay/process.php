<?php

// Get DATA param string from SystemPay API and redirect to shop

// Return_Context : view=shop&id={$this->shopblock->instance->id}&pinned={$this->shopblock->pinned}

include '../../../../config.php';
require_once $CFG->dirroot.'/blocks/courseshop/paymodes/systempay/systempay.class.php';
require_once $CFG->dirroot.'/blocks/courseshop/shop/lib.php';

// we cannot know yet which block instanceplays as infomation is in the mercanet
// cryptic answer. Process() decodes cryptic answer and get this context information to 
// go further.
$blockinstance = null;
$payhandler = new courseshop_paymode_systempay($blockinstance);

if ($_REQUEST['vads_result'] != SP_PURCHASE_CANCELLED){
	// process all cases, including payment failure with this credit card, 
	// so we can keep the order alive to be payed by another card.
	$payhandler->process();
} else {
	// explicit purchase cancellation on payment front end.
	$payhandler->cancel();	
}

?>