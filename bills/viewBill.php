<?php

    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");
    
    require_js($CFG->wwwroot.'/blocks/courseshop/js/bills.js');

    $billitems = array();
    $globalattachements = array();

    $relocated = optional_param('relocated', '', PARAM_TEXT);
    $z = optional_param('z', '', PARAM_TEXT);
    $billid = optional_param('billid', '', PARAM_TEXT);
    
    $url = $CFG->wwwroot."/blocks/courseshop/bills/view.php?id={$id}&pinned={$pinned}&view=viewBill&billid={$billid}";

    /* perform local commands on orderitems */

    $cmd = optional_param('cmd', '', PARAM_TEXT);
	
    if ( $cmd != "" ) {
        /* "_" prefixed commands are routed to upper level controller */
        if (preg_match("/^_(.*)$/", $cmd, $matches)) {
            $cmd = $matches[1];
			include $CFG->dirroot. '/blocks/courseshop/bills/viewAllBills.controller.php';
        } else {
            include $CFG->dirroot. '/blocks/courseshop/bills/viewBill.controller.php';
        }
    }
	
    $sql = "
        SELECT 
           b.*,
           c.firstname,
           c.lastname,
           c.email,
           c.city,
           c.address1,
           c.address2,
           c.zip,
           c.country,
           c.hasaccount
        FROM 
           {$CFG->prefix}courseshop_bill as b,
           {$CFG->prefix}courseshop_customer as c
        WHERE
           b.id = '$billid' AND
           c.id = b.userid
    ";
	
	if ($bill = get_record_sql($sql)){
		$billitems = get_records('courseshop_billitem', 'billid', $billid, 'ordering');
	 	  
       /* calculate TAX amounts */

        $sql = "
            SELECT 
                bi.taxcode as code, 
                ROUND(SUM(totalprice) * t.ratio / 100, 2) as amount,
                t.title as title,
                t.ratio as ratio
            FROM 
                {$CFG->prefix}courseshop_billitem as bi,
                {$CFG->prefix}courseshop_tax as t
            WHERE
                billid = '$billid' AND
                bi.taxcode = t.id
            GROUP BY 
                taxcode
        ";
        $taxlines = get_records_sql($sql);
   
        /* get global attachement list (interrogating file system) */   
        $globalDataPath = "/bills/".md5($bill->userid)."/ORD-".$bill->emissiondate."-".$bill->id."/";
        // $globalAttachements = fs_getFileList($globalDataPath);
    } else {
        error('Bad bill ID');
    }

	print_box_start('', 'billpanel');
?>
<form name="selection" action="<?php echo $url ?>" method="get">
<input type="hidden" name="cmd" value="" />
<input type="hidden" name="items" value="" />
</form>

<table class="generaltable" width="100%">
<tr>
   <td valign="top" style="padding : 2px" colspan="5" class="billListTitle">
      <h1><?php 
      		if ($bill->status == 'PENDING' || $bill->status == 'DELAYED'){
      			print_string('order', 'block_courseshop'); 
      		} else {
      			print_string('bill', 'block_courseshop'); 
      		}
      		?> <span class="titleData">B-<?php echo $bill->emissiondate; ?>-<?php echo $bill->id; ?></span></h1><br/>
      <?php echo userdate($bill->emissiondate) ?>
   </td>
   <td colspan="3">
       <b><?php print_string('transactionid', 'block_courseshop') ?>: </b><br />
       <div id="transactionid"><?php echo $bill->transactionid ?></div><br />
<?php
if ($bill->onlinetransactionid != ''){
?>
       <b><?php print_string('paimentcode', 'block_courseshop') ?></b><br />
       <div id="transactionid"><?php echo  $bill->onlinetransactionid ?></div>
<?php
}
if ($bill->transactionid == ''){
?>
        <?php print_string('nocodegenerated', 'block_courseshop') ?><br/>
        <a href="<?php echo $url."&cmd=generatecode" ?>"><?php print_string('generateacode', 'block_courseshop') ?></a>
<?php
}
?>
   	</td>
   	<td colspan="2" valign="top">
   		<b><?php print_string('lettering', 'block_courseshop') ?></b>
   		<?php helpbutton('lettering', get_string('lettering', 'block_courseshop'), 'block_courseshop'); ?>
   		<br/>
   		<?php 
		if ($bill->status == 'PENDING' || $bill->status == 'DELAYED'){
			print_string('noletteringaspending', 'block_courseshop');
			echo '<br/>';
		} else {
	   		if (!empty($letteringfeedback)){
	   			echo $letteringfeedback;
	   		}
   		?>
   		<form name="billletteringform" action="" method="post" >
   		<input type="hidden" name="view" value="viewBill" />
   		<input type="hidden" name="id" value="<?php p($id) ?>" />
   		<input type="hidden" name="pinned" value="<?php p($pinned) ?>" />
   		<input type="hidden" name="billid" value="<?php p($billid) ?>" />
   		<input type="hidden" name="cmd" value="reclettering" />
   		<input type="text" name="idnumber" value="<?php echo $bill->idnumber ?>" />
   		<input type="submit" name="go_lettering" value="<?php print_string('updatelettering', 'block_courseshop') ?>" />
   		</form><br/>
   		<?php
	   	}
	   	?>
   		<b><?php print_string('paymodes', 'block_courseshop') ?>: </b><?php print_string($bill->paymode, 'block_courseshop') ?>
	</td>
</tr>
<tr>
   <td valign="top" style="padding : 2px" colspan="10" class="billTitle">
      <h1><?php print_string('title', 'block_courseshop') ?> : <span class="titleData"><?php echo $bill->title ?></span></h1>
   </td>
</tr>
</table>
<?php  

$portlet = get_record('courseshop_customer', 'id', $bill->userid);
include ($CFG->dirroot.'/blocks/courseshop/lib/customerBox.portlet.php');

print_heading(get_string('order', 'block_courseshop'), 'center', 2); 

?>
<table class="generaltable" width="100%">
<?php
if (count($billitems) == 0) {
?>
<tr class="billrow" height="100">
   <td valign="top" style="padding : 2px" class="billItemMessage" colspan="10">
   <?php print_string('emptybill', 'block_courseshop') ?>
   </td>
</tr>
<?php
} else {
?>
<tr valign="top">
   <!--<th class="header c0">
      &nbsp;
   </th>-->
   <th style="text-align:left" class="header c0">
      <?php print_string('order', 'block_courseshop') ?>
   </th>
   <th style="text-align:left" class="header c1">
      <?php print_string('code', 'block_courseshop') ?>
   </th>
   <th style="text-align:left" class="header c2">
      <?php print_string('product', 'block_courseshop') ?>
   </th>
   <th style="text-align:left" class="header c3">
      <?php print_string('deadline', 'block_courseshop') ?>
   </th>
   <th style="text-align:left" class="header c4">
      <?php print_string('unittex', 'block_courseshop') ?>
   </th>
   <th style="text-align:left" class="header c5">
      <?php print_string('quant', 'block_courseshop') ?>
   </th>
   <th style="text-align:left" class="header c6">
      <?php print_string('totaltex', 'block_courseshop') ?>
   </th>
   <th class="header c7">
      <?php print_string('taxcode', 'block_courseshop') ?>
   </th>
   <th class="header lastcol">
		<?php print_string('orders', 'block_courseshop') ?>
   </th>
</tr>

<?php

    if ($billitems){
		
		
		foreach ($billitems as $portlet) {
		
		/*$portlet->totalprice = $portlet->unitcost * $portlet->quantity;
		
		$bill->untaxedamount = $bill->untaxedamount + $portlet->totalprice;
		
			if ($bill->ignoretax == 0) {
			$tax = get_record('courseshop_tax', 'id', $portlet->taxcode);
			$bill->taxes = $bill->taxes + (($portlet->totalprice*$tax->ratio)/100);
			} */
		
            if (($cmd == 'relocating') && ($portlet->ordering <= $z)) {
?>
<tr class="billRow">
   <td>
      <a href="<?php echo $url."&cmd=relocate&relocated={$relocated}&z={$z}&at={$portlet->ordering}" ?>"><img src="<?php $CFG->wwwroot.'/blocks/courseshop/pix/relocateBox.gif' ?>" style="border : 1px dashed #B0B0B0"></a>
   </td>
</tr>
<?php
          }
          if (($cmd != 'relocating') || ($portlet->id != $relocated)) {
            include $CFG->dirroot.'/blocks/courseshop/lib/billItemLine.portlet.php';
          }
          if (($cmd == 'relocating') && ($portlet->ordering > $z)) {
?>
<tr class="billRow">
   <td>
      <a href="<?php echo $url."&cmd=relocate&relocated={$relocated}&z={$z}&at={$portlet->ordering}" ?>"><img src="images/icons/relocateBox.gif" style="border : 1px dashed #B0B0B0"></a>
   </td>
</tr>
<?php
          	}
       	}
	  // $bill->amount = $bill->amount + ($bill->untaxedamount + $bill->taxes);
	   if (empty($bill->currency)) $bill->currency = 'EUR'; // go to default
	   if (!$newid = update_record('courseshop_bill', $bill)){
            error("could not update bill");
        }
    }
}

?>
</table>

<?php

/// total section

    print_heading(get_string('taxes', 'block_courseshop'), 'center', 2);
    
    $collectedTax = 0;
    if (!empty($taxlines)) {

?>
<table class="generaltable" width="100%">
    <tr valign="top">
       <th style="text-align:left" class="header c0">
          <?php print_string('code', 'block_courseshop') ?>
       </th>
       <th style="text-align:left" class="header c1">
          <?php print_string('tax', 'block_courseshop') ?>
       </th>
       <th style="text-align:left" class="header c2">
          <?php print_string('rate', 'block_courseshop') ?>
       </th>
       <th style="text-align:left" class="header c3" >
          <?php print_string('amount', 'block_courseshop') ?>
       </th>
       <th style="text-align:left" class="header lastcol">
          <?php // print_string('orders', 'block_courseshop') ?>
       </th>
    </tr>

<?php
        if ($bill->ignoretax == 1) $bill->ignoreStyle = " shadow";
        foreach ($taxlines as $portlet) {
            if ($portlet->code == '') continue;
            $portlet->bill = &$bill;
            include $CFG->dirroot.'/blocks/courseshop/lib/taxBillLine.portlet.php';
            $collectedTax += $portlet->amount;
        }
        echo '</table>';
    } else {
        print_string('emptyTaxes', 'block_courseshop');
    }


    print_heading(get_string('billtotal', 'block_courseshop'), 'center', 2);

?>

<table width="100%" class="generaltable">
   <tr class="totals">
      <td valign="top" style="padding : 2px" colspan="9" class="totals attribute">
         <?php print_string('totalti', 'block_courseshop'); ?>				
      </td>
      <td valign="top" style="padding : 2px" class="" align="right">
         <?php echo sprintf('%.2f', round($bill->untaxedamount, 2)) ?> <?php echo get_string($bill->currency.'_symb', 'block_courseshop') ?>
      </td>
   </tr>
   <tr class="taxes">
      <td valign="top" style="padding : 2px" colspan="9" class="totals attribute">
         <?php print_string('billtaxes', 'block_courseshop') ?>
      </td>
      <td valign="top" style="padding : 2px" class="" align="right">
         <?php echo sprintf('%.2f', round($bill->taxes, 2)) ?>
      </td>
   </tr>
   <tr class="totals">
      <th valign="top" colspan="9" class="header">
         <?php print_string('totalTTC', 'block_courseshop') ?>
      </th>
      <th valign="top" class="header" align="right">
         <?php echo sprintf('%.2f', round($bill->amount, 2)) ?> <?php echo get_string($bill->currency.'_symb', 'block_courseshop') ?>
      </th>
   </tr>
</table>

<table width="99%" class="flowcontrol">
   <tr>
     <td colspan="10">
<!-- Flow control section -->
<?php

unset($portlet);
$portlet->status = $bill->status;
$portlet->url = $url;
include $CFG->dirroot.'/blocks/courseshop/lib/flowControl.portlet.php';

?>
        </td>
    </tr>
<!-- Attachement section -->
    <!-- tr>
       <td valign="top" style="padding : 2px ; padding-top : 20px" colspan="9">
          <h2><?php print_string('attachements', 'block_courseshop') ?> + <a href="<?php echo $CFG->wwwroot.'/blocks/courseshop/bills/attachTo.php?type=bill' ?>"><img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/attach.gif' ?>" alt="<?php print_string('attach', 'block_courseshop') ?>" border="0" /></a></h2>
       </td>
    </tr -->
<?php
if (count($globalattachements) == 0) {
?>
<!-- tr class="billRow">
   <td valign="top" style="padding : 2px" class="billItemMessage" colspan="7">
      <?php print_string('nobillattachements', 'block_courseshop') ?>
   </td>
</tr -->
<?php
} else {
    foreach($globalattachements as $aFile){
?>
<tr class="billRow">
   <td valign="top" style="padding : 2px" class="billItemMessage" colspan="7">
<?php
      unset($portlet);
      $portlet->attachement = $aFile;
      $portlet->attachementType = 'bill';
      include $CFG->dirroot.'/blocks/courseshop/lib/attachement.portlet.php';
?>
   </td>
</tr>
<?php
    }
}
?>
</table>
<table width="100%">
    <tr>
        <td align="right">
        	<?php 
        	if (empty($bill->idnumber)){
    		?>
            <a class="pageLinks" href="<?php echo $CFG->wwwroot."/blocks/courseshop/bills/edit_billitem.php?id={$id}&pinned={$pinned}&billid=$billid"; ?>"><?php print_string('newbillitem', 'block_courseshop') ?></a> -
            <?php
	        }
	        ?>
            <a class="pageLinks" href="<?php echo $url."&cmd=recalculate"; ?>"><?php print_string('recalculate', 'block_courseshop') ?></a>
        </td>
    </tr>
</table>
<br />
<br />

<?php
	print_box_end();
?>
