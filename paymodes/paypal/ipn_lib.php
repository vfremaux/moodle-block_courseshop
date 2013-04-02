<?php

if (!defined('MOODLE_INTERNAL')) die ("You cannot use this script this way");

include_once $CFG->dirroot.'/blocks/courseshop/mailtemplatelib.php';

/**
* A lib to provide stuff to simulate IPN from the shop itself
*
*/

function paypal_print_test_ipn_link($aFullBillid, $transid, $id, $pinned){
	global $CFG;

	$sellerexpectedname = (empty($CFG->block_courseshop_test)) ? $CFG->block_courseshop_paypalsellername : $CFG->block_courseshop_paypalsellertestname ;

	$txnid = substr($transid, 0,10);
	
	$url = $CFG->wwwroot.'/blocks/courseshop/paymodes/paypal/paypal_ipn.php';

	$custom = $id.'-'.$pinned;

	$testipnstr = get_string('ipnfortest', 'block_courseshop', null, $CFG->dirroot.'/blocks/courseshop/paymodes/paypal/lang/');
	echo "<form action=\"{$url}\" name=\"ipnsimulate\" method=\"POST\" >";
	echo "<input type=\"hidden\" name=\"invoice\" value=\"{$transid}\" />";
	echo "<input type=\"hidden\" name=\"custom\" value=\"{$custom}\" />";
	echo "<input type=\"hidden\" name=\"txn_id\" value=\"{$txnid}\" />";
	echo "<input type=\"hidden\" name=\"business\" value=\"{$sellerexpectedname}\" />";
	echo "<input type=\"hidden\" name=\"payment_status\" value=\"Completed\" />";

	// catch all post values that came back from Paypal
    foreach ($_POST as $key => $value) {
        $value = stripslashes($value);
		echo "<input type=\"hidden\" name=\"$key\" value=\"$value\" />";
    }

	echo "<input type=\"submit\" value=\"$testipnstr\" />"; 
	echo "</form>";
}

/**
* sends admin a notification
*
*/
function courseshop_email_paypal_error_to_admin($subject, $data) {

    if($salesrole = get_record('role', 'shortname', 'sales')){
        $salesadmins = get_users_from_role_on_context($salesrole, get_context_instance(CONTEXT_SYSTEM));
    }
    if (empty($salesadmins)){
        $salesadmins[] = get_admin();
    }

    $site = get_site();

    $message = "$site->fullname: Paypal IPN : Transaction failed.\n\n$subject\n\n";

    foreach ($data as $key => $value) {
        $message .= "$key => $value\n";
    }

	if (!empty($salesadmins)){
	    foreach($salesadmins as $salesadmin){
	        email_to_user($salesadmin, $salesadmin, "Paypal IPN Error : ".$subject, $message);
	    }
	}
}

?>