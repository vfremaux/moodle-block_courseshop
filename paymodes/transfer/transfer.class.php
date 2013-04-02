<?php
require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paymode.class.php';

class courseshop_paymode_transfer extends courseshop_paymode{

	function __construct(&$shopblockinstance){
		parent::__construct('transfer', $shopblockinstance);
	}
		
	// prints a payment porlet in an order form
	function print_payment_portlet(&$billdata){
		global $CFG;
		$proc = 1;
		
		echo '<p>' . compile_mail_template('payInstructions', array(), 'block_courseshop/paymodes/transfer');
		echo compile_mail_template('printProcedureText', array(
			'SELLER' => $CFG->block_courseshop_sellername,
            'ADDRESS' => $CFG->block_courseshop_sellerbillingaddress,
            'ZIP' => $CFG->block_courseshop_sellerbillingzip,
            'CITY' => $CFG->block_courseshop_sellerbillingcity,
            'COUNTRY' => strtoupper($CFG->block_courseshop_sellercountry),
            'BANKING' => $CFG->block_courseshop_banking,
            'BANK_CODE' => $CFG->block_courseshop_bankcode,
            'BANK_OFFICE' => $CFG->block_courseshop_bankoffice,
            'BANK_ACCOUNT' => $CFG->block_courseshop_bankaccount,
            'ACCOUNT_KEY' =>  $CFG->block_courseshop_bankaccountkey,
            'IBAN' =>  $CFG->block_courseshop_iban,
            'BIC' =>  $CFG->block_courseshop_bic,
            'TVA_EUROPE' =>  $CFG->block_courseshop_tvaeurope,
            'PROC_ORDER' => $proc++  ), 'block_courseshop/paymodes/transfer');
	}

	// prints a payment porlet in an order form
	function print_invoice_info(&$billdata = null){
		global $CFG;
		$proc = 1;
		
		echo '<p>' . compile_mail_template('payInstructions_invoice', array(), 'block_courseshop/paymodes/transfer');
		echo compile_mail_template('printProcedureText_invoice', array(
			'SELLER' => $CFG->block_courseshop_sellername,
            'ADDRESS' => $CFG->block_courseshop_sellerbillingaddress,
            'ZIP' => $CFG->block_courseshop_sellerbillingzip,
            'CITY' => $CFG->block_courseshop_sellerbillingcity,
            'COUNTRY' => strtoupper($CFG->block_courseshop_sellercountry),
            'BANKING' => $CFG->block_courseshop_banking,
            'BANK_CODE' => $CFG->block_courseshop_bankcode,
            'BANK_OFFICE' => $CFG->block_courseshop_bankoffice,
            'BANK_ACCOUNT' => $CFG->block_courseshop_bankaccount,
            'ACCOUNT_KEY' =>  $CFG->block_courseshop_bankaccountkey,
            'IBAN' =>  $CFG->block_courseshop_iban,
            'BIC' =>  $CFG->block_courseshop_bic,
            'TVA_EUROPE' =>  $CFG->block_courseshop_tvaeurope,
            'PROC_ORDER' => $proc++  ), 'block_courseshop/paymodes/transfer');
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
