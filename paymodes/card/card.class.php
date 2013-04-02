<?php
require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paymode.class.php';

/**
* A generic sample class for credic card payment
* not enabled in reality.
*/
class courseshop_paymode_card extends courseshop_paymode{

	function __construct(&$shopblockinstance){
		parent::__construct('card', $shopblockinstance, false);
	}
		
	// prints a payment porlet in an order form
	function print_payment_portlet(&$billdata){
	}

	// prints a payment porlet in an order form
	function print_invoice_info(&$billdata = null){
	}

	function print_complete(){
        echo compile_mail_template('billCompleteText', array(), 'block_courseshop') ; 
	}

	// processes a payment return
	function process(){		
	}

	// processes a payment asynchronoous confirmation
	function process_ipn(){
	}
	
	// provides global settings to add to courseshop settings when installed
	function settings(&$settings){
	}
	
}
