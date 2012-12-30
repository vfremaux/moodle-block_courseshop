<?php

    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    $sortorder = optional_param('order', 'id', PARAM_TEXT);
    $dir = optional_param('dir', 'ASC', PARAM_TEXT);
    $cmd = optional_param('cmd', '', PARAM_TEXT);
    $status = optional_param('status', 'ALL', PARAM_TEXT);
    $cur = optional_param('cur', '', PARAM_TEXT);

    $statusclause = ($status != 'ALL') ? " AND b.status = '$status' " : '' ;
    
    $url = $CFG->wwwroot."/blocks/courseshop/bills/view.php?view=viewAllBills&id=$id";

    if ($cmd != ''){
        include $CFG->dirroot.'/blocks/courseshop/bills/viewAllBills.controller.php';
    }

	$curclause = '';    
    if (!empty($cur)) {
    	$curclause = " AND currency = '$cur' ";
    }

    $sql = "
        SELECT 
           b.*,
           c.firstname,
           c.lastname,
           c.email,
           b.currency,
           DATE_FORMAT(FROM_UNIXTIME(emissiondate), \"%Y%m%d\") as date
        FROM 
           {$CFG->prefix}courseshop_bill as b,
           {$CFG->prefix}courseshop_customer as c
        WHERE
           b.userid = c.id
           $statusclause
           $curclause
        ORDER BY 
           `{$sortorder}` {$dir}
    ";

	block_courseshop_print_currency_choice($cur, $CFG->wwwroot.'/blocks/courseshop/bills/view.php?view=viewAllBills', array('id' => $id, 'pinned' => $pinned, 'dir' => $dir, 'order' => $sortorder, 'status' => $status));
    
    $samecurrency = true;
    if ($bills = get_records_sql($sql)){
    	reset($bills);
    	$firstbill = current($bills);
    	$billcurrency = $firstbill->currency;
	
        foreach ($bills as $bill) {
        	if ($billcurrency != $bill->currency){
        		$samecurrency = false;
        	}
          	$billsbystate[$bill->status][$bill->id] = $bill;		  	  
        }
    } else {
        $billsbystate = array();
    }
    
    print_heading_with_help(get_string('billing', 'block_courseshop'), 'billstates', 'block_courseshop');

/// print tabs

    $total->WORKING = count_records_select('courseshop_bill', " status = 'WORKING' $curclause");
    $total->PENDING = count_records_select('courseshop_bill', " status = 'PENDING' $curclause");
    $total->DELAYED = count_records_select('courseshop_bill', "status = 'DELAYED' $curclause");
    $total->SOLDOUT = count_records_select('courseshop_bill', "status = 'SOLDOUT' $curclause");
    $total->COMPLETE = count_records_select('courseshop_bill', "status = 'COMPLETE' $curclause");
    $total->CANCELLED = count_records_select('courseshop_bill', " status = 'CANCELLED' $curclause");
    $total->FAILED = count_records_select('courseshop_bill', "status = 'FAILED' $curclause");
    $total->PAYBACK = count_records_select('courseshop_bill', "status = 'PAYBACK' $curclause");
    $total->ALL = count_records_select('courseshop_bill', " 1 $curclause ");

    $rows[0][] = new tabobject('WORKING', "$url&status=WORKING&cur=$cur", get_string('bill_WORKINGs', 'block_courseshop').' ('.$total->WORKING.')');
    $rows[0][] = new tabobject('PENDING', "$url&status=PENDING&cur=$cur", get_string('bill_PENDINGs', 'block_courseshop').' ('.$total->PENDING.')');
    $rows[0][] = new tabobject('DELAYED', "$url&status=DELAYED&cur=$cur", get_string('bill_DELAYEDs', 'block_courseshop').' ('.$total->DELAYED.')');
    $rows[0][] = new tabobject('SOLDOUT', "$url&status=SOLDOUT&cur=$cur", get_string('bill_SOLDOUTs', 'block_courseshop').' ('.$total->SOLDOUT.')');
    $rows[0][] = new tabobject('COMPLETE', "$url&status=COMPLETE&cur=$cur", get_string('bill_COMPLETEs', 'block_courseshop').' ('.$total->COMPLETE.')');
    $rows[0][] = new tabobject('CANCELLED', "$url&status=CANCELLED&cur=$cur", get_string('bill_CANCELLEDs', 'block_courseshop').' ('.$total->CANCELLED.')');
    $rows[0][] = new tabobject('FAILED', "$url&status=FAILED&cur=$cur", get_string('bill_FAILEDs', 'block_courseshop').' ('.$total->FAILED.')');
    $rows[0][] = new tabobject('PAYBACK', "$url&status=PAYBACK&cur=$cur", get_string('bill_PAYBACKs', 'block_courseshop').' ('.$total->PAYBACK.')');
    $rows[0][] = new tabobject('ALL', "$url&status=ALL&cur=$cur", get_string('bill_ALLs', 'block_courseshop').' ('.$total->ALL.')');
    
    print_tabs($rows, $status);

/// print bills
    
    $subtotal = 0;

    if (empty($billsbystate)) {
        print_box_start();
        print_string('nobills', 'block_courseshop');
        print_box_end();
    } else {
?>
<table width="100%" class="generaltable">
    <tr>
       <th class="header c0"> 
       </th>
       <th class="header c1">
            <?php print_string('num', 'block_courseshop') ?>
       </th>
       <th class="header c2">
            <?php print_string('label', 'block_courseshop') ?>
       </th>
       <th class="header c3">
            <?php print_string('transaction', 'block_courseshop') ?>
       </th>
       <th class="header lastcol">
            <?php print_string('amount', 'block_courseshop') ?>
       </th>
    </tr>
<?php
       $i = 0;
       foreach (array_keys($billsbystate) as $status) {
?>
    <tr>
        <td colspan="5" class="grouphead">
           <b><?php print_string('bill_' . $status . 's', 'block_courseshop') ?></b>
        </td>
    </tr>
<?php
        $CFG->subtotal = 0;
        foreach ($billsbystate[$status] as $portlet){
			$subtotal += floor($portlet->amount * 100) / 100;
            include ($CFG->dirroot.'/blocks/courseshop/lib/billMerchantLine.portlet.php');
        }
?>
    <tr>
        <td colspan="2" class="groupSubtotal">
        </td>
        <td colspan="3" align="right" class="groupsubtotal">
            <?php 
            if ($samecurrency){
            	echo sprintf('%.2f', round($subtotal, 2));
            	echo ' ';
            	echo get_string($billcurrency.'_symb', 'block_courseshop');
            } else {
            	print_string('nosamecurrency', 'block_courseshop');
            }
            ?>
        </td>
    </tr>
<?php
        $i++;
        }
        echo '</table>';
    }
?>
<table width="100%">
   <tr>
      <td align="left">
      </td>
      <td align="right">
         <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/bills/edit_bill.php?id={$id}" ?>"><?php print_string('newbill', 'block_courseshop') ?></a>
      </td>
   </tr>
</table>
<br />
