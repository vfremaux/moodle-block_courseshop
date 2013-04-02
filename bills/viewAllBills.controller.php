<?php

// Security
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

if ($cmd == 'addbill') {
    
   $bill->id = required_param('billid', PARAM_INT);
   $bill->title = required_param('title', PARAM_TEXT);
   $bill->worktype = required_param('worktype', PARAM_TEXT);
   $bill->userid = optional_param('customer', '', PARAM_INT);
   $bill->currency = required_param('currency', PARAM_TEXT);
   
   	$bill->userid = required_param('userid', PARAM_INT);
   	$bill->title = required_param('title', PARAM_TEXT);
   	$bill->status = 'WORKING';
   	$bill->emissiondate = time();
   	$bill->lastactiondate = time();
   	$bill->assignedto = $USER->id;
   	$bill->timeToDo = 0;
   	$bill->untaxedamount = 0;
   	$bill->taxes = 0;
   	$bill->amount = 0;
   	$bill->convertedamount = 0;

   	if(!$newid = insert_record('courseshop_bill', $bill ))	 {
   		error("Could not insert record");
   	}
   
   redirect($CFG->wwwroot."/blocks/courseshop/bills/view.php?id=$id&view=viewBill&billid=$newid");

} elseif ($cmd == 'updatebill') {

   $bill->id = required_param('billid', PARAM_INT);
   $bill->title = required_param('title', PARAM_TEXT);
   $bill->worktype = required_param('worktype', PARAM_TEXT);
   $bill->delay = optional_param('timetodo', 0, PARAM_NUMBER);
   $bill->status = required_param('status', PARAM_TEXT);
   $bill->cost = optional_param('amount', 0, PARAM_NUMBER);

   update_record('courseshop_bill', $bill);

}elseif ($cmd == 'deletebill') {

   $billid = required_param('billid', PARAM_INT);
   
   if ($bill = get_record('courseshop_bill', 'id', $billid)){

        delete_records('courseshop_bill', 'id', $billid);
        delete_records('courseshop_billitem', 'billid', $billid);
    
       /* Delete all attachements to the bill */
       $itemDataPath = "/shops/{$catalogueid}/bills/".md5($bill->userid.$CFG->passwordsaltmain).'/ORD-'.$bill->receiveddate.'-'.$billid;
       fs_clearDir($itemDataPath);
    }

} elseif ($cmd == 'deleteitems') {

    $items = required_param('items', PARAM_INT);
    $itemlist = str_replace(',', "','", $items);

    $dirIds = array();
    if ($billinfos = get_records_select('courseshop_bill', " id IN ('$itemlist') ", 'id, receiveddate')){
        foreach ($billinfos as $billinfo) {
            $dirIds[] = 'ORD-' . $billinfo->receiveddate . "-" . $billinfo->id; 
        }

        delete_records_select('courseshop_bill', " id IN ('$itemlist') ");
        delete_records_select('courseshop_billitem', " billid IN ('$itemlist') ");

        /* Delete attachements to all these bills */
        foreach($dirIds as $adir) {
            $itemDataPath = "/shops/{$catalogueid}/bills/".md5($bill->userid.$CFG->passwordsaltmain).'/'.$adir;
            fs_clearDir($itemDataPath, true);
        }
    }

} elseif($cmd == 'changestate') {

    $bill->id = required_param('billid', PARAM_INT);
    $bill->status = required_param('status', PARAM_TEXT);
    update_record('courseshop_bill', $bill); 

} elseif($cmd == 'ignoretax') {

    $bill->id = required_param('billid', PARAM_INT);
    $bill->ignoretax = 1; 
    update_record('courseshop_bill', $bill); 

} elseif($cmd == 'restoretax') {

    $bill->id = required_param('billid', PARAM_INT);
    $bill->ignoretax = 0; 
    update_record('courseshop_bill', $bill); 

}