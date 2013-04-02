<?php

//Get return parms and redirect to shop

// Return_Context : id={$this->shopblock->instance->id}&pinned={$this->shopblock->pinned}

include '../../../../config.php';
require_once $CFG->dirroot.'/blocks/courseshop/paymodes/paypal/paypal.class.php';
require_once $CFG->dirroot.'/blocks/courseshop/shop/lib.php';

// we cannot know yet which block instanceplays as infomation is in the mercanet
// cryptic answer. Cancel() decodes crytpic answer and get this context information to 
// go further.
$blockinstance = null;
$payhandler = new courseshop_paymode_paypal($blockinstance);
$payhandler->cancel();

?>