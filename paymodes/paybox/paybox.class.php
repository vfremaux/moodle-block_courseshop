<?php
require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paymode.class.php';

class courseshop_paymode_paybox extends courseshop_paymode{

	function __construct(&$shopblockinstance){
		// to enable paybox in your installation, change second param to "true"
		parent::__construct('paybox', $shopblockinstance, false, true);
	}
		
	// prints a payment porlet in an order form
	function print_payment_portlet(&$billdata){
		echo '<p>Not implemeted Yet!</p> ';
	}

	// prints a payment porlet in an order form
	function print_invoice_info(&$billdata = null){
		echo get_string($this->name.'paymodeinvoiceinfo', $this->name, '', $CFG->dirroot.'/blocks/courseshop/paymodes/'.$this->name.'/lang/');
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
		global $CFG;
		
		$settings->add(new admin_setting_heading('block_courseshop_'.$this->name, get_string($this->name.'paymodeparams', 'block_courseshop', '', $CFG->dirroot.'/blocks/courseshop/paymodes/'.$this->name.'/lang/'), ''));

	}
	
}
