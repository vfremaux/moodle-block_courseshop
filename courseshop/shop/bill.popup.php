<?php

	include '../../../config.php';
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/shop/lib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/mailtemplatelib.php';

    $id = required_param('id', PARAM_INT);
	$transid = required_param('transid', PARAM_TEXT);
	$billid = required_param('billid', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
    if (!$instance = get_record($blocktable, 'id', $id)){
        error('Invalid block');
    }
    $theBlock = block_instance('courseshop', $instance);
	$context = get_context_instance(CONTEXT_BLOCK, $theBlock->instance->id);

    // get active catalog from block 

	/*
    if (isset($theBlock->config)){
        $catalogid = $theBlock->config->catalogue;
    } else {
        error("This block is not configured");
    }

    $theCatalog->id = $catalogid; // make a small proxy
    */

	if (!$aFullBill = courseshop_get_full_bill($transid, $theBlock)){
		print_error('invalidbillid', 'block_courseshop', $CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=shop&id='.$id.'&pinned='.$pinned);
	}
	
?>
<html>
<head>
<base href="<?php echo $CFG->wwwroot ?>/">
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot.'/blocks/courseshop/print_preview.css' ?>" type="text/css">
</head>
<body style="max-width:780px">
<table cellpadding="4" width="780">
    <tr>
        <td><img src="<?php echo $CFG->wwwroot.'/theme/'.current_theme().'/logo.gif' ?>"></td>
        <td align="right"><a href="#" onclick="window.print();return false;"><?php echo get_string('printbilllink', 'block_courseshop') ?></a></td>
    </tr>
    <tr>
    	<td colspan="2">
    		<?php
		    if (empty($aFullBill->idnumber)){
		        $billtitle = get_string('proforma', 'block_courseshop');
		        print_heading($billtitle, 'center', 1);
		        print_string('ordertempstatusadvice', 'block_courseshop');
		    } else {
		        $billtitle = get_string('bill', 'block_courseshop');
		        print_heading($billtitle, 'center', 1);
		    }
		    ?>
    	</td>
    </tr>
   	<tr>
      	<td width="60%" valign="top">
         	<p><b><?php 
            	if ($aFullBill->idnumber){
                	echo get_string('bill', 'block_courseshop') .': </b>'. $aFullBill->idnumber;
            	} else {
                	echo get_string('proforma', 'block_courseshop') .': </b>'. $aFullBill->ordering;
            	}
         	?>
      	</td>
   	</tr>
</table>

<table cellspacing="4" width="780">
   <tr>
      <td width="60%" valign="top">
     	<span style="font-family:monospace;background-color:#D0D0D0"><?php echo $aFullBill->transactionid ?></span>
      </td>
      <td width="40%" valign="top" align="right">
         <b><?php print_string('on', 'block_courseshop') ?>:</b> <?php echo userdate($aFullBill->emissiondate) ?>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php print_string('customer', 'block_courseshop') ?>: </b> <?php echo $aFullBill->customer->lastname ?> <?php echo $aFullBill->customer->firstname ?>
      </td>
      <td width="40%" valign="top" align="right">
         <b><?php echo $CFG->block_courseshop_sellername ?></b>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
      </td>
      <td width="40%" valign="top" align="right">
         <b><?php echo  $CFG->block_courseshop_selleraddress ?></b>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php print_string('city') ?>: </b>
         <?php echo  $aFullBill->customer->city ?>
      </td>
      <td width="40%" valign="top" align="right">
         <b><?php echo $CFG->block_courseshop_sellerzip ?> <?php echo $CFG->block_courseshop_sellercity ?></b>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php print_string('country') ?>: </b> <?php echo strtoupper($aFullBill->customer->country) ?>
      </td>
      <td width="40%" valign="top" align="right">
         <?php echo $CFG->block_courseshop_sellercountry ?>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php print_string('email') ?>: </b> <?php echo $aFullBill->customer->email ?>
      </td>
      <td width="40%" valign="top" align="right">
        <?php echo $CFG->block_courseshop_sellermail ?>
      </td>
   </tr>
</table>

<table cellspacing="5" class="generaltable" width="780">
   <tr valign="top">
      <th width="40%" class="header c0">
         <?php print_string('designation', 'block_courseshop') ?>
      </th>
      <th width="10%" class="header c1">
         <?php print_string('reference', 'block_courseshop') ?>
      </th>
      <th width="11%" class="header c2">
         <?php print_string('unitprice', 'block_courseshop') ?>
      </th>
      <th width="8%" class="header c3">
         <?php print_string('quantity', 'block_courseshop') ?>
      </th>
      <th width="8%" align="right" class="header c4">
         <?php print_string('taxes', 'block_courseshop') ?>
      </th>
      <th width="8%" align="right" class="header c5">
         &nbsp;&nbsp;<?php print_string('rate', 'block_courseshop') ?>
      </th>
      <th width="15%" align="right" class="header c6">
         <?php print_string('totalpriceTTC', 'block_courseshop') ?>
      </th>
   </tr>
<?php
    foreach($aFullBill->items as $portlet){
        include($CFG->dirroot.'/blocks/courseshop/lib/billCustomerLine.portlet.php');
    }
?>
</table>
<table cellspacing="5" class="generaltable" width="780">
   <tr>
      <td width="40%" valign="middle" rowspan="5">
      			<b><?php print_string('paymentmode', 'block_courseshop') ?>:</b><br/>
               	<span id="modepayspan"><?php print_string($aFullBill->paymode, 'block_courseshop') ?></span>&nbsp;<br>
      </td>
      <td width="40%" valign="top" class="cell c1">
         <?php print_string('subtotal', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" valign="top" class="total">
         <?php echo $aFullBill->unshippedtaxedamount ?>&nbsp;<?php echo get_string($aFullBill->currency.'_symb', 'block_courseshop') ?>&nbsp;
      </td>
   </tr>
<?php
if ($aFullBill->discount != 0){
?>
   <tr valign="top">
      <td width="40%" class="cell c1">
         <?php print_string('discount', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" class="cell c2">
         <?php  echo "<b>-" . ($CFG->block_courseshop_discountrate) . "%</b>" ; ?>&nbsp;&nbsp;
      </td>
   </tr>
<?php
}
if (!empty($CFG->block_courseshop_hasshipping) || $aFullBill->shipping->value > 0){
	if ($aFullBill->discount != 0){
?>
   <tr>
      <td width="40%" valign="top" class="cell c1">
         <?php print_string('totaldiscounted', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" valign="top" class="cell c2">
         <?php echo $aFullBill->discountedtaxedamount ?>&nbsp;<?php echo get_string($aFullBill->currency.'_symb', 'block_courseshop') ?>&nbsp;
      </td>
   </tr>
<?php
	}
?>
   <tr valign="top">
      <td width="40%" class="cell c1">
         <?php print_string('shipping', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" class="cell c2">
         <?php echo $aFullBill->shipping->value ?>&nbsp;<?php echo get_string($aFullBill->currency.'_symb', 'block_courseshop') ?>&nbsp;
      </td>
   </tr>
<?php
}
?>
   <tr>
      <td width="40%" valign="top" class="cell c1 topay">
         <?php print_string('totalpriceTTC', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" valign="top" class="cll c2 topay">
         <?php echo $aFullBill->totaltaxedamount ?>&nbsp;<?php echo get_string($aFullBill->currency.'_symb', 'block_courseshop') ?>&nbsp;
      </td>
   </tr>
</table>
</center>
   <!-- Total sections -->
<p><b><?php print_string('taxessummary', 'block_courseshop') ?>:</b>
<center>
<table width="450" class="generaltable">
<?php
$collectedTax = 0;
if (!empty($aFullBill->taxes)) {
?>
    <tr valign="top">
       <th class="header c0" align="left" width="33%">
          <?php print_string('tax', 'block_courseshop') ?>
       </th>
       <th class="header c1" align="left" width="33%">
          <?php print_string('rate', 'block_courseshop') ?>
       </th>
       <th class="header lastcol"  align="right" width="33%">
          <?php print_string('amount', 'block_courseshop') ?>
       </th>
    </tr>
<?php
   if ($aFullBill->ignoretax) $aFullBill->ignorestyle = " shadow";
   foreach ($aFullBill->taxes as $taxid => $portlet) {
      $portlet->bill = &$aFullBill;
      include($CFG->dirroot.'/blocks/courseshop/lib/customerTaxBillLine.portlet.php');
   }
?>
    <tr valign="top">
        <td>
            &nbsp;
        </td>
        <td align="right">
            <b><?php echo get_string('totaltaxes', 'block_courseshop') ?>: </b>
        </td>
        <td align="right" class="total">
         <?php echo $aFullBill->totaltaxes ?>&nbsp;<?php echo get_string($aFullBill->currency.'_symb', 'block_courseshop') ?>&nbsp;
        </td>
    </tr>
<?php
} else {
?>
<tr class="billrow" height="100" valign="top">
   <td style="padding : 2px ; color : white" colspan="10">
   <?php print_string('emptytaxes', 'block_courseshop') ?>
   </td>
</tr>
<?php
}
?>
</table>

<table width="780">
   <tr valign="top">
      <td class="sectionHeader">
         <b><?php echo get_string('payment', 'block_courseshop') ?></b>
      </td>
   </tr>
    <tr>
        <td>
		<?php
			require_once $CFG->dirroot.'/blocks/courseshop/paymodes/'.$aFullBill->paymode.'/'.$aFullBill->paymode.'.class.php';
			$classname = 'courseshop_paymode_'.$aFullBill->paymode;
			$pm = new $classname($theBlock);
			$pm->print_invoice_info();
		?>
        </td>
    </tr>
</table>

<p><?php echo get_string('forquestionssendmailto', 'block_courseshop') ?>: <a href="mailto:<?php echo $_CFG->block_courseshop_sellermail ?>"><?php echo $CFG->block_courseshop_sellermail ?></a>
