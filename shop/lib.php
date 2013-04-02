<?php

require_once $CFG->libdir.'/pear/HTML/AJAX/JSON.php';
require_once $CFG->dirroot.'/auth/ticket/lib.php';

if (!defined('PHP_ROUND_HALF_EVEN')) define('PHP_ROUND_HALF_EVEN', 3);
if (!defined('PHP_ROUND_HALF_ODD')) define('PHP_ROUND_HALF_ODD', 3);


/**
* prints a purchase procedure progression bar
*
*/
function courseshop_print_progress($step){
    global $CFG;

	echo '<div id="progress">';
	echo '<center>';
    $lang = current_language();
    $theme = current_theme();
    if (file_exists($CFG->dirroot."/theme/{$theme}/blocks/courseshop/lang/{$lang}/{$step}.png")){
    	echo "<img src=\"{$CFG->wwwroot}/theme/{$theme}/blocks/courseshop/lang/{$lang}/{$step}.png\" />";
    } else {
	    echo "<img src=\"{$CFG->wwwroot}/blocks/courseshop/lang/$lang/{$step}.png\" />";
	}
	echo '</center>';
	echo '</div>';

}

/**
* get the full productline from cetegories
*
*/
function courseshop_get_all_products(&$categories, $theCatalog){
    global $CFG, $SESSION;

	$isloggedinclause = '';
	if (empty($SESSION->courseshopseeall)){
	    $isloggedinclause = (isloggedin()) ? ' AND onlyforloggedin > -1  ' : ' AND onlyforloggedin < 1 ' ;
	}

    $shopproducts = array();

    foreach($categories as $key => $aCategory){


        // get master catalog items
        /*
        product might be standalone product or set or bundle
        */
        $shopproducts = array();
        if ($theCatalog->isslave){
            $sql = "
               SELECT
                  ci.*
               FROM
                  {$CFG->prefix}courseshop_catalogitem as ci
               WHERE
                  ci.catalogid = '{$theCatalog->groupid}' AND
                  ci.categoryid = '{$aCategory->id}' AND
                  ci.status IN ('AVAILABLE','PROVIDING') AND
                  ci.setid = 0
                  $isloggedinclause
               ORDER BY
                  ci.shortname
            ";
            $products = get_records_sql($sql);
            foreach($products as $aProduct){
                if ($aProduct->thumb  == ''){
                    if ($aProduct->image  == ''){
                       $aProduct->thumb = $CFG->wwwroot.'/blocks/courseshop/pix/defaultProduct.gif';
                    } else {
                       $aProduct->thumb = $aProduct->image;
                    }
                }
                $shopproducts[$aProduct->code] = $aProduct; 
            }
        }

        // override with slave versions
        $sql = "
           SELECT
              ci.*
           FROM
              {$CFG->prefix}courseshop_catalogitem as ci
           WHERE
              catalogid = '{$theCatalog->id}' AND
              categoryid = '{$aCategory->id}' AND
              ci.status IN ('AVAILABLE','PROVIDING') AND
              setid = 0
              $isloggedinclause
           ORDER BY
              ci.shortname
        ";
        if ($products = get_records_sql($sql)){
            foreach($products as $aProduct){
                if ($aProduct->thumb  == ''){
                    if ($aProduct->image  == ''){
                       $aProduct->thumb = $CFG->wwwroot.'/blocks/courseshop/pix/defaultProduct.gif';
                    } else {
                       $aProduct->thumb = $aProduct->image;
                    }
                }
                $shopproducts[$aProduct->code] = $aProduct;
            }
        }

        foreach(array_values($shopproducts) as $aProduct){
            if ($aProduct->isset){
                $set = array();
    
                // get set elements in master catalog (same set code)
                if ($theCatalog->isslave){
                    $sql = "
                      SELECT
                        ci.*
                      FROM
                        {$CFG->prefix}courseshop_catalogitem as ci,
                        {$CFG->prefix}courseshop_catalogitem as cis
                      WHERE
                        ci.setid = cis.id AND
                        cis.code = '{$aProduct->code}' AND
                        ci.status IN ('AVAILABLE','PROVIDING') AND
                        ci.catalogid = '{$theCatalog->groupid}'
                        $isloggedinclause
		              ORDER BY
		                ci.shortname
                    ";
                    $products = get_records_sql($sql);
                    foreach($products as $aSetElement){
                        if ($aSetElement->thumb  == '') {
                            if ($aSetElement->image  == ''){
                               $aSetElement->thumb = $CFG->wwwroot.'/blocks/courseshop/pix/defaultProduct.gif';
                            } else {
                               $aSetElement->thumb = $aSetElement->image;
                            }
                        }
                        $set[$aSetElement->code] = $aSetElement;
                    }
                }
    
                // override with local versions
                $sql = "
                  SELECT
                    ci.*
                  FROM
                    {$CFG->prefix}courseshop_catalogitem as ci
                  WHERE
                    ci.setid = '{$aProduct->id}' AND
                    ci.status IN ('AVAILABLE','PROVIDING') AND
                    ci.catalogid = '{$theCatalog->id}'
                    $isloggedinclause
               	  ORDER BY
                    ci.shortname
                ";                
                $set = array();
                if ($products = get_records_sql($sql)){
	                foreach($products as $aSetElement){
	                    if ($aSetElement->thumb == '') {
	                        if ($aSetElement->image == ''){
	                           $aSetElement->thumb = $CFG->wwwroot.'/blocks/courseshop/pix/defaultProduct.gif';
	                        } else {
	                           $aSetElement->thumb = $aSetElement->image;
	                        }
	                    }
	                    $set[$aSetElement->code] = $aSetElement;
	                }
	            }
                $shopproducts[$aProduct->code]->set = $set;
            }
        }
        $categories[$key]->products = array_values($shopproducts);
    }
    
    return $shopproducts;
}

function courseshop_get_billing_productline(&$codeToShortname, &$products, &$theCatalog){
    global $CFG;

    $shopproducts = array();
    
    $sql = "
        SELECT
            ci.*,
            t.ratio as ratio,
            t.formula as formula
        FROM
            {$CFG->prefix}courseshop_catalogitem as ci,
            {$CFG->prefix}courseshop_tax as t
        WHERE
            catalogid = '{$theCatalog->id}' AND
            t.id = ci.taxcode AND
            (isset = '0' OR isset = 'B') AND
            status IN('AVAILABLE','PROVIDING')
    ";
    $products = get_records_sql($sql);

    $codeToShortname = array();
    foreach($products as $key => $product){

        // calculate tax amounts
        $HT = $product->price1;
        $TR = $product->ratio;
        if (empty($product->formula)) $product->formula = '$TTC = $HT';
        eval($product->formula.';');

        $products[$key]->taxamount = round(($TTC - $product->price1) * 100) / 100;
        $products[$key]->taxedprice = $TTC;
        $shopproducts[$product->shortname] = $key;
        $codeToShortname[$product->code] = $product->shortname;
    }
    
    return $shopproducts;
}

/**
* prints the payment block on GUI
*
*/
function courseshop_print_payment_block(&$theBlock){
	global $SESSION;

    print_heading(get_string('paymentmethod', 'block_courseshop'));

	echo '<table width="100%" id="paymodes">';
   	echo '<tr>';
    echo '<td valign="top" colspan="1">';
	print_string('paymentmode', 'block_courseshop');
	echo '<sup>*</sup>:';
	echo '</td></tr>';
   	echo '<tr><td valign="top" align="left">';

	// Checking  paymodes availibility and creating radios
	$paymodes = get_list_of_plugins('/blocks/courseshop/paymodes', 'CVS');
	foreach($paymodes as $var){
		$isenabledvar = "enable$var";
		if (!empty($theBlock->config->$isenabledvar)){
			// set default paymode as first available
			if (empty($SESSION->shoppingcart['paymode'])){
				$default = (empty($theBlock->config->defaultpaymode)) ? $var : $theBlock->config->defaultpaymode;
				$SESSION->shoppingcart['paymode'] = $default ;
				$paymode = $default;
			} else {
				$paymode = $SESSION->shoppingcart['paymode'];
			}
			$checked = ($paymode == $var) ? 'checked="checked" ' : '' ;
			echo "<input type=\"radio\" name=\"paymode_sel\" value=\"$var\" onclick=\"document.forms['caddie'].paymode.value = '$var';\" $checked /> <em>";
			if ($checked){
				echo '<script type="text/javascript">';
				echo "document.forms['caddie'].paymode.value = '$var';";
				echo '</script>';										
			}
			print_string($isenabledvar.'2', 'block_courseshop');
			echo '</em><br/>';
		}
	}
	echo '</td></tr></table>';
	return $paymode;
}

function courseshop_get_full_bill($transid, &$theBlock){
	global $CFG;

    // Get product line from base
    $sql = "
       SELECT
          ci.*
       FROM
          {$CFG->prefix}courseshop_catalogitem as ci
       WHERE
          catalogid = '{$theBlock->config->catalogue}' AND
          isset = '0' AND
          status IN ('AVAILABLE','PROVIDING')
    ";
    $codeToShortname = array();
    
    if ($products = get_records_sql($sql)){
        foreach($products as $key => $product){
            // calculate tax amounts
            $TTC = courseshop_calculate_taxed($product->price1, $product->taxcode);
            $products[$key]->taxamount = $TTC - $product->price1;
            $products[$key]->taxedprice = $TTC;
            $shopproducts[$product->shortname] = $product;
            $codeToShortname[$product->code] = $product->shortname;
        }
    }

	$sql = "
      SELECT 
         *
      FROM
         {$CFG->prefix}courseshop_bill as b
      WHERE
         b.transactionid = '{$transid}'
    ";
    $aFullBill = get_record_sql($sql);

	if ($aFullBill){

	    $aFullBill->shipping->value = 0;
	    $aFullBill->shipping->taxedvalue = 0;
	    $aFullBill->discount = 0;
	    $aFullBill->unshippedamount = 0;
	    $aFullBill->unshippedtaxedamount = 0;
	    $aFullBill->totalamount = 0;
	    $aFullBill->totaltaxedamount = 0;
	    $aFullBill->totaluntaxedamount = 0;
	    $aFullBill->discountedamount = 0;
	    $aFullBill->totaltaxes = 0;
	    $aFullBill->taxes = array();

	    $aFullBill->customer = get_record('courseshop_customer', 'id', $aFullBill->userid);
		if ($aFullBill->customer->hasaccount){
			// this is a known customer. PrePay process will bypass.
		    if ($aFullBill->user = get_record('user', 'id', $aFullBill->customer->hasaccount)){
		    	// we need sending email whatever the user wants
			    $aFullBill->user->emailstop = 0;
			}
		} else {
			// this is a new customer. PrePay process might create the user from the customer.
			$aFullBill->user = null;
		}

	    if($aFullBill->items = get_records('courseshop_billitem', 'billid', $aFullBill->id, 'ordering')){
	
	        $aFullBill->itemcount = 0;
	
	        foreach($aFullBill->items as $key => $anItem){
	            if($anItem->type == 'BILLING'){
	                if (preg_match("/^SHIP_/", $anItem->itemcode)){
	                     $aFullBill->shipping->value += $anItem->totalprice;
	                     $taxedshipping = courseshop_calculate_taxed($anItem->totalprice, $anItem->taxcode);
	                     $aFullBill->shipping->taxedvalue += $taxedshipping;
	                     $aFullBill->totalamount += $anItem->totalprice;
	                     $aFullBill->totaltaxedamount += $taxedshipping;
	                     $aFullBill->totaltaxes += $taxedshipping - $anItem->totalprice;
						 $aFullBill->taxes[$anItem->taxcode]->amount = 0 + @$aFullBill->taxes[$anItem->taxcode] + $taxedshipping - $anItem->totalprice;
	                     unset($aFullBill->items[$key]);
	                     continue;
	                }
	                if (preg_match("/^DISCOUNT/", $anItem->itemcode)){
	                     $aFullBill->discount += $anItem->totalprice;
	                     $aFullBill->totaltaxedamount += $aFullBill->discount;
	                     unset($aFullBill->items[$key]);
	                     continue;
	                }
	
	                $aFullBill->itemcount += $anItem->quantity;
	                $productKey = $codeToShortname[$anItem->itemcode];
	                $aFullBill->items[$key]->code = $shopproducts[$productKey]->code;
	                $aFullBill->items[$key]->name = $shopproducts[$productKey]->name;
	                $aFullBill->items[$key]->ratio = get_field('courseshop_tax', 'ratio', 'id', $shopproducts[$productKey]->taxcode);
	                $aFullBill->items[$key]->taxamount = $shopproducts[$productKey]->taxamount;
	                $aFullBill->items[$key]->enablehandler = $shopproducts[$productKey]->enablehandler;
	                if (!empty($aFullBill->items[$key]->customerdata)){
	                	$aFullBill->items[$key]->required = json_decode(base64_decode($aFullBill->items[$key]->customerdata));
	                }
	                if($anItem->taxcode){
		                $taxed = courseshop_calculate_taxed($anItem->totalprice, $anItem->taxcode);
		            } else {
		            	$anItem->totalprice;
		            }
	                $aFullBill->items[$key]->taxedprice = $taxed;
					$aFullBill->totalamount += $anItem->totalprice;
					$aFullBill->totaltaxedamount += $taxed;
					$aFullBill->unshippedamount += $anItem->totalprice;
					$aFullBill->unshippedtaxedamount += $taxed;
					$aFullBill->totaltaxes += $taxed - $anItem->totalprice;
					$aFullBill->taxes[$anItem->taxcode]->amount = 0 + @$aFullBill->taxes[$anItem->taxcode]->amount + $taxed - $anItem->totalprice;
	                // traps shipping and discount
	            }
	        }
	    }
	    
	    // complete tax table
	    foreach(array_keys($aFullBill->taxes) as $taxid){
	    	$taxdef = get_record('courseshop_tax', 'id', $taxid);
	    	$aFullBill->taxes[$taxid]->title = $taxdef->title;
	    	$aFullBill->taxes[$taxid]->ratio = $taxdef->ratio;
	    	$aFullBill->taxes[$taxid]->amount = sprintf('%0.2f', round($aFullBill->taxes[$taxid]->amount, 2));
	    }
	    
	    // note discount is calculated pre-shiping
	    $aFullBill->discountedtaxedamount = 0;
	    if ($aFullBill->discount){
	    	$aFullBill->discountedtaxedamount = $aFullBill->unshippedtaxedamount + $aFullBill->discount; // negative discount
	    }
	
	    $aFullBill->shipping->value = sprintf('%0.2f', round($aFullBill->shipping->value, 2));
	    $aFullBill->shipping->taxedvalue = sprintf('%0.2f', round($aFullBill->shipping->taxedvalue, 2));
	    $aFullBill->discount = sprintf('%0.2f', round($aFullBill->discount, 2));
	    $aFullBill->unshippedamount = sprintf('%0.2f', round($aFullBill->unshippedamount, 2));
	    $aFullBill->unshippedtaxedamount = sprintf('%0.2f', round($aFullBill->unshippedtaxedamount, 2));
	    $aFullBill->totalamount = sprintf('%0.2f', round($aFullBill->totalamount, 2));
	    $aFullBill->totaltaxedamount = sprintf('%0.2f', round($aFullBill->totaltaxedamount, 2));
	    $aFullBill->totaluntaxedamount = sprintf('%0.2f', round($aFullBill->totaluntaxedamount, 2));
	    $aFullBill->discountedtaxedamount = sprintf('%0.2f', round($aFullBill->discountedtaxedamount, 2));
	    $aFullBill->totaltaxes = sprintf('%0.2f', round($aFullBill->totaltaxes, 2));
	}
	
	return $aFullBill;
}

function courseshop_print_printable_bill_link($billid, $transid, $blockid, $pinned){
	global $CFG;
	
	echo "<form name=\"bill\" action=\"{$CFG->wwwroot}/blocks/courseshop/shop/order.popup.php\" target=\"_blank\" />";
	echo "<input type=\"hidden\" name=\"transid\" value=\"{$transid}\" />";
	echo "<input type=\"hidden\" name=\"billid\" value=\"{$billid}\">";
	echo "<input type=\"hidden\" name=\"id\" value=\"{$blockid}\">";
	echo "<input type=\"hidden\" name=\"pinned\" value=\"{$pinned}\">";
	echo '<table><tr valign="top"><td align="center">';
    echo '<br /><br /><br /><br />';
    $options = '';            
    $billurl = $CFG->wwwroot."/blocks/courseshop/shop/order.popup.php?id={$blockid}&pinned={$pinned}&billid={$billid}&transid={$transid}";
    $customerid = get_field('courseshop_bill', 'userid', 'id', $billid); 
    $userid = get_field('courseshop_customer', 'hasaccount', 'id', $customerid);
    $billuser = get_record('user', 'id', $userid);
    $ticket = ticket_generate($billuser, 'immediate access', $billurl);
    button_to_popup_window ("/login/index.php?ticket={$ticket}" , get_string('printbill', 'block_courseshop'), get_string('printbill', 'block_courseshop'), 1024, 840, get_string('order', 'block_courseshop'), $options);
    $backtoshopstr = get_string('backtoshop', 'block_courseshop');
	echo "<input type=\"button\" name=\"cancel_btn\" value=\"{$backtoshopstr}\" onclick=\"self.location.href='{$CFG->wwwroot}/blocks/courseshop/shop/view.php?view=shop&id={$blockid}&pinned={$pinned}'\" />";
	echo '</td></tr></table>';
	echo '</form>';
	
}

function courseshop_print_customer_info(&$aFullBill){

	echo '<div id="customerinfo">';
	echo '<table cellspacing="4" width="100%">';
   	echo '<tr><td width="60%" valign="top">';
	echo '<b>'.get_string('orderID', 'block_courseshop').'</b>'. $aFullBill->id;
	echo '</td><td width="40%" valign="top" align="right">';
	echo '<b>'.get_string('on', 'block_courseshop').':</b> '.userdate($aFullBill->emissiondate);
	echo '</td></tr>';

   	echo '<tr><td width="60%" valign="top">';
	echo '<b>'.get_string('customer', 'block_courseshop').':</b> '.$aFullBill->customer->lastname.' '.$aFullBill->customer->firstname;
	echo '</td><td width="40%" valign="top">';
	echo '</td></tr>';
	
	echo '<tr><td width="60%" valign="top">';
	echo '<b>'.get_string('city').': </b> '.$aFullBill->customer->zip.' '.$aFullBill->customer->city;
	echo '</td><td width="40%" valign="top">';
	echo '</td></tr>';

	echo '<tr><td width="60%" valign="top">';
	echo '<b>'.get_string('country').':</b> '.strtoupper($aFullBill->customer->country);
	echo '</td><td width="40%" valign="top">';
	echo '</td></tr>';

	echo '<tr><td width="60%" valign="top">';
	echo '<b>'.get_string('email').':</b> '.$aFullBill->customer->email;
	echo '</td><td width="40%" valign="top">';
	echo '</td></tr>';
	echo '</table>';
	echo '</div>';
}

function courseshop_print_local_confirmation_form($requireddata){
	global $CFG;
	
	$confirmstr = get_string('confirm', 'block_courseshop');
	$disabled = (!empty($requireddata)) ? 'disabled="disabled"' : '' ;
	echo '<center>';
	echo "<form name=\"confirmation\" method=\"POST\" action=\"{$CFG->wwwroot}/blocks/courseshop/shop/view.php\" style=\"display : inline\">";
	echo '<table style="display : block ; visibility : visible" width="100%"><tr><td align="center">';
	if (!empty($disabled)){
		echo '<br><span id="disabled-advice-span" class="error">'.get_string('requiredataadvice', 'block_courseshop').'</span><br/>';
	}
	echo "<input type=\"button\" name=\"go_confirm\" value=\"$confirmstr\" onclick=\"send_confirm();\" {$disabled} />";
	echo '</td></tr></table>';
	echo '</form>';
	echo '</center>';
}

/**
* this function calculates an overall shipping additional line to be added to bill
* regarding order elements and location of customer. It will use all rules defined
* in shipping zones and shipping meta-information. 
*
* If shipzone has a 'billscopeamount' defined, this amount is used as unique shipping value
* once the shipping zone is assigned. When no zone can be disciminated using applicability
* rules, then the default zone of code '00' (if exists) is used against the same process.
*
* If shipzone has no billscopeamount defined, but has some product shipping information setup, 
* the order is scanned for entries matching the presence of shipping rules. If the rule has a 
* fixed value, then this value is used independantely of the quantity. If no value is defined, 
* but a formula, the formula is evaluated using $HT as unit untaxed price, $TTC ad unit taxed
* price, $Q as quantity, and $a, $b, $c as three coefficient values defined in the shipping.
*
* for security reasons all pseudo variables (startign with $ in formula are discarded, and the
* formula may not be parsable any more.  
*
* Applicability checks: 
*
* applicability of a zone is a set description that allow matching a country/zipcode condition.
* the applicability is a union of matching rules (rule1)op(rule2)
* 
* Rules can be combined using & (and) or | (or) operator
* 
* a Rule is divided in two sets : a set of accepted countrycodes, and a pattern of accepted zipcodes.
* Rule sample : [*][*] all locations in the world
* Rule sample : [fr,uk][*] all zips in uk and france
* Rule sample : [*][000$] all zip in all countries finishing by 000 
* Rule sample : [*][*000] similar to above
* Rule sample : [fr][^9.....$] zip with 6 digits starting with 9 in france (DOM-TOM)
* Rule sample : [fr][06...,83...,04...,05...] all cities in south east of france
*
* @param text $country Country code
* @param text $zipcode Customer zipcode
* @param array $order array of ordered elements (quantity keyed by catalogitem label)
* @return an object providing entries for a billitem setup as shipping additional
* pseudo product
*/
function courseshop_calculate_shipping($catalogid, $country, $zipcode, $order, $transactionid){
	
	courseshop_trace("[{$transactionid}] Courseshop Shipping Calculation for [$country][$zipcode]");

	if (!$shipzones = get_records('courseshop_catalogshipzone', 'catalogid', $catalogid)){
		courseshop_trace('No shipzones');
		// echo "noshipzones ";
		$return->value = 0;
		return $return;
	}
	
	// determinating shipping zone
	function reduce_and($v, $w){
	    return $v && $w;
	}

	function reduce_or($v, $w){
	    return $v || $w;
	}

	$applicable = null;
	foreach($shipzones as $z){
		if ($z->zonecode == '00') {
			$defaultzone = $z;
			continue; // optional '00' special default zone is considered 'in fine'
		}
		$ands = preg_split('/&\|/', $z->applicability); // detokenize &
		for($i = 0 ; $i < count($ands) ; $i++){
			// echo "examinating and rule ".$ands[$i];
			if (strstr('|', $ands[$i])){
				$ors = preg_split('/\|/', $ands[$i]); // detokenize |				
				for($j = 0 ; $j < count($ors) ; $j++){
					$ors[$j] = courseshop_resolve_zone_rule($country, $zipcode, $ors[$j]);
				}
				$ands[$i] = array_reduce($ors, 'reduce_or', false);
			} else {
				// echo "processing unique and rule ".$ands[$i];
				$ands[$i] = courseshop_resolve_zone_rule($country, $zipcode, $ands[$i]);
			}
		}

		if (array_reduce($ands, 'reduce_and', true)){
			$applicable = $z;
			break;
		} else {
			if (isset($defaultzone)){
				$applicable = $defaultzone;
				break;
			}
			// in spite of shipzones found in the way, none applicable
			courseshop_trace("[{$transactionid}] No shipzone applicable for [$country][$zipcode]");
			// echo "no shipzone applicable ";
			$return->value = 0;
			return $return;
		}
	}

	courseshop_trace("[{$transactionid}] Courseshop Shipping : Found applicable zone $applicable->zonecode ");
	
	// checking bill scope shipping for zone 
	if ($applicable->billscopeamount != 0){
		courseshop_trace("[{$transactionid}] Courseshop Shipping : Using bill scope amount ");
		$return->value = $applicable->billscopeamount;
		$return->code = 'SHIP_';
		$return->taxcode = $applicable->taxid;
        // calculate tax amounts
   		$return->taxedvalue = courseshop_calculate_taxed($return->value, $applicable->taxid);
   		
   		return $return;
	}

	courseshop_trace("[{$transactionid}] Courseshop Shipping : Examinating shippings");

	// examinating products
	if ($shippings = get_records('courseshop_catalogshipping', 'zoneid', $applicable->id)){
		$return->code = 'SHIP_';
		$return->taxcode = $applicable->taxid;
		$return->value = 0;
		foreach($shippings as $sh){
			$shippedproduct = get_record('courseshop_catalogitem', 'code', $sh->productcode);
			// must be a valid product in order AND have some items required
			if (array_key_exists($shippedproduct->shortname, $order) && $order[$shippedproduct->shortname] > 0){
				if ($sh->value > 0){
					$return->value += $sh->value;
				} else {
					if (!empty($sh->formula)){
						$A = $sh->a;
						$B = $sh->b;
						$C = $sh->c;
						$HT = $shippedproduct->price1;
						$TTC = courseshop_calculate_taxed($shippedproduct->price1, $shippedproduct->taxcode);
						$Q = $order[$shippedproduct->shortname];
						eval($sh->formula.';');
						$return->value += 0 + @$SHP;
					} else {
						$return->value += 0;
					}
				}
			}
		}
		if ($return->value > 0){
	   		$return->taxedvalue = courseshop_calculate_taxed($return->value, $applicable->taxid);
	   	} else {
	   		$return->taxedvalue = 0;
	   	}
   		return $return;
	}
	
	// void return if no shipping solution
	courseshop_trace("[{$transactionid}] Courseshop Shipping : No shipping solution");
	// echo "no shipping solution";
	$return->value = 0;
	return $return;
}

/**
* resolves a single geographic rule
*
*/
function courseshop_resolve_zone_rule($country, $zipcode, $rule){
	
	if (preg_match('/\\(\\[(.*?)\\]\\[(.*?)\\]\\)/', $rule, $matches)){
		$countries = strtolower($matches[1]);
		$zipcodes = $matches[2];
		
		$country = strtolower($country); // ensure we have no issues with case.
		
		if ($countries != '*'){
			if (!preg_match("/\\b$country\\b/", $countries)){
				// echo "country $country fails matching $countries ";
				return false;
			}
			// echo 'country matches ';
		} else {
			// echo 'wildcard country ';
		}

		if ($zipcodes != '*'){
			$ziprules = explode(',', $zipcodes);
			foreach($ziprules as $ru){
				if (preg_match("/$ru/", $zipcode)){
					// echo "matching ";
					return true;
				} else {
					// echo "not matching $zipcode for /$ru/ ";
				}
			}
			return false;
		} else {
			// echo 'wildcard zipcode ';
		}
		return true;
	} else {
		// echo "no matching rule $rule ";
	}

	return false;
}
?>