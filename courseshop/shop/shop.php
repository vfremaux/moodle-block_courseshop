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

/// calculate a new transaction id
    $transid = strtoupper(substr(mysql_escape_string(base64_encode(crypt(microtime() + rand(0,32)))), 0, 32));
    while(record_exists('courseshop_bill', 'transactionid', $transid)){
        $transid = strtoupper(substr(mysql_escape_string(base64_encode(crypt(microtime() + rand(0,32)))), 0, 40));
    }
?>

<script type="text/javascript" src="<?php echo $CFG->wwwroot."/blocks/courseshop/js/shop.js.php?id={$id}&pinned={$pinned}" ?>"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot."/blocks/courseshop/js/form_protection.js.php" ?>"></script>

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

    print_box_start();

	if (isloggedin() && has_capability('moodle/site:doanything', get_context_instance(CONTEXT_SYSTEM))){
		print_string('adminoptions', 'block_courseshop');
		
		$disableall = get_string('disableallmode', 'block_courseshop');
		$enableall = get_string('enableallmode', 'block_courseshop');
		if($SESSION->courseshopseeall){
			echo "<a href=\"view.php?view=shop&seeall=0&id=$id&pinned=$pinned\">$disableall</a>";
		} else {
			echo "<a href=\"view.php?view=shop&seeall=1&id=$id&pinned=$pinned\">$enableall</a>";
		}
	
	    print_box_end();
	}

    courseshop_print_progress('SELECT');

    echo "<table width=\"100%\" cellspacing=\"10\"><tr valign=\"top\"><td width=\"*\">";

/// print catalog product line

    foreach($categories as $cat){
        $cat->level = 1;
        print_heading($cat->name, 'center', $cat->level);
        
        if (!function_exists('subportlet')){
            function subportlet(&$portlet){
                global $CFG;
                
                if ($portlet->isset == 1){
                   include($CFG->dirroot.'/blocks/courseshop/lib/productSet.portlet.php');
                }
                elseif ($portlet->isset == PRODUCT_BUNDLE){
                   include($CFG->dirroot.'/blocks/courseshop/lib/bundleBlock.portlet.php');
                } else {
                   include($CFG->dirroot.'/blocks/courseshop/lib/productBlock.portlet.php');
                }
            }
        }
        
        if (!empty($cat->products)){
            foreach($cat->products as $aProduct){
            	$aProduct->currency = block_courseshop_currency($theBlock, 'symbol');
                subportlet($aProduct);
            }
        } else {
            print_string('noproductincategory', 'block_courseshop');
        }
    }

    echo "</td><td width=\"180\" style=\"padding-left:10px\">";

/*
/// Order item counting block 

    print_heading(get_string('order', 'block_courseshop'));
    
    echo "<table width=\"100%\" id=\"orderblock\">";
    foreach($categories as $aCategory){
        foreach($aCategory->products as $aProduct){
            if ($aProduct->isset === 1){
                foreach($aProduct->set as $portlet){
                    $portlet->preset = !empty($SESSION->shoppingcart[$portlet->shortname]) ? $SESSION->shoppingcart[$portlet->shortname] : 0 ;
                    include ($CFG->dirroot.'/blocks/courseshop/lib/shopProductTotalLine.portlet.php');
                }
            } else {
                $portlet = &$aProduct;
                $portlet->preset = !empty($SESSION->shoppingcart[$portlet->shortname]) ? $SESSION->shoppingcart[$portlet->shortname] : 0 ;
                include($CFG->dirroot.'/blocks/courseshop/lib/shopProductTotalLine.portlet.php');
            }
        }
    }
    echo "</table>";
*/

/// Order total block

?>

<table width="100%" id="ordertotals">
   <tr valign="top">
      <td align="left" class="ordercell">
         <b><?php print_string('ordertotal', 'block_courseshop') ?></b> : 
      </td>
      <td align="left" class="ordercell">
         <span id="total_euros_span">0.00</span> 
         <?php echo block_courseshop_currency($theBlock, 'symbol') ?> 
         <?php print_string('for', 'block_courseshop') ?> 
         <span id="object_count_span">0</span> 
         <?php print_string('objects', 'block_courseshop') ?> .
         <input type="hidden" name="totalEurosTTC" value="0">
      </td>
   </tr>
<?php 
if (!empty($CFG->block_courseshop_discountthreshold)){
?>
   <tr>
      <td>
         <?php print_string('ismorethan', 'block_courseshop') ?> <b><?php echo  $CFG->block_courseshop_discountthreshold ?>&nbsp;</b><b><?php echo block_courseshop_currency($theBlock, 'symbol') ?></b>,<br/> 
         <?php print_string('yougetdiscountof', 'block_courseshop') ?> <b><?php echo $CFG->block_courseshop_discountrate  ?> %</b>.<br/>
      </td>
      <td>&nbsp;
      </td>
   </tr>
<?php
}
?>
   <tr valign="bottom">
      <td class="finalcount">
         <b><?php print_string('orderingtotal', 'block_courseshop') ?></b> 
      </td>
      <td align="left" class="finalcount">
         <span id="discounted_span">0.00</span>
         <input type="hidden" name='discounted' size="8" maxlength="6" value="0"> <?php echo block_courseshop_currency($theBlock, 'symbol') ?>
      </td>
   </tr>
   <tr>
      <td align="left" colspan="2">
         <span class="smalltext">(*) <?php print_string('shippingadded', 'block_courseshop') ?><br/></span>
		<?php
		if (empty($CFG->block_courseshop_useshipping)){
			$shipchecked = (@$SESSION->shoppingcart['shipping']) ? 'checked="checked"' : '' ; 
		?>
         <input type="checkbox" name="shipping" value="1" <?php echo $shipchecked ?> /> <?php echo get_string('askforshipping', 'block_courseshop'); ?>
		<?php
		}
		?>
      </td>
   </tr>
</table>

<?php 

/// Payment method block
$paymode = courseshop_print_payment_block($theBlock);

print_heading(get_string('customerinformation', 'block_courseshop')); 
if(isloggedin()){
    $lastname = $USER->lastname;
    $firstname = $USER->firstname;
    $organisation = $USER->institution;
    $city = $USER->city;
    $address = $USER->address;
    $zip = '';
    $country = $USER->country;
    $email = $USER->email;
    
    // get potential ZIP code information from an eventual customer record
    if ($customer = get_record('courseshop_customer', 'hasaccount', $USER->id)){
    	$zip = $customer->zip;
    }
} else {
    $lastname = @$SESSION->shoppingcart['lastname'];
    $firstname = @$SESSION->shoppingcart['firstname'];
    $organisation = @$SESSION->shoppingcart['organisation'];
    $country = @$SESSION->shoppingcart['country'];
    $address = @$SESSION->shoppingcart['address'];
    $city = @$SESSION->shoppingcart['city'];
    $zip = @$SESSION->shoppingcart['zip'];
    $email = @$SESSION->shoppingcart['mail'];
}

?>
<table cellspacing="3" width="100%" id="customerdata">
   <tr valign="top">
      <td align="right">
         <?php print_string('lastname') ?><sup style="color : red">*</sup>:
      </td>
      <td align="left">
         <input type="text" name="lastname" size="20" class="form_attenuated" onchange="setupper(this)" value="<?php p($lastname) ?>" />
      </td>
   </tr>
   <tr valign="top">
      <td align="right">
         <?php print_string('firstname') ?><sup style="color : red">*</sup>:
      </td>
      <td align="left">
         <input type="text" name="firstname" size="20" class="form_attenuated" onchange="capitalizewords(this)" value="<?php p($firstname) ?>" />
      </td>
   </tr>
<?php
if (!empty($theBlock->config->customerorganisationrequired)){
?>
   <tr valign="top">
      <td align="right">
         <?php print_string('organisation', 'block_courseshop') ?>:
      </td>
      <td align="left">
         <input type="text" name="organisation" size="26" maxlength="64" class="form_attenuated" value="<?php p($organisation) ?>" />
      </td>
   </tr>
<?php
}
?>
   <tr valign="top">
      <td align="right">
         <?php print_string('address') ?><sup style="color : red">*</sup>: 
      </td>
      <td align="left">
         <input type="text" name="address" size="26" class="form_attenuated" onchange="setupper(this)" value="<?php p($address) ?>" />
      </td>
   </tr>
   <tr valign="top">
      <td align="right">
         <?php print_string('city') ?><sup style="color : red">*</sup>: 
      </td>
      <td align="left">
         <input type="text" name="city" size="26" class="form_attenuated" onchange="setupper(this)" value="<?php p($city) ?>" />
      </td>
   </tr>
   <tr valign="top">
      <td align="right">
         <?php print_string('zip','block_courseshop') ?><sup style="color : red">*</sup> 
      </td>
      <td align="left">
         <input type="text" name="zip" size="6" class="form_attenuated" value="<?php p($zip) ?>" />
      </td>
   </tr>
   <tr valign="top">
      <td align="right">
         <?php print_string('country') ?><sup style="color : red">*</sup>: <br>
      </td>
      <td align="left">
        <?php 
            // $country = 'FR';
            $choices = get_list_of_countries();
            $choices = array('' => get_string('selectacountry').'...') + $choices;
            choose_from_menu($choices, 'country', $country, 'choose', '', '0', false, false, 0, '', false, false, 'countrybox');
        ?>
      </td>
   </tr>
   <tr valign="top">
      <td align="right">
         <?php print_string('email', 'block_courseshop') ?><sup style="color : red">*</sup>
      </td>
      <td align="left">
         <input type="text" name="mail" size="30" class="form_attenuated" onchange="testmail(this)" value="<?php p($email) ?>" />
      </td>
   </tr>
</table>

<?php

/// Order item counting block 

    print_heading(get_string('order', 'block_courseshop'));
    
    echo "<table width=\"100%\" id=\"orderblock\">";
    foreach($categories as $aCategory){
        foreach($aCategory->products as $aProduct){
            if ($aProduct->isset === 1){
                foreach($aProduct->set as $portlet){
                	$portlet->currency = block_courseshop_currency($theBlock, 'symbol');
                    $portlet->preset = !empty($SESSION->shoppingcart[$portlet->shortname]) ? $SESSION->shoppingcart[$portlet->shortname] : 0 ;
                    include ($CFG->dirroot.'/blocks/courseshop/lib/shopProductTotalLine.portlet.php');
                }
            } else {
                $portlet = &$aProduct;
            	$portlet->currency = block_courseshop_currency($theBlock, 'symbol');
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
