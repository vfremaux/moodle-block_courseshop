<?php

require_once $CFG->dirroot.'/auth/ticket/lib.php';
require_once $CFG->dirroot.'/blocks/courseshop/mailtemplatelib.php';

/*
* perform a transition from state to state for a workflowed object
*/
function bill_transition_PENDING_SOLDOUT($info){
    global $CFG, $SITE, $USER;
  
    // Scenario : 
    /*
    * the order is being payed offline. 
    * the operator needs to sold out manually the bill and realize all billitems production
    * info : the billid.
    */  
    
    if ($bill = get_record('courseshop_bill', 'id', $info->billid)){

		// Start marking soldout status. Final may be COMPLETE if production occurs
		set_field('courseshop_bill', 'status', 'SOLDOUT', 'id', $info->billid);    
		courseshop_trace("[{$bill->transactionid}] Bill Controller : Transaction Soldout Operation on seller behalf by $USER->username");	

        $bill->billItems = get_records('courseshop_billitem', 'billid', $bill->id);
        include $CFG->dirroot.'/blocks/courseshop/datahandling/production.php';
        $customer = get_record('courseshop_customer', 'id', $bill->userid);
        $bill->foruser = $customer->hasaccount;
        $bill->user = get_record('user', 'id', $customer->hasaccount);
        $bill->blockid = $info->blockid;
        $productiondata = produce_postpay($bill);

        courseshop_aggregate_production($bill, $productiondata, true);
        
        print_box_start();
        echo $productiondata->salesadmin;
        print_box_end();

		// now notify user the order and all products have been activated
		if (!empty($productiondata->private)){
			courseshop_trace("[{$bill->transactionid}] Bill Controller : Transaction Autocompletion Operation on seller behalf by $USER->username");	
	
	        // notify end user
	    	// feedback customer with mail confirmation.
	    	$notification  = compile_mail_template('salesFeedback', array('SERVER' => $SITE->shortname,
	                                                                   'SERVER_URL' => $CFG->wwwroot,
	                                                                   'SELLER' => $CFG->block_courseshop_sellername,
	                                                                   'FIRSTNAME' => $customer->firstname,
	                                                                   'LASTNAME' =>  $customer->lastname,
	                                                                   'MAIL' => $customer->email,
	                                                                   'CITY' => $customer->city,
	                                                                   'COUNTRY' => $customer->country,
	                                                                   'ITEMS' => count($bill->billItems),
	                                                                   'PAYMODE' => get_string($bill->paymode, 'block_courseshop'),
	                                                                   'AMOUNT' => $bill->amount), 
	                                               'block_courseshop');
	    	$customerBillViewUrl = $CFG->wwwroot . "/blocks/courseshop/shop/view.php?id={$info->blockid}&pinned={$info->pinned}&view=bill&billid={$bill->id}&transid={$info->transid}";
	
	    	$seller = new StdClass;
		    $seller->firstname = $CFG->block_courseshop_sellername;
		    $seller->lastname = '';
		    $seller->email = $CFG->block_courseshop_sellermail;
		    $seller->maildisplay = 1;
		    
		    $title = $SITE->shortname . ' : ' . get_string('yourorder', 'block_courseshop');
		    $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', $productiondata->private, $notification);
	
		    ticket_notify($bill->user, $seller, $title, $sentnotification, $sentnotification, $customerBillViewUrl);
		}
    }    
}

/*
* perform a transition from state to state for a workflowed object
* When a bill gets pending, it waits for a payement that accomplishes the SOLDOUT state.
* a DELAYED to PENDING should try to recover pre_payment production if performed 
* manually
*/
function bill_transition_DELAYED_PENDING($info){
    global $CFG, $SITE, $USER;
  
    // Scenario : 
    /*
    * the order is being payed offline. 
    * the operator needs to sold out manually the bill and realize all billitems production
    * info : the billid.
    */  
    
    if ($bill = get_record('courseshop_bill', 'id', $info->billid)){

		courseshop_trace("[{$bill->transactionid}] Bill Controller : Delayed Transaction Activating Operations on seller behalf by $USER->shortname");	

        $bill->billItems = get_records('courseshop_billitem', 'billid', $bill->id);
        include $CFG->dirroot.'/blocks/courseshop/datahandling/production.php';
        $customer = get_record('courseshop_customer', 'id', $bill->userid);
        $bill->blockid = $info->blockid;
        $productiondata = produce_prepay($bill);
        
        courseshop_aggregate_production($bill, $productiondata, true);
        
        print_box_start();
        echo $productiondata->salesadmin;
        print_box_end();

		// now notify user the order and all products have been activated
		if (!empty($productiondata->private)){
				
	        // notify end user
	    	// feedback customer with mail confirmation.
	    	$notification  = compile_mail_template('salesFeedback', array('SERVER' => $SITE->shortname,
	                                                                   'SERVER_URL' => $CFG->wwwroot,
	                                                                   'SELLER' => $CFG->block_courseshop_sellername,
	                                                                   'FIRSTNAME' => $customer->firstname,
	                                                                   'LASTNAME' =>  $customer->lastname,
	                                                                   'MAIL' => $customer->email,
	                                                                   'CITY' => $customer->city,
	                                                                   'COUNTRY' => $customer->country,
	                                                                   'ITEMS' => count($bill->billItems),
	                                                                   'PAYMODE' => get_string($bill->paymode, 'block_courseshop'),
	                                                                   'AMOUNT' => $bill->amount), 
	                                                  'block_courseshop');
	    	$customerBillViewUrl = $CFG->wwwroot . "/blocks/courseshop/shop/view.php?id={$info->blockid}&pinned={$info->pinned}&view=bill&billid={$bill->id}&transid={$info->transid}";
	
	    	$seller = new StdClass;
		    $seller->firstname = $CFG->block_courseshop_sellername;
		    $seller->lastname = '';
		    $seller->email = $CFG->block_courseshop_sellermail;
		    $seller->maildisplay = 1;
		    
		    $title = $SITE->shortname . ' : ' . get_string('yourorder', 'block_courseshop');
		    $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', $productiondata->private, $notification);
	
		    ticket_notify($bill->user, $seller, $title, $sentnotification, $sentnotification, $customerBillViewUrl);
		}
    }    
}

function bill_transition_SOLDOUT_COMPLETE($info){
    global $CFG, $SITE, $USER;
  
    // Scenario : 
    /*
    * the order has being payed offline and passed to SOLDOUT, but no automated production
    * pushhed the bill to COMPLETE. Operator marks manually the order COMPLETE after an
    * offline shiping operation has been done. 
    * the operator needs to COMPLETE manually the bill and realize all billitems production
    * info : the billid.
    */  
    
    if ($bill = get_record('courseshop_bill', 'id', $info->billid)){

		// Start marking soldout status. Final may be COMPLETE if production occurs
		set_field('courseshop_bill', 'status', 'COMPLETE', 'id', $info->billid);    
		courseshop_trace("[{$bill->transactionid}] Bill Controller : Transaction Complete Operation on seller behalf by $USER->username");	
	}
}


