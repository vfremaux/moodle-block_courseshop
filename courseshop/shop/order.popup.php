<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/shop/lib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/mailtemplatelib.php';
    
/// get block information

    $id = required_param('id', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
    if (!$instance = get_record($blocktable, 'id', $id)){
        error('Invalid block');
    }
    $theBlock = block_instance('courseshop', $instance);
	$context = get_context_instance(CONTEXT_BLOCK, $theBlock->instance->id);

    // Security 
    // require_login();

    // get active catalog from block 

    if (isset($theBlock->config)){
        $catalogid = $theBlock->config->catalogue;
    } else {
        error("This block is not configured");
    }

    $theCatalog->id = $catalogid; // make a small proxy

/// invoke controller.

    $billid = required_param('billid', PARAM_INT);
    $cmd = optional_param('cmd', '', PARAM_TEXT);
    $transid = required_param('transid', PARAM_TEXT);

	if (!$aFullBill = courseshop_get_full_bill($transid, $theBlock)){
		print_error('invalidbillid', 'block_courseshop', $CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=shop&id='.$id.'&pinned='.$pinned);
	}

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot.'/blocks/courseshop/print_preview.css' ?>" type="text/css">
</head>
<body>
<table width="780">
    <tr>
        <td><img src="<?php echo $CFG->wwwroot.'/theme/'.current_theme().'/logo.gif' ?>"></td>
        <td align="right"><a href="#" onclick="window.print();return false;"><?php echo get_string('printorderlink', 'block_courseshop') ?></a></td>

    </tr>
    <tr>
        <td colspan="2" align="center">
        <?php
        $headerstring = ($aFullBill->idnumber) ? get_string('bill', 'block_courseshop') : get_string('ordersheet', 'block_courseshop') ;
        print_heading($headerstring, 'center', 1);
        ?>
        </td>
    </tr>
    <tr>
      <td width="60%" valign="top">
         <b><?php echo get_string('transactioncode', 'block_courseshop') ?>:</b><br /><code style="background-color : #E0E0E0"><?php echo $transid ?></code><br />
         <span class="pttparag"><?php echo get_string('providetransactioncode', 'block_courseshop') ?></span>
      </td>
      <td width="40%" valign="top" align="right" rowspan="5" class="order-preview-seller-address">
         <b><?php echo get_string('on', 'block_courseshop') ?>:</b> <?php echo userdate($aFullBill->emissiondate) ?><br />
         <br />
         <b><?php echo $CFG->block_courseshop_sellername ?></b><br />
         <b><?php echo $CFG->block_courseshop_selleraddress ?></b><br />
         <b><?php echo $CFG->block_courseshop_sellerzip ?> <?php echo  $CFG->block_courseshop_sellercity ?></b><br />
         <?php echo $CFG->block_courseshop_sellercountry ?>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php echo get_string('customer', 'block_courseshop') ?>: </b> <?php echo $aFullBill->customer->lastname ?> <?php echo $aFullBill->customer->firstname ?>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php echo get_string('city') ?>: </b>
         <?php echo $aFullBill->customer->zip ?> <?php echo $aFullBill->customer->city ?>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php echo get_string('country') ?>: </b> <?php echo  strtoupper($aFullBill->customer->country) ?>
      </td>
      <td>
      &nbsp;
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php echo get_string('email') ?>: </b> <?php echo $aFullBill->customer->email ?>
      </td>
   </tr>
   <tr>
      <td colspan="2">
      &nbsp;<br />
      </td>
   </tr>
   <tr>
      <td colspan="2" class="sectionHeader">
         <?php print_heading(get_string('order', 'block_courseshop'), 'left', 2); ?>
      </td>
   </tr>
   <tr>
      <td colspan="2">
         <table cellpadding="4" class="order-preview-order-list" width="100%">
           <tr valign="top">
              <th width="41%" class="header c0">
                 <?php echo get_string('designation', 'block_courseshop') ?>
              </th>
              <th width="22%" class="header c1">
                 <?php echo get_string('code', 'block_courseshop') ?>
              </th>
              <th width="14%" class="header c2">
                 <?php echo get_string('unitprice', 'block_courseshop') ?>
              </th>
              <th width="9%" class="header c3">
                 <?php echo get_string('quantity', 'block_courseshop') ?>
              </th>
              <th width="13%" align="right" class="header lastcol">
                 <?php echo get_string('totalTTC', 'block_courseshop') ?>
              </th>
           </tr>
<?php
foreach($aFullBill->items as $portlet){
    include ($CFG->dirroot.'/blocks/courseshop/lib/orderLine.portlet.php');
}
?>
           </table>
      </td>
   </tr>
</table>
<table cellspacing="5" class="generaltable" width="780">
   <tr>
      <td colspan="3">
         <?php print_heading(get_string('totals', 'block_courseshop'), 'left', 2); ?>
      </td>
   </tr>
   <tr>
      <td width="40%" valign="top" rowspan="5">
      &nbsp;
      </td>
      <td width="40%" valign="top" class="cell c1">
         <?php print_string('subtotal', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" valign="top" class="cell lestcol">
         <?php echo $aFullBill->unshippedtaxedamount ?>&nbsp;<?php echo block_courseshop_currency($theBlock, 'symbol') ?>&nbsp;
      </td>
   </tr>
<?php
	if ($aFullBill->discount != 0){
?>
   <tr>
      <td width="40%" valign="top" class="totaltitle ratio">
         <?php print_string('discount', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" valign="top" class="totals ratio">
         <?php  echo "<b>-" . ($CFG->block_courseshop_discountrate) . "%</b>" ; ?>
      </td>
   </tr>
<?php 
}
if (!empty($CFG->block_courseshop_hasshipping) || ($aFullBill->shipping->value != 0)){
	if ($aFullBill->discount != 0){
?>
   <tr>
      <td width="40%" valign="top" class="totaltitle">
         <?php print_string('totaldiscounted', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" valign="top" class="totals">
         <?php echo $aFullBill->discountedamount ?>&nbsp;<?php echo block_courseshop_currency($theBlock, 'symbol') ?>&nbsp;
      </td>
   </tr>
<?php
	}
?>
   <tr>
      <td width="40%" valign="top" class="totaltitle">
         <?php print_string('shipping', 'block_courseshop') ?>:
      </td>
      <td width="20%" align="right" valign="top" class="totals">
         <?php echo $aFullBill->shipping->taxedvalue ?>&nbsp;<?php echo block_courseshop_currency($theBlock, 'symbol') ?>&nbsp;
      </td>
   </tr>
<?php
}
?>
   <tr>
      <td width="40%" valign="top" class="totaltitle topay">
         <b><?php print_string('totalprice', 'block_courseshop') ?></b>:
      </td>
      <td width="20%" align="right" valign="top" class="total topay">
         <b><?php echo $aFullBill->totaltaxedamount ?>&nbsp;<?php echo block_courseshop_currency($theBlock, 'symbol') ?></b>&nbsp;
      </td>
   </tr>
</table>
<table width="780">
   <tr>
      <td colspan="2">
         <?php print_heading(get_string('paymentmode', 'block_courseshop'), 'left', 2) ?>
      </td>
   </tr>
   <tr>
      <td colspan="2" align="left">
		<?php
			require_once $CFG->dirroot.'/blocks/courseshop/paymodes/'.$aFullBill->paymode.'/'.$aFullBill->paymode.'.class.php';
			$classname = 'courseshop_paymode_'.$aFullBill->paymode;
			$pm = new $classname($theBlock);
			$pm->print_name();
		?>
      </td>
   </tr>
   <tr>
      <td colspan="2">
         <p><?php echo  get_string('forquestionssendmailto', 'block_courseshop') ?>: <a href="mailto:<?php echo $CFG->block_courseshop_sellermail ?>"><?php echo $CFG->block_courseshop_sellermail ?></a>
      </td>
   </tr>
</table>
</body>
</html>