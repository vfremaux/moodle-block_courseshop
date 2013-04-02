<?php

// Security
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

if ($cmd == 'recalculate') {
    recalculate($billid);
}
/****************************** Generate bill code ***************************/
if ($cmd == 'generatecode') {
    $bill = new StdClass;
    $bill->transactionid = md5(session_id() . time());
    update_record('courseshop_bill', $bill);
}
/****************************** Delete Single ***************************/
if ($cmd == 'deleteItem') {
	
	$itemid = required_param('billitemid', PARAM_INT);
    $z = required_param('z', PARAM_INT); // ordering
    
	delete_records('courseshop_billitem', 'id', $itemid);

   
    if (!$maxOrder = get_field_select('courseshop_billitem', 'MAX(ordering)', " billid = '$billid' GROUP BY billid ")){
        $maxOrder = 1;
    }

    /* reorder end of list */
    $i = $z;
    if ($upperrecs = get_records_select('courseshop_billitem', " id = $billid AND ordering > $z ", 'ordering', 'id, ordering')){
        foreach($upperrecs as $upperrec){
            update_record('courseshop_billitem', $upperrec);
        }
    }

    /* Delete attachements to this item */
    $sql = "
        SELECT 
           *,
           DATE_FORMAT(emissiondate, '%Y%m%d') as date
        FROM 
           {$CFG->prefix}courseshop_bill
        WHERE
           id = '$billid'
    ";
    /*if($bill = get_record_sql($sql)){
	print_object($bill);
        $itemDataPath = "/bills/" . md5($bill->userid) . "/B-" . $bill->date . "-" . $billid . "/" . $itemid . "/"; //$billitemid = $itemid
        fs_clearDir($itemDataPath, true); // full delete required
        recalculate($billId);
    }*/	
}
/****************************** Delete Items ***************************/
if ($cmd == 'deleteItems') {
    $items = required_param('items', PARAM_INT);
    $itemlist = str_replace(',', "','", $items);

    /* fetches item to reorder (above the smaller deleted ordering) */
    $sql = "
        SELECT
            id, ordering as ordering
        FROM
            {$CFG->prefix}courseshop_billitem 
        WHERE
            billid = '$billId' AND
            ordering >= MIN($items) AND
            ordering NOT IN ($items)
        ORDER BY 
            ordering ASC
    ";
    if($moveditems = get_records_sql($sql)){
        $minOrdering = $moveditems[0]->ordering; // Catch the min
    }
   
    // delete other records
    delete_records_select('courseshop_billitem', " id IN ($itemlist) ");

    // reorder records
    $i = $minOrdering;
    foreach($moveditems as $moveditem) {
        $moveditem->ordering = $i;
        update_record('courseshop_billitem', $moveditem);
        $i++;
   }
   recalculate($bill->id);
}
/****************************** Relocates ***************************/
if ($cmd == 'relocate') {
    // unlocks constraint
    // not safe,find better algorithm
    $sql = "
        ALTER TABLE 
            {$CFG->prefix}courseshop_}billitem
        DROP INDEX 
            unique_ordering
    ";
    execute_sql($sql);

    // relocates
    $relocated = required_param('relocated', PARAM_INT);
    $z = required_param('z', PARAM_INT);
    $where = required_param('at', PARAM_INT);
    if ($z > $where) {
        $gap = $z - $where;
        for ($i = $z - 1 ; $i >= $where ; $i--) {
            moveRecord(1, $i, $billid);
        }
        $query = "
            UPDATE 
                {$CFG->prefix}courseshop_}billitem 
            SET
                ordering = ordering - $gap
            WHERE
                id = $relocated
        ";
        execute_sql($sql);
    } elseif ($z < $where) {
      for ($i = $z + 1 ; $i <= $where ; $i++) {
         moveRecord(-1, $i, $billid);
      }
      $gap = $where - $z;
      $query = "
         UPDATE 
            {$CFG->prefix}courseshop_billitem 
         SET
            ordering = ordering + $gap
         WHERE
            id = '$relocated'
      ";
      mysql_execute($query);
   }
   
    // locks constraints back
    // remove this : cannot support concurrent operations
    $sql = "
        ALTER TABLE 
            {$CFG->prefix}courseshop_billitem
        ADD INDEX 
            unique_ordering ( `billid` , `ordering` )
     ";
      execute_sql($sql);
}
/************************** Unattach attachement *************************/
if ($cmd == 'unattach') {
   $bill = get_record('courseshop_bill', 'id', $billid, '', '', '', '', " id, DATE_FORMAT(emissiondate, '%Y%m%d') as date, userid ");
   $itemDataPath = "/bills/" . md5($bill->userid) . "/B-" . $bill->date . "-" . $billid . "/";
   fs_deleteFile($itemDataPath . required_param('file', PARAM_TEXT));
}
/************************** Unattach attachement *************************/
if ($cmd == 'reclettering') {
	$lettering = required_param('idnumber', PARAM_TEXT);
	if ($checkbill = get_record('courseshop_bill', 'idnumber', $lettering)){
		if ($checkbill->id != $billid){
			$badbillurl = $CFG->wwwroot."/blocks/courseshop/bills/view.php?id=$id&pinned=$pinned&view=viewBill&billid=$checkbill->id";
			$letteringfeedback = '<div class="bill_error">'.get_string('uniqueletteringfailure', 'block_courseshop', $badbillurl).'</div>';
		}
	} else {
		set_field('courseshop_bill', 'idnumber', $lettering, 'id', $billid);
		$letteringfeedback = '<div class="bill_good">'.get_string('letteringupdated', 'block_courseshop').'</div>';
	}
}
/******************************* Work flow *************************/
if ($cmd == 'flowchange'){
    
    // this implements a statefull automaton on bills
    // trigers a state change handler if needed.
    //
    // Typical resolution is a manual SOLDOUT order
    // for realizing production action when payed out.  
    
    $status = required_param('status', PARAM_TEXT);
    $priorstatus = get_field('courseshop_bill', 'status', 'id', $billid);
    $updatedbill->id = $billid;
    $updatedbill->status = $status;
    
    // call a transition handler
    $result = 1;
    if (file_exists($CFG->dirroot.'/blocks/courseshop/transitions.php')){
        include_once $CFG->dirroot.'/blocks/courseshop/transitions.php';
        $transitionhandler = "bill_transition_{$priorstatus}_{$status}";
        if (function_exists($transitionhandler)){
            $info->billid = $billid;
            $info->blockid = $id;
            $info->pinned = $pinned;
            $info->transid = get_field('courseshop_bill', 'transactionid', 'id', $billid);
            $result = $transitionhandler($info);
        }
    }
    
    if ($result) update_record('courseshop_bill', $updatedbill);
}

/************************** ******************** *************************/
/*                               Utilities                               */
/************************** ******************** *************************/

/**
*
*
*/
function moveRecord($dir, $z, $billid) {
   global $CFG;

   $sql = "
      UPDATE 
         {$CFG->prefix}courseshop_billitem 
      SET
         ordering = ordering + $dir
      WHERE
         ordering = '$z' AND
         billid = '$billid'
   ";
   execute_sql($sql);
}


/**
*
*
*
*/
function recalculate($billid) {
    global $CFG;

    $billTotals = array(0,0,0);

    $sql = "
        SELECT
            SUM(totalprice) as untaxedamount,
            SUM(totalprice * (IF(t.ratio IS NOT NULL,t.ratio,0) / 100)) as taxes,
            SUM(totalprice * (1 + (IF(t.ratio IS NOT NULL,t.ratio,0) / 100))) as amount
        FROM
            {$CFG->prefix}courseshop_billitem as bi 
        LEFT JOIN
            {$CFG->prefix}courseshop_tax as t
        ON
            bi.taxcode = t.id
        WHERE
            billid = '$billid'
        GROUP BY 
            billid
    ";

    if ($billTotals = get_record_sql($sql)){
        $billTotals->id = $billid;
        update_record('courseshop_bill', $billTotals);
    }
}

