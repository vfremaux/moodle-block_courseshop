<?php

/// get all input parms
include '../../../../config.php';
require_once $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/mercanet.class.php';
require_once $CFG->dirroot.'/blocks/courseshop/shop/lib.php';

/// Setup trace

    debug_trace('Mercanet Autorepsonse (IPN) : Open mecanetbacksession');

/// Keep out casual intruders 

    if (empty($_POST) or !empty($_GET)) {
        error("Sorry, you can not use the script that way.");
    }

// we cannot know yet which block instanceplays as infomation is in the mercanet
// cryptic answer. Process_ipn() decodes cryptic answer and get this context information to 
// go further.

	$blockinstance = null;
	$payhandler = new courseshop_paymode_mercanet($blockinstance);
	$payhandler->process_ipn();

?>
