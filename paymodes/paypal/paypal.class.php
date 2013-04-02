<?php

require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paymode.class.php';
require_once $CFG->dirroot.'/blocks/courseshop/locallib.php';

class courseshop_paymode_paypal extends courseshop_paymode{

	function __construct(&$shopblockinstance){
		parent::__construct('paypal', $shopblockinstance, true, true);
	}
		
	// prints a payment porlet in an order form
	/*
	* @param object $portlet a data stub that contains required information for the portlet raster
	*/
	function print_payment_portlet(&$billdata){
		global $CFG;
		
		echo '<table id="paypal_panel"><tr><td>';
        echo compile_mail_template('paypalDoorTransferText', array(), 'block_courseshop/paymodes/paypal');
        echo '</td></tr>';
        echo '<tr><td align="right">';
		
   		$portlet->amount = $billdata->totaltaxedamount;
   		$portlet->firstname = $billdata->customer->firstname;
   		$portlet->lastname = $billdata->customer->lastname;
   		$portlet->city = $billdata->customer->city;
   		$portlet->country = $billdata->customer->country;
   		$portlet->mail = $billdata->customer->email;
   		$portlet->transactionid = $billdata->transactionid;
   		$portlet->shipping = $billdata->shipping;
   		include($CFG->dirroot.'/blocks/courseshop/paymodes/paypal/paypalAPI.portlet.php');

		echo '<p><span class="courseshop-procedure-cancel">X</span>';
		$cancelstr = get_string('cancel');
        echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/shop/view.php?step=shop&id={$this->shopblock->instance->id}&pinned={$this->shopblock->pinned}\" class=\"smalltext\">{$cancelstr}</a>";
        echo '</td></tr></table>';
	}
	
	// prints a payment porlet in an order form
	function print_invoice_info(&$billdata = null){
		echo get_string($this->name.'paymodeinvoiceinfo', $this->name, '', $CFG->dirroot.'/blocks/courseshop/paymodes/'.$this->name.'/lang/');
	}

	function print_complete(){
		echo compile_mail_template('paypalCompleteText', array(), 'block_courseshop') ;
	}

	/**
	* guesses it is a paypal transaction 
	*
	function identify_transaction(&$transid, &$cmd){
		$cmd = optional_param('cmd', '', PARAM_TEXT);
		if ($cmd == 'paypalback' || $cmd == 'paypalbackasync'){		
	    	$transid = required_param('invoice', PARAM_TEXT);
	    }
	}
	*/
	
	/**
	* Cancels the order and return to shop
	*/ 
	function cancel(){
		global $CFG, $SESSION;

		$instanceid = required_param('id', PARAM_INT);
		$pinned = required_param('pinned', PARAM_INT);
		$transid = required_param('transid', PARAM_RAW);
				
		// cancel shopping cart
		unset($SESSION->shoppingcart);

		// mark transaction (order record) as abandonned
	    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
	    if (!$instance = get_record($blocktable, 'id', $instanceid)){
	        error('Invalid block');
	    }
	    $theBlock = block_instance('courseshop', $instance);
		$aFullBill = courseshop_get_full_bill($transid, $theBlock);

	    $updatedbill->id = $aFullBill->id;
	    $updatedbill->onlinetransactionid = $transid;
	    $updatedbill->paymode = 'paypal';
	    $updatedbill->status = 'CANCELLED';
	    update_record('courseshop_bill', $updatedbill);

		redirect($CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=shop&id='.$instanceid.'&pinned='.$pinned);		
	}

	function process(){
		courseshop_trace('Paypal Return Controller');
		
		$transid = required_param('transid', PARAM_RAW);
		$instanceid = required_param('id', PARAM_INT);
		$pinned = optional_param('pinned', 0, PARAM_INT);

	    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
	    if (!$instance = get_record($blocktable, 'id', $instanceid)){
	        error('Invalid block');
	    }
	    $theBlock = block_instance('courseshop', $instance);
		$aFullBill = courseshop_get_full_bill($transid, $theBlock);
			
		// bill could already be SOLDOUT by IPN	so do nothing
		// process it only if needind to process.
		if ($aFullBill->status == 'DELAYED'){
		    // Bill has not yet been soldout nor produced by an IPN notification	
		    $updatedbill->id = $aFullBill->id;
		    $aFullBill->status = $updatedbill->status = 'PENDING';
		    update_record('courseshop_bill', $updatedbill);	
			courseshop_trace("[$transid] Paypal Return Controller Complete : Redirecting");
			redirect($CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=success&id='.$instanceid.'&pinned='.$pinned.'&transid='.$transid);
		}
	}

	// processes a payment asynchronoous confirmation
	function process_ipn(){
		global $CFG;
		
		/// get all input parms
		    
	    $transid = required_param('invoice', PARAM_TEXT);
	    list($instanceid, $pinned) = explode('-', required_param('custom', PARAM_TEXT)); // get the blockId and the pinned status of the block. We need them to reconstruct URLs in notifications
		
		// WE ned the block instance for the success.controller.php
	    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
	    if (!$instance = get_record($blocktable, 'id', $instanceid)){
	        error('Invalid block');
	    }
	    $theBlock = block_instance('courseshop', $instance);
		$aFullBill = courseshop_get_full_bill($transid, $theBlock);

	    $txnid = required_param('txn_id', PARAM_TEXT);
		
	    $data = new StdClass;
		
	    $validationquery = 'cmd=_notify-validate';
	    $querystring = '';
		
	    courseshop_trace("[$transid] Paypal IPN : paypal txn : $txnid");
	    courseshop_trace("[$transid] Paypal IPN : paypal trans : $transid");
		
	    foreach ($_POST as $key => $value) {
	        $value = stripslashes($value);
	        $querystring .= "&$key=".urlencode($value);
	        $data->$key = $value;
	    	courseshop_trace("[$transid] Paypal IPN : paypal $key : ".$value);
	    }
		    
	    $validationquery .= $querystring;
		   
	    // control for replicated notifications (normal operations)
	    if (empty($CFG->block_courseshop_test) && record_exists('courseshop_paypal_ipn', 'txnid', $txnid)){
	    	courseshop_trace("[$transid] Paypal IPN : paypal event collision on $txnid");
	        courseshop_email_paypal_error_to_admin("Paypal IPN : Transaction $txnid is being repeated.", $data);
	        die;
	    } else {
	        $paypalipn->txnid = $txnid;
	        $paypalipn->transid = $transid;
	        $paypalipn->paypalinfo = $querystring;
	        $paypalipn->result = '';
	        $paypalipn->timecreated = time();
	    	courseshop_trace("[$transid] Paypal IPN : Recording paypal event");
	        if (!insert_record('courseshop_paypal_ipn', $paypalipn)){
	    		courseshop_trace("[$transid] Paypal IPN : Recording paypal event error");
	    	}
	    }
		
	    $paypalurl = 'https://www.paypal.com/cgi-bin/webscr';
		
	    if (empty($CFG->block_courseshop_test)) {
		    // fetch the file on the consumer side and store it here through a CURL call    
		    $ch = curl_init("{$paypalurl}?$validationquery");
		    courseshop_trace("[$transid] Paypal IPN : sending validation request: "."{$paypalurl}?$validationquery");
		
		    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_POST, false);
		    curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
		    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	
			$rawresponse = curl_exec($ch);
		} else {
		    courseshop_trace("[$transid] Paypal IPN : faking validation request for test: "."{$paypalurl}?$validationquery");
			$rawresponse = 'VERIFIED'; // just for testing end of procedure
		}
		
	    if ($rawresponse){
	        if ($rawresponse == 'VERIFIED'){
	            
	            if ($data->payment_status != "Completed" and $data->payment_status != "Pending") {
	                courseshop_email_paypal_error_to_admin("Paypal IPN : Status not completed nor pending. Check transaction with customer.", $data);
	                if (!empty($CFG->block_courseshop_test)){
	                	mtrace("Paypal IPN : Status not completed nor pending. Check transaction with customer.");
	                } else {
	                	courseshop_trace("[$transid] Paypal IPN : Status not completed nor pending. Check transaction with customer.");
	                }
	                die;
	            }
	
				$sellerexpectedname = (empty($CFG->block_courseshop_test)) ? $CFG->block_courseshop_paypalsellername : $CFG->block_courseshop_paypalsellertestname ;
	
	            if ($data->business != $sellerexpectedname) {   // Check that the business account is the one we want it to be
	                courseshop_email_paypal_error_to_admin("Paypal IPN : Business email is $data->business (not $CFG->block_courseshop_paypalsellername)", $data);
	                if (!empty($CFG->block_courseshop_test)){
	                	mtrace("Paypal IPN : Business email is $data->business (not $CFG->block_courseshop_paypalsellername)");
	                } else {
	                	courseshop_trace("[$transid] Paypal IPN : Business email is $data->business (not $CFG->block_courseshop_paypalsellername)");
	                }
	                die;
	            }
	
	            set_field('courseshop_paypal_ipn', 'result', 'VERIFIED', 'txnid', $txnid);
	    		courseshop_trace("[$transid] Paypal IPN : Recording VERIFIED STATE on ".$txnid);
	            if (!empty($CFG->block_courseshop_test)){
	            	mtrace('Paypal IPN : Recording VERIFIED STATE on '.$txnid);
	            }

			    // Bill has not yet been soldout through an IPN notification
			    // sold it out and update both DB and memory record
			    if ($aFullBill->status != 'SOLDOUT'){    
			        // stores the back code of paypal
			        $tx = required_param('invoice', PARAM_TEXT);
			        $updatedbill->id = $aFullBill->id;
			        $aFullBill->onlinetransactionid = $updatedbill->onlinetransactionid = $tx;
			        $aFullBill->paymode = $updatedbill->paymode = 'paypal';
			        $aFullBill->status = $updatedbill->status = 'SOLDOUT';
			        $aFullBill->paymentfee = $updatedbill->paymentfee = 0 + @$data->mc_fee;
			        update_record('courseshop_bill', $updatedbill);
					courseshop_trace("[$transid] Success Controller : Paypal soldout record");

					// perform final production
					$cmd = '';
	            	include $CFG->dirroot.'/blocks/courseshop/shop/success.controller.php';
			    }
	            	
	    		courseshop_trace("[$transid] Paypal IPN : End of transaction");
	            if (!empty($CFG->block_courseshop_test)){
	            	mtrace('Paypal IPN : End of transaction');
	            }
	        }
	    } else {
			courseshop_trace('Paypal IPN : ERROR');		
	    }		
	}
	
	// provides global settings to add to courseshop settings when installed
	function settings(&$settings){
		global $CFG;

		$settings->add(new admin_setting_heading('block_courseshop_'.$this->name, get_string($this->name.'paymodeparams', $this->name, '', $CFG->dirroot.'/blocks/courseshop/paymodes/'.$this->name.'/lang/'), ''));

		$settings->add(new admin_setting_configtext('block_courseshop_paypalsellertestname', get_string('paypalsellertestname', 'block_courseshop'),
		                   get_string('configpaypalsellername', 'block_courseshop'), '', PARAM_TEXT));
		
		$settings->add(new admin_setting_configtext('block_courseshop_sellertestitemname', get_string('sellertestitemname', 'block_courseshop'),
		                   get_string('configselleritemname', 'block_courseshop'), '', PARAM_TEXT));

		$settings->add(new admin_setting_configtext('block_courseshop_paypalsellername', get_string('paypalsellername', 'block_courseshop'),
		                   get_string('configpaypalsellername', 'block_courseshop'), '', PARAM_TEXT));
		
		$settings->add(new admin_setting_configtext('block_courseshop_selleritemname', get_string('selleritemname', 'block_courseshop'),
		                   get_string('configselleritemname', 'block_courseshop'), '', PARAM_TEXT));

	}
	
}
