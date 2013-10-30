<?php

    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    include_once $CFG->dirroot."/blocks/courseshop/classes/Catalog.class.php";
	require_js($CFG->wwwroot.'/blocks/courseshop/shop/js/order.js');

    $theCatalog = new Catalog($catalogid);

    $cmd = required_param('cmd', PARAM_TEXT);
    if ($cmd){
        $result = include_once $CFG->dirroot.'/blocks/courseshop/shop/order.controller.php';
    }
    
/// get paymode instance    

	require_once $CFG->dirroot.'/blocks/courseshop/paymodes/'.$paymode.'/'.$paymode.'.class.php';
	$classname = 'courseshop_paymode_'.$paymode;
	$pm = new $classname($theBlock);    

/// Start ptinting page 

	print_box_start('', 'orderpanel');
	courseshop_print_progress('CONFIRM');
	courseshop_print_customer_info($aFullBill);

/// check zero euros order (free products)

	if ($aFullBill->amount == 0){

		// We set the bill as prepaied, so complete production cann be done at succes step. 
		set_field('courseshop_bill', 'status', 'SOLDOUT', 'id', $aFullBill->id);
		set_field('courseshop_bill', 'paymode', 'freeorder', 'id', $aFullBill->id);
		$aFullBill->status = 'SOLDOUT';
		$aFullBill->paymode = 'freeorder';
		courseshop_print_free_order_form($aFullBill, $theBlock->instance->id, $pinned);
		print_box_end();
		print_footer();
		exit;
	} 
    

?>

<p>
<center>
<?php

if ($transactionFail == 'bounce'){
?>
	<div class="error">
		<?php print_string('transactionbounce', 'block_courseshop') ?>
	</div>
<?php
}
if ($cmd == 'confirm'){
	print_container_start();
	$pm->print_complete();
	print_container_end();
}

/// Print main ordering table

?>
<div id="order">
<form name="bill" action="<?php echo $CFG->wwwroot.'/blocks/courseshop/shop/order.popup.php' ?>" target="_blank">
<table cellspacing="5" class="generaltable" width="100%">
   <tr valign="top">
      <th width="41%" align="left" class="header c0">
         <?php print_string('designation', 'block_courseshop') ?>
      </th>
      <td width="22%" align="left" class="header c1">
         <?php print_string('reference', 'block_courseshop') ?>
      </td>
      <td width="14%" align="left" class="header c2">
         <?php print_string('unitprice', 'block_courseshop') ?>
      </td>
      <td width="9%" class="header c3">
         <?php print_string('quantity', 'block_courseshop') ?>
      </td>
      <td width="13%" align="right" class="header lastcol">
         <?php print_string('totalpriceTTC', 'block_courseshop') ?>
      </td>
   </tr>
<?php

$hasrequireddata = array();
foreach($aFullBill->items as $portlet){
    include $CFG->dirroot.'/blocks/courseshop/lib/orderLine.portlet.php';	
}

?>
</table>
<?php
if (!empty($hasrequireddata)){
	$required = implode("','", $hasrequireddata);
	echo '<script type="text/javascript">';
	echo "requiredorderfieldlist = ['$required'];";
	echo '</script>';
}
?>
</div>

<table cellspacing="5" class="generaltable" width="100%">
   <tr>
      <td width="40%" valign="top" rowspan="5" class="cell c0">
      &nbsp;
      </td>
      <td width="40%" valign="top" class="cell c1">
         <?php print_string('subtotal', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" valign="top" class="cell lastcol">
         <?php echo $aFullBill->unshippedtaxedamount ?>&nbsp;<?php echo courseshop_currency($theBlock, 'symbol') ?>&nbsp;
      </td>
   </tr>
<?php
	if ($aFullBill->discount != 0){
?>
   <tr>
      <td width="40%" valign="top" class="courseshop-totaltitle ratio">
         <?php print_string('discount', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" valign="top" class="courseshop-totals ratio">
         <?php echo "<b>-" . ($CFG->block_courseshop_discountrate) . "%</b>" ; ?>
      </td>
   </tr>
<?php 
}
if (!empty($CFG->block_courseshop_hasshipping) || $applyshipping){
	if ($aFullBill->discount != 0){
?>
   	<tr>
      	<td width="40%" valign="top" class="courseshop-totaltitle">
         	<?php print_string('totaldiscounted', 'block_courseshop') ?>:
      	</td>
      	<td width="20%" align="right" valign="top" class="courseshop-totals">
         	<?php echo $aFullBill->discountedtaxedamount ?>&nbsp;<?php echo courseshop_currency($theBlock, 'symbol') ?>&nbsp;
      	</td>
   	</tr>
<?php
	}
?>
   <tr>
      <td width="40%" valign="top" class="courseshop-totaltitle">
         <?php print_string('shipping', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" valign="top" class="courseshop-totals">
         <?php echo $aFullBill->shipping->taxedvalue ?>&nbsp;<?php echo courseshop_currency($theBlock, 'symbol') ?>&nbsp;
      </td>
   </tr>
<?php
}
?>
   <tr>
      <td width="40%" valign="top" class="courseshop-totaltitle topay">
         <b><?php print_string('totalprice', 'block_courseshop') ?></b>:
      </td>
      <td width="20%" align="right" valign="top" class="courseshop-total topay">
         <b><?php echo $aFullBill->totaltaxedamount ?>&nbsp;<?php echo courseshop_currency($theBlock, 'symbol') ?></b>&nbsp;
      </td>
   </tr>
</table>
</center>
	<input type="hidden" name="view" value="" />
	<input type="hidden" name="cmd" value="" />
	<input type="hidden" name="country" value="<?php echo $country ?>" />
	<input type="hidden" name="transid" value="<?php p($transid) ?>">
	<input type="hidden" name="billid" value="<?php p($bill->id) ?>">
	<input type="hidden" name="invoice" value="<?php echo $transid ?>" />
	<input type="hidden" name="pinned" value="<?php p($pinned) ?>">
	<input type="hidden" name="id" value="<?php p($id) ?>">
</form>

<!-- include paymode inserts !!! -->

<?php 
	print_heading(get_string('procedure', 'block_courseshop')); 
	$member = 'enable'.$paymode;
	echo '<p><span id="modepayspan">';
	print_string($member.'2', 'block_courseshop'); 
	echo '</span>.&nbsp;<br />';
	
	$pm->print_payment_portlet($aFullBill);

	// noconfirm can be setup by the payment mode plugin, specially if payment mode is online.
	if ($pm->needslocalconfirm()){
		courseshop_print_local_confirmation_form($hasrequireddata);
	}
?>

<!-- include customer question support -->

<p><?php print_string('forquestionssendmailto', 'block_courseshop') ?>: <a href="mailto:<?php echo $CFG->block_courseshop_sellermail ?>"><?php echo  $CFG->block_courseshop_sellermail ?></a>
</center>

<?php

print_box_end();

echo courseshop_check_and_print_eula_conditions($products, $shopproducts);


?>