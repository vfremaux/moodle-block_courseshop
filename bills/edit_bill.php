<?php

    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/shop/lib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_bill.class.php';
	
	require_js($CFG->wwwroot.'/blocks/courseshop/js/bills.js');

/// get the block reference and key context

	$id = required_param('id', PARAM_INT); // the blockid
	$pinned = optional_param('pinned', 0, PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);

/// Security

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);
    
    $billid = optional_param('billid', 0, PARAM_INT);
    include_once $CFG->dirroot."/blocks/courseshop/classes/Catalog.class.php";
    $theCatalog = new Catalog($theBlock->config->catalogue);
    
    $context = get_context_instance(CONTEXT_BLOCK, $id);
    
    require_capability('block/courseshop:salesadmin', $context);

/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), '', $navigation);

    if($bill = get_record('courseshop_bill', 'id', $billid)){
        $mform = new Bill_Form('edit', $CFG->wwwroot."/blocks/courseshop/bills/edit_bill.php?id=$id&billid=$billid", $id);
    	$mform->set_data($bill);
    } else {
        $mform = new Bill_Form('addbill', $CFG->wwwroot."/blocks/courseshop/bills/edit_bill.php?id=$id", $id);
		$bill->autobill = 0;
    }

    if ($mform->is_cancelled()){
        redirect($CFG->wwwroot."/blocks/courseshop/bills/view.php?id=$id&view=viewAllBills");
    }
	
	if ($bill = $mform->get_data()){
				
        $bill->id = optional_param('billid', '', PARAM_INT);
		
		$now = time();
		
		$transid = strtoupper(substr(mysql_escape_string(base64_encode(crypt(microtime() + rand(0,32)))), 0, 32));
		
		while(record_exists('courseshop_bill', 'transactionid', $transid)){
        	$transid = strtoupper(substr(mysql_escape_string(base64_encode(crypt(microtime() + rand(0,32)))), 0, 40));
		}		
		
		$bill->transactionid = $transid;
		$bill->emissiondate = $now;
        $bill->lastactiondate = $now;
				
		if (empty($bill->currency)){
			$bill->currency = $CFG->block_courseshop_defaultcurrency;
		}
		
		if (!empty($CFG->block_courseshop_useshipping)){
			$shipping = courseshop_calculate_shipping($catalogid, $country, $order);
		} else {
			$shipping->value = 0;
		}
		
		//creating a customer account for an user
		
		if ($bill->useraccountid != 0) {		
			$user = get_record('user', 'id', $bill->useraccountid);
			$customer->firstname = $user->firstname;
			$customer->lastname = $user->lastname;
			$customer->email = $user->email;
			$customer->address1 = $user->address;
			$customer->city = $user->city;
			$customer->zip = '';
			$customer->country = $user->country;
			$customer->hasaccount = $user->id;
		
			if (!$newcustomerid = insert_record('courseshop_customer', $customer)) {
				error("Cannot record a new customer account");
			}
				
			$bill->userid = $newcustomerid;
		}	
        if (empty($bill->id)){
			
            if (!$newid = insert_record('courseshop_bill', $bill)){
                error("could not record bill");
			}        
        } else {
            if (!$newid = update_record('courseshop_bill', $bill)){
                error("could not update bill");
            }
        }
		$billid = $newid;
		
		redirect($CFG->wwwroot."/blocks/courseshop/bills/view.php?view=viewBill&id=$id&billid=$billid");
    } else {
		$mform->display();
    }
?>