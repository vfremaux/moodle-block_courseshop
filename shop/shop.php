<?php

    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

	// check see all mode in session
	if (isloggedin() && has_capability('moodle/site:doanything', get_context_instance(CONTEXT_SYSTEM))){
		$SESSION->courseshopseeall = optional_param('seeall', @$SESSION->courseshopseeall, PARAM_BOOL);
	}
	
	// pre feed SESSION shoppingcart if required
	$cmd = optional_param('cmd', '', PARAM_TEXT);
	if ($cmd){
		include 'shop.controller.php';
	}

    include_once $CFG->dirroot.'/blocks/courseshop/classes/Catalog.class.php';
	$theCatalog = new Catalog($catalogid);

    $categories = courseshop_get_categories($theCatalog);
    
/// now we browse categories for making the catalog

    $shopproducts = courseshop_get_all_products($categories, $theCatalog);

// if transid exists, get back some customer information to feed the session
	/*
	$transid = optional_param('transid', false, PARAM_TEXT);
	if ($transid){
		$aFullBill = courseshop_get_full_bill($transid, $theBlock);
	}
	*/

/// calculate a new transaction id

    $transid = strtoupper(substr(mysql_escape_string(base64_encode(crypt(microtime() + rand(0,32)))), 0, 32));
    while(record_exists('courseshop_bill', 'transactionid', $transid)){
        $transid = strtoupper(substr(mysql_escape_string(base64_encode(crypt(microtime() + rand(0,32)))), 0, 40));
    }

	require_js($CFG->wwwroot.'/blocks/courseshop/js/form_protection.js.php');
?>

<script type="text/javascript" src="<?php echo $CFG->wwwroot."/blocks/courseshop/js/shop.js.php?id={$id}&pinned={$pinned}" ?>"></script>

<form name="caddie" action="<?php echo $CFG->wwwroot ?>/blocks/courseshop/shop/view.php" method="POST">
<input type="hidden" name="view" value="order" />
<input type="hidden" name="id" value="<?php p($id) ?>" />
<input type="hidden" name="pinned" value="<?php p($pinned) ?>" />
<input type="hidden" disabled name="MANDATORY" value=" paymode firstname lastname city mail zip address" />
<input type="hidden" disabled name="lastname_mandatory" value="<?php print_string('requirelastname', 'block_courseshop') ?>" />
<input type="hidden" disabled name="firstname_mandatory" value="<?php print_string('requirefirstname', 'block_courseshop') ?>" />
<input type="hidden" disabled name="address_mandatory" value="<?php print_string('requireaddress', 'block_courseshop') ?>" />
<input type="hidden" disabled name="city_mandatory" value="<?php print_string('requirecity', 'block_courseshop') ?>" />
<input type="hidden" disabled name="zip_mandatory" value="<?php print_string('requirezip', 'block_courseshop') ?>" />
<input type="hidden" disabled name="mail_mandatory" value="<?php print_string('requiremail', 'block_courseshop') ?>" />
<input type="hidden" disabled name="paymode_mandatory" value="<?php print_string('requirepaymode', 'block_courseshop') ?>" />
<input type="hidden" name="paymode" value="<?php echo @$SESSION->shoppingcart['paymode'] ?>" />
<input type="hidden" name="transid" value="<?php echo $transid ?>" />
<input type="hidden" name="ispublic" value="0" />
<input type="hidden" name="cmd" value="order" />
<center>

<?php 

    if (empty($theBlock->config->shopcaption)){
        error("The courseshop is not configured");
    }

    print_heading(@$theBlock->config->shopcaption);

    print_box_start();

    echo(@$theBlock->config->shopdescription);

    print_box_end();

	if (isloggedin() && has_capability('moodle/site:doanything', get_context_instance(CONTEXT_SYSTEM))){

    	print_box_start();
		print_string('adminoptions', 'block_courseshop');
		
		$disableall = get_string('disableallmode', 'block_courseshop');
		$enableall = get_string('enableallmode', 'block_courseshop');
		$toproductbackofficestr = get_string('gotobackoffice', 'block_courseshop');
		if($SESSION->courseshopseeall){
			echo "<a href=\"view.php?view=shop&seeall=0&id={$id}&pinned={$pinned}\">$disableall</a>";
		} else {
			echo "<a href=\"view.php?view=shop&seeall=1&id={$id}&pinned={$pinned}\">$enableall</a>";
		}
		echo "&nbsp;<a href=\"{$CFG->wwwroot}/blocks/courseshop/products/view.php?view=viewAllProducts&amp;id={$id}&pinned={$pinned}\">$toproductbackofficestr</a>";
	
	    print_box_end();
	}

    courseshop_print_progress('SELECT');

    echo "<table width=\"100%\" cellspacing=\"10\"><tr valign=\"top\"><td width=\"*\">";
	courseshop_print_catalog($theBlock, $categories);
    echo "</td><td width=\"180\" style=\"padding-left:10px\">";

/// Order total block

	courseshop_print_order_totals($theBlock);
	
/// Payment method block

	$paymode = courseshop_print_payment_block($theBlock);
	
	courseshop_print_customer_info_form($theBlock, $theCatalog);

/// Order item counting block 

    print_heading(get_string('order', 'block_courseshop'));
    
    echo "<table width=\"100%\" id=\"orderblock\">";
    foreach($categories as $aCategory){
        foreach($aCategory->products as $aProduct){
            if ($aProduct->isset === 1){
                foreach($aProduct->set as $portlet){
                	$portlet->currency = courseshop_currency($theBlock, 'symbol');
                    $portlet->preset = !empty($SESSION->shoppingcart[$portlet->shortname]) ? $SESSION->shoppingcart[$portlet->shortname] : 0 ;
                    include ($CFG->dirroot.'/blocks/courseshop/lib/shopProductTotalLine.portlet.php');
                }
            } else {
                $portlet = &$aProduct;
            	$portlet->currency = courseshop_currency($theBlock, 'symbol');
                $portlet->preset = !empty($SESSION->shoppingcart[$portlet->shortname]) ? $SESSION->shoppingcart[$portlet->shortname] : 0 ;
                include($CFG->dirroot.'/blocks/courseshop/lib/shopProductTotalLine.portlet.php');
            }
        }
    }
    echo "</table>";

?>
<p align="center"> 
</p>
<table align="center">
	<tr>
	<td>
		<p>(<span style="color : red">*</span>) <?php print_string('mandatories', 'block_courseshop') ?>
	</td>
	<td>
		<?php helpbutton('shopform', get_string('help_informations', 'block_courseshop'), 'block_courseshop'); ?>
	</td>	
	</tr>
</table>	
<p><table width="100%">
   <tr>
      <td align="center">
         <input type="button" name="go_btn" onclick="Javascript:checkZeroEuroCommand()" value="<?php print_string('launchorder', 'block_courseshop') ?>" />
      </td>
   </tr>
</table>

</form>

<?php

echo "</td></tr></table>";

?>

<script type="text/javascript">
    totalize();
</script>
