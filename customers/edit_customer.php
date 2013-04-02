<?php

    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_customer.class.php';

/// get the block reference and key context

	$id = required_param('id', PARAM_INT); // the blockid
	$pinned = optional_param('pinned', 0, PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);

/// Security

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);
    
    $customerid = optional_param('customerid', '', PARAM_INT);
    
    $context = get_context_instance(CONTEXT_BLOCK, $id);
    
    require_capability('block/courseshop:salesadmin', $context);

/// make page header and navigation

    $navlinks[] = array('name' => get_string('courseshop', 'block_courseshop'),
    		'link' => $CFG->wwwroot."/blocks/courseshop/index.php?id={$id}&pinned={$pinned}",
    		'type' => 'url');
    $navlinks[] = array('name' => get_string('editcustomer', 'block_courseshop'),
    		'link' => '',
    		'type' => 'title');
    $navigation = build_navigation($navlinks);
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), $navigation);


    if ($customerid){
        $customer = get_record('courseshop_customer', 'id', $customerid);
        $mform = new customer_Form('edit', $CFG->wwwroot."/blocks/courseshop/customers/edit_customer.php?id=$id&customerid=$customerid");
    	$mform->set_data($customer);
    } else {
        $mform = new Customer_Form('add', $CFG->wwwroot."/blocks/courseshop/customers/edit_customer.php?id=$id");
    }

    if ($data = $mform->get_data()){
        $customer->id = optional_param('customerid', '', PARAM_INT);
        $customer->firstname = required_param('firstname', PARAM_TEXT);
        $customer->lastname = required_param('lastname', PARAM_TEXT);
        $customer->address1 = required_param('address1', PARAM_TEXT);
		$customer->address2 = optional_param('address2', PARAM_TEXT);
		$customer->zip = required_param('zip', PARAM_TEXT);
		$customer->city = required_param('city', PARAM_TEXT);
		$customer->email = required_param('email', PARAM_TEXT);
		$customer->country = required_param('country', PARAM_TEXT);
		$customer->organisation = optional_param('organisation', PARAM_TEXT);
		
		if (!record_exists('courseshop_customer', 'email', $customer->email)){
			if (record_exists('user', 'email', $customer->email)){
				$account = get_record('user', 'email', $customer->email);
				$customer->hasaccount = $account->id;
				//$customer->hasaccount = 1;
			} else {
			$customer->hasaccount = 0;
			}
		} else {
		error("Email address already associated to a customer account");
		redirect($CFG->wwwroot."/blocks/courseshop/customers/view.php&view=viewAllCustomers?id=$id");
		}
		        
    
        if (empty($customer->id)){
            $newid = insert_record('courseshop_customer', $customer);
        } else {
            $updateid = update_record('courseshop_customer', $customer);
        }
        redirect($CFG->wwwroot."/blocks/courseshop/customers/view.php?view=viewAllCustomers&id=$id&cmd=$cmd");
    } else {
        $mform->display();
	}

?>