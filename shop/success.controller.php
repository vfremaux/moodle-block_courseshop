<?php

	require_once $CFG->dirroot.'/auth/ticket/lib.php';
	include_once($CFG->dirroot.'/blocks/courseshop/datahandling/production.php');

/// Security
	
	if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");
	
	global $SESSION;
	
	$aFullBill->blockid = $instanceid;
	$aFullBill->pinned = $pinned;
	
	$updatedbill->id = $aFullBill->id;
	
	$bounceModePay = '';

	if ($cmd == 'confirm') {
	    // a more direct resolution when paiement is not performed online
	    // we can perform pre_pay operations
	    
		courseshop_trace("[{$aFullBill->transactionid}] ".'Order confirm (offline payments)');
	    $bounceModePay = 'offline';
	    $aFullBill->status = $updatedbill->status = 'PENDING';
	    update_record('courseshop_bill', $updatedbill);
	    $interactive = true;
	
		courseshop_trace("[{$aFullBill->transactionid}] ".'Production starting ...');
		courseshop_trace("[{$aFullBill->transactionid}] ".'Success Controller : Pre Pay process');	
	    $productiondata = produce_prepay($aFullBill);    
	
	    // log new production data into bill record
	    // the first producing procedure stores production data.
	    // if interactive shopback process comes later, we just have production
	    // data to display to user.
	    courseshop_aggregate_production($aFullBill, $productiondata);
	    
	    // all has been finished
	    unset($SESSION->shoppingcart);
	} else {

		/// start production
		courseshop_trace("[{$aFullBill->transactionid}] ".'Production starting ...');
	
		if ($aFullBill->status == 'PENDING' || $aFullBill->status == 'SOLDOUT'){
			
			// when using the controller to finish a started production, do not
			// preproduce again (paypal IPN finalization)	
			courseshop_trace("[{$aFullBill->transactionid}] ".'Success Controller : Pre Pay process');	
		    $productiondata = produce_prepay($aFullBill);
		
		    add_to_log(0, 'block_courseshop', 'success', "blocks/courseshop/bills/view.php?id={$instanceid}&pinned={$pinned}&view=viewBill&billid={$aFullBill->id}", $aFullBill->customer->email, $theBlock->instance->id);
		
		    if ($aFullBill->status == 'SOLDOUT'){
				courseshop_trace("[{$aFullBill->transactionid}] ".'Success Controller : Post Pay process');	
		        if($productiondata2 = produce_postpay($aFullBill)){
		            $productiondata->public .= '<br/>'.$productiondata2->public;
		            $productiondata->private .= '<br/>'.$productiondata2->private;
		            $productiondata->salesadmin .= '<br/>'.$productiondata2->salesadmin;
		            
					$aFullBill->status = $updatedbill->status = 'COMPLETE';
					update_record('courseshop_bill', $updatedbill);
		        }
		    }
		    // log new production data into bill record
		    // the first producing procedure stores production data.
		    // if interactive shopback process comes later, we just have production
		    // data to display to user.
		    courseshop_aggregate_production($aFullBill, $productiondata);
		}
	}   
	
/// Send final notification by mail if something has been done the end user should know.
	    
	courseshop_trace("[{$aFullBill->transactionid}] ".'Success Controller : Transaction Complete Operations');
	
	// notify end user
	// feedback customer with mail confirmation.
	global $SITE;
	$notification  = compile_mail_template('salesFeedback', array('SERVER' => $SITE->shortname,
	                                                           'SERVER_URL' => $CFG->wwwroot,
	                                                           'SELLER' => $CFG->block_courseshop_sellername,
	                                                           'FIRSTNAME' => $aFullBill->customer->firstname,
	                                                           'LASTNAME' =>  $aFullBill->customer->lastname,
	                                                           'MAIL' => $aFullBill->customer->email,
	                                                           'CITY' => $aFullBill->customer->city,
	                                                           'COUNTRY' => $aFullBill->customer->country,
	                                                           'ITEMS' => $aFullBill->itemcount,
	                                                           'PAYMODE' => get_string($aFullBill->paymode, 'block_courseshop'),
	                                                           'AMOUNT' => sprintf("%.2f", round($aFullBill->amount, 2))), 
	                                         'block_courseshop');
	$customerBillViewUrl = $CFG->wwwroot . "/blocks/courseshop/shop/view.php?id={$instanceid}&pinned={$pinned}&view=bill&billid={$aFullBill->id}&transid={$transid}";
	
	$seller = new StdClass;
	$seller->firstname = $CFG->block_courseshop_sellername;
	$seller->lastname = '';
	$seller->email = $CFG->block_courseshop_sellermail;
	$seller->maildisplay = true;
	
	$title = $SITE->shortname . ' : ' . get_string('yourorder', 'block_courseshop');
	if (!empty($productiondata->private)){
	    $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', $productiondata->private, $notification);
	} else {
	    $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', '', $notification);
	}
	
	if (empty($aFullBill->user)){
		$aFullBill->user->email = $aFullBill->customer->email;
		$aFullBill->user->username = '';
		$aFullBill->user->mailformat = FORMAT_HTML;
	}
	
	ticket_notify($aFullBill->user, $seller, $title, $sentnotification, $sentnotification, $customerBillViewUrl);
	courseshop_trace("[{$aFullBill->transactionid}] ".'Success Controller : Transaction notified to customer');
	
	/* notify sales forces and administrator */
	
	/// Send final notification by mail if something has been done the sales administrators users should know.
	
	$salesnotification = compile_mail_template('transactionConfirm', array('TRANSACTION' => $aFullBill->transactionid,
	                                                           'SERVER' => $SITE->fullname,
	                                                           'SERVER_URL' => $CFG->wwwroot,
	                                                           'SELLER' => $CFG->block_courseshop_sellername,
	                                                           'FIRSTNAME' => $aFullBill->customer->firstname,
	                                                           'LASTNAME' => $aFullBill->customer->lastname,
	                                                           'MAIL' => $aFullBill->customer->email,
	                                                           'CITY' => $aFullBill->customer->city,
	                                                           'COUNTRY' => $aFullBill->customer->country,
	                                                           'PAYMODE' => $aFullBill->paymode,
	                                                           'ITEMS' => $aFullBill->itemcount,
	                                                           'AMOUNT' => sprintf("%.2f", round($aFullBill->untaxedamount, 2)),
	                                                           'TAXES' => sprintf("%.2f", round($aFullBill->taxes, 2)),
	                                                           'TTC' => sprintf("%.2f", round($aFullBill->amount, 2))
	                                                            ), 'block_courseshop');
	$administratorViewUrl = $CFG->wwwroot . "/blocks/courseshop/bills/view.php?id={$instanceid}&pinned={$pinned}&view=viewBill&billid={$aFullBill->id}";
	
	if($salesrole = get_record('role', 'shortname', 'sales')){
	
		$seller = new StdClass;
	    $seller->firstname = $CFG->block_courseshop_sellername;
	    $seller->lastname = '';
	    $seller->email = $CFG->block_courseshop_sellermail;
	    $seller->maildisplay = true;
	    
	    $title = $SITE->shortname . ' : ' . get_string('orderinput', 'block_courseshop');
		if (!empty($productiondata->private)){
	    	$sentnotification = str_replace('<%%PRODUCTION_DATA%%>', $productiondata->salesadmin, $salesnotification);
	    } else {
	    	$sentnotification = str_replace('<%%PRODUCTION_DATA%%>', '', $salesnotification);
	    }
		
		ticket_notifyrole($salesrole->id, get_context_instance(CONTEXT_BLOCK, $instanceid), $seller, $title, $sentnotification, $sentnotification, $administratorViewUrl);
		courseshop_trace("[{$aFullBill->transactionid}] Courseshop Transaction Confirm Notification to sales");
	} else {
		courseshop_trace("[{$aFullBill->transactionid}] ".'Success Controller : No sales role defined');
	}
	
	unset($SESSION->shoppingcart);

?>