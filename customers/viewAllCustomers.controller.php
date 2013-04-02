<?php

// Security
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

//Delete customer
if ($cmd == 'deletecustomer'){
	
    $customerid = required_param('customerid', PARAM_INT);
	$customeridlist = $customerid;
	delete_records_select('courseshop_customer', " id IN ('$customeridlist') ");
}
// ********** adding manually a customer record ***********/
if ($cmd == 'addcustomer'){
   $customer->firstname = required_param('firstname', PARAM_TEXT);
   $customer->lastname = required_param('lastname', PARAM_TEXT);
   $customer->address1 = required_param('address1', PARAM_TEXT);
   $customer->address2 = required_param('address2', PARAM_TEXT);
   $customer->email = required_param('email', PARAM_TEXT);
   $customer->zip = required_param('zip', PARAM_TEXT);
   $customer->city = required_param('city', PARAM_TEXT);
   $customer->country = optional_param('country', 'FR', PARAM_ALPHA);

	if(!$newid = insert_record('courseshop_customer', $customer ))	 {
		error("Could not insert record");
	}
}

?>