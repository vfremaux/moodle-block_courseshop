<?php
require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paymode.class.php';

class courseshop_paymode_check extends courseshop_paymode{
	
	function __construct(&$shopblockinstance){
		parent::__construct('check', $shopblockinstance);
	}
	
	// prints a payment porlet in an order form
	function print_payment_portlet(&$billdata){
		global $CFG;
		
		$proc = 1;
		echo '<p>' . compile_mail_template('payInstructions', array(), 'block_courseshop/paymodes/check');
		echo '<blockquote>'; 
		echo compile_mail_template('printProcedureText', array( 'PROC_ORDER' => $proc++ ), 'block_courseshop/paymodes/check');
		echo compile_mail_template('chkProcedureText', array(
			'SELLER' => $CFG->block_courseshop_sellername,
           	'ADDRESS' => $CFG->block_courseshop_selleraddress,
           	'ZIP' => $CFG->block_courseshop_sellerzip,
           	'CITY' => $CFG->block_courseshop_sellercity,
           	'COUNTRY' => strtoupper($CFG->block_courseshop_sellercountry),
           	'PROC_ORDER' => $proc++ ), 'block_courseshop/paymodes/check');
		echo '</blockquote>';
	}

	function print_invoice_info(&$billdata = null){
		global $CFG;

		$proc = 1;
		echo '<p>' . compile_mail_template('payInstructions_invoice', array(), 'block_courseshop/paymodes/check');
		echo '<blockquote>'; 
		echo compile_mail_template('printProcedureText_invoice', array( 'PROC_ORDER' => $proc++ ), 'block_courseshop/paymodes/check');
		echo compile_mail_template('chkProcedureText_invoice', array(
			'SELLER' => $CFG->block_courseshop_sellername,
           	'ADDRESS' => $CFG->block_courseshop_selleraddress,
           	'ZIP' => $CFG->block_courseshop_sellerzip,
           	'CITY' => $CFG->block_courseshop_sellercity,
           	'COUNTRY' => strtoupper($CFG->block_courseshop_sellercountry),
           	'PROC_ORDER' => $proc++ ), 'block_courseshop/paymodes/check');
		echo '</blockquote>';
	}		
	
	function print_complete(){
        echo compile_mail_template('billCompleteText', array(), 'block_courseshop') ; 
	}

	// processes a payment return
	function process(){		
	}

	// processes a payment asynchronoous confirmation
	function process_ipn(){
		// no IPN for offline payment.
	}
	
	// provides global settings to add to courseshop settings when installed
	function settings(&$settings){
	}
	
}
