<?php

/**
* This abstract class emplements an object wrapper for a payment method
* in the courseshop block
*
* All payment method should be a subclass of this class.
* A payment method provides callbacks that execute specific code
* for this payment mode.
* 
*/

abstract class courseshop_paymode {
	
	var $name;
	var $enabled;
	var $overridelocalconfirm;
	var $shopblock;
	var $interactive; // after processing will tell if the transaction is handled in interactive mode
	
	function __construct($name, &$shopblockinstance, $enabled = true, $overridelocalconfirm = false){
		$this->name = $name;
		$this->shopblock = $shopblockinstance;
		$this->enabled = $enabled;
		$this->overridelocalconfirm = $overridelocalconfirm;
		$this->interactive = true;
	}
	
	// prints a payment portlet in an order form
	abstract function print_payment_portlet(&$billdata);

	// prints a payment info on an invoice
	abstract function print_invoice_info(&$billdata = null);

	// prints a message when transaction is complete
	abstract function print_complete();

	// processes a payment return
	abstract function process();

	// processes a payment asynchronoous confirmation
	abstract function process_ipn();
	
	// provides global settings to add to courseshop settings when installed
	abstract function settings(&$settings);	

	// provides global settings to add to courseshop settings when installed
	function print_instance_config(){
		global $CFG;
		
		$isenabledvar = "enable".$this->name;
		$checked = (@$this->shopblock->config->$isenabledvar) ? 'checked="checked"' : '' ;
		echo "<input type=\"checkbox\" name=\"$isenabledvar\" value=\"1\" ";
		echo $checked;
		echo '/>';
		echo get_string($isenabledvar, $this->name, '', $CFG->dirroot.'/blocks/courseshop/paymodes/'.$this->name.'/lang/');
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$defaultchecked = (@$this->shopblock->config->defaultpaymode == $this->name) ? 'checked="checked"' : '' ;
		print_string('isdefault', 'block_courseshop');
		echo "<input type=\"radio\" name=\"defaultpaymode\" value=\"{$this->name}\" ";
		echo $defaultchecked;
		echo '/>';
		echo '<br/>';
	}
	
	/**
	* trivial accessor
	*/
	function get_name(){
		return $this->name;
	}

	/**
	* printable name
	*/
	function print_name(){
		echo get_string($this->name, 'block_courseshop');
	}

	// get a mail template	
	function get_mail($mailtype, $data){
	}
	
	function needslocalconfirm(){
		return !$this->overridelocalconfirm;
	}
	
	public static function resolve_transaction_identification(&$transid, &$cmd, &$paymode){
		$plugins = courseshop_paymode::courseshop_get_plugins(null);
		$transid = '';
		$cmd = '';
		foreach($plugins as $plugin){
			$plugin->identify_transaction($transid, $cmd);
			if (!empty($transid)){
    			$paymode = strtolower(get_field('courseshop_bill', 'paymode', 'transactionid', $transid));
    			if ($paymode != $plugin->get_name()){
    				$transid = '';
    				$cmd = '';
    				error(get_string('paymodedonotmatchtoresponse', 'block_courseshop'));
    			}
    			// we have valid transid and cmd and paymode, so process it in controller
				return $plugin;
			}
		}
	}

	// get all payment plugins
	public static function courseshop_get_plugins(&$shopblockinstance){
		global $CFG;
		
		$plugins = get_list_of_plugins('/blocks/courseshop/paymodes', 'CVS');
		foreach($plugins as $p){
			include_once $CFG->dirroot.'/blocks/courseshop/paymodes/'.$p.'/'.$p.'.class.php';
			$classname = "courseshop_paymode_$p";
			$payments[$p] = new $classname($shopblockinstance);
		}
		
		return $payments;
	}
	
	// get all payment plugins
	public static function courseshop_add_paymode_settings(&$settings){
		global $CFG;
		
		$plugins = get_list_of_plugins('/blocks/courseshop/paymodes', 'CVS');
		foreach($plugins as $p){
			include_once $CFG->dirroot.'/blocks/courseshop/paymodes/'.$p.'/'.$p.'.class.php';
			$classname = "courseshop_paymode_$p";
			$blockinstance = null;
			$pm = new $classname($blockinstance); // no need of real shop instances here
			if ($pm->enabled){
				$pm->settings($settings);
			}
		}	
	}
}