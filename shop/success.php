<?php

    // Security

    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    require_once $CFG->dirroot.'/blocks/courseshop/mailtemplatelib.php';
    require_once $CFG->dirroot.'/blocks/courseshop/locallib.php';

	// 	resolving invoice identity and command
	$transid = '';
	$cmd = '';
	/**
	$payplugin = paymode::resolve_transaction_identification($transid, $cmd, $paymode);
	*/
	$transid = required_param('transid', PARAM_RAW);
	$cmd = optional_param('cmd', '', PARAM_TEXT);
	$aFullBill = courseshop_get_full_bill($transid, $theBlock);
	/* 
	All payment process is supposed to be done here !!
	success is only for giving interactive answer to user.
	$payplugin->process($cmd, $aFullBill, $theBlock); 
	*/
    
    if ($cmd != ''){
    	$instanceid = $id; // unify interactive and non interactive processing
      	include_once $CFG->dirroot.'/blocks/courseshop/shop/success.controller.php';
    }

    $supports = array();
    if ($CFG->block_courseshop_sellermailsupport) $supports[] = get_string('byemailat', 'block_courseshop'). ' '. $CFG->block_courseshop_sellermailsupport;
    if ($CFG->block_courseshop_sellerphonesupport) $supports[] = get_string('byphoneat', 'block_courseshop'). ' '. $CFG->block_courseshop_sellerphonesupport;
    
    $supportstr = implode(' '.get_string('or', 'block_courseshop').' ', $supports);
        
    if ($aFullBill->status == 'SOLDOUT' || $aFullBill->status == 'COMPLETE'){

	    echo '<center>';
	    courseshop_print_progress('BILL');
	    echo '</center>';

        echo '<center>';
        print_box_start();
        echo $CFG->block_courseshop_sellername.' ';
        echo compile_mail_template('postBillingMessage', array(), 'block_courseshop');
        
		echo compile_mail_template('successFollowUpText', array('SUPPORT' => $supportstr), 'block_courseshop/paymodes/'.$aFullBill->paymode);
        print_box_end();

		// a specific report
		if (!empty($aFullBill->productiondata->public)){
        	print_box_start();
		    echo $aFullBill->productiondata->public;
        	print_box_end();
		}
	} else {
	    echo '<center>';
	    courseshop_print_progress('PENDING');
	    echo '</center>';

        print_box_start();
        echo $CFG->block_courseshop_sellername.' ';
        echo compile_mail_template('postBillingMessage', array(), 'block_courseshop');
		echo compile_mail_template('pendingFollowUpText', array('SUPPORT' => $supportstr), 'block_courseshop/paymodes/'.$aFullBill->paymode);
        print_box_end();
	}
	courseshop_print_printable_bill_link($aFullBill->id, $transid, $id, $pinned);
	
	// if testing the shop, provide a manual link to generate the paypal_ipn call
	if ($CFG->block_courseshop_test && $aFullBill->paymode == 'paypal'){
		require_once($CFG->dirroot.'/blocks/courseshop/paymodes/paypal/ipn_lib.php');
		paypal_print_test_ipn_link($aFullBill->id, $transid, $id, $pinned);
	}

