<?php

    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    include_once $CFG->dirroot.'/blocks/courseshop/mailtemplatelib.php';
    include_once $CFG->dirroot.'/auth/ticket/lib.php';
    
    $billid 		= optional_param('billid', false, PARAM_INT);
    $transid 		= required_param('transid', PARAM_TEXT);
    $applyshipping 	= optional_param('shipping', @$SESSION->shoppingcart['shipping'], PARAM_BOOL);
    // $totalEurosTTC 	= required_param('totalEurosTTC',PARAM_NUMBER);
    // $discounted 	= required_param('discounted',PARAM_NUMBER);
    $transactionFail = '';
    
    // customer data
    $SESSION->shoppingcart['firstname'] = $firstname = required_param('firstname', PARAM_TEXT);
    $SESSION->shoppingcart['lastname'] = $lastname = required_param('lastname', PARAM_TEXT);
    $SESSION->shoppingcart['address'] = $address = required_param('address', PARAM_TEXT);
    $SESSION->shoppingcart['city'] = $city = required_param('city', PARAM_TEXT);
    $SESSION->shoppingcart['zip'] = $zip = optional_param('zip', '', PARAM_TEXT);
    $SESSION->shoppingcart['organisation'] = $organisation = optional_param('organisation', '', PARAM_TEXT);
    $SESSION->shoppingcart['country'] = $country = required_param('country', PARAM_TEXT);
    $SESSION->shoppingcart['paymode'] = $paymode = required_param('paymode', PARAM_ALPHA);
    $SESSION->shoppingcart['mail'] = $mail = required_param('mail', PARAM_TEXT);

/// check existance of a moodle user with this information, based on mail similarity

	// trap if same user exists and anonymous unlogged purchase or using another email than your account
	if (record_exists('user', 'email', $mail) && (!isloggedin() || (isloggedin() && $mail != $USER->email))){
		notify(get_string('sameuserexists', 'block_courseshop', $mail));
		
		$options['id'] = $id;
		$options['pinned'] = $pinned;
		echo "<table width=\"70%\"><tr><td>";
		print_single_button($CFG->wwwroot.'/blocks/courseshop/shop/view.php', $options, get_string('backtooutdoorsshop', 'block_courseshop'));
		echo "</td><td>";
		print_single_button($CFG->wwwroot.'/login/index.php', null, get_string('login'));
		echo "</td></tr></table>";
		print_footer();
		die;
	}
    
/// input validation process.

    unset($SESSION->shoppingcart['errors']);

    if (empty($firstname)){
        $errors['firstname'] = get_string('erroremptyfirstname', 'block_courseshop');
    }

    if (empty($lastname)){
        $errors['lastname'] = get_string('erroremptylastname', 'block_courseshop');
    }
    
    if (empty($city)){
        $errors['city'] = get_string('erroremptycity', 'block_courseshop');
    }

    if (empty($zip)){
        $errors['zip'] = get_string('erroremptyzip', 'block_courseshop');
    }

    if (empty($country)){
        $errors['country'] = get_string('erroremptycountry', 'block_courseshop');
    }
    
    if (empty($paymode)){
        $errors['paymode'] = get_string('erroremptypaymode', 'block_courseshop');
    }

    if (empty($mail)){
        $errors['mail'] = get_string('erroremptymail', 'block_courseshop');
    }
    // testing if we do not have a user already registered with that email
	if (!isloggedin() && $olduser = get_record('user', 'email', $mail)) {   
		$errors['mail'] = get_string('existingmailpleaselogin', 'block_courseshop', $CFG->wwwroot);
	}
    
    // Wait before returning we can register the information about the order.

/// Get product line from base (standalone and bundles). Note that bundle parts are retrieved as standalones. 

    $shopproducts = courseshop_get_billing_productline($codeToShortname, $products, $theCatalog);
    
/// at this point all products in catalog are known with tax amounts (tax not relevant for bundles)

    // first record, prepare for insert

    if ($cmd == 'order'){
        // order items are collected here
        foreach(array_keys($shopproducts) as $anItem){
            $SESSION->shoppingcart[$anItem] = $order[$anItem] = optional_param($anItem, 0, PARAM_INT);
        }
        
        // if there were some errors, we have now the order in session. we need go back to shop
        if (!empty($errors)){
            echo "<span style=\"color:red\">";
            foreach($errors as $key => $errormessage){
                $SESSION->shoppingcart['errors'][$key] = $errormessage;
                echo $errormessage.'<br/>';
            }
            echo '</span>';
            redirect($CFG->wwwroot."/blocks/courseshop/shop/view.php?view=shop&id=$id&pinned=$pinned");
        }
    
        // record of customer/prospect if not in customer base
        if (isloggedin()){ // catch the current moodleuser if a logged in user is new customer.
        	if(!$customer = get_record('courseshop_customer', 'hasaccount', $USER->id)){
		        $customer->email = $mail;
		        $customer->address = $address;
		        $customer->firstname = $firstname;
		        $customer->lastname = $lastname;
		        $customer->organisation = $organisation;
		        $customer->city = $city;
		        $customer->zip = $zip;
		        $customer->country = strtoupper($country);
    			$customer->hasaccount = $USER->id;
            	$customer->id = insert_record('courseshop_customer', $customer);
        	} else {
		        $customer->email = $mail;
		        $customer->address = $address;
		        $customer->firstname = $firstname;
		        $customer->lastname = $lastname;
		        $customer->organisation = $organisation;
		        $customer->city = $city;
		        $customer->zip = $zip;
		        $customer->country = strtoupper($country);
            	update_record('courseshop_customer', $customer);
        	}
        } else {
	        if(!$customer = get_record('courseshop_customer', 'email', $mail)){
	            $customer->email = $mail;
	            $customer->address = $address;
	            $customer->firstname = $firstname;
	            $customer->lastname = $lastname;
		        $customer->organisation = $organisation;
	            $customer->city = $city;
	            $customer->zip = $zip;
	            $customer->country = strtoupper($country);
				$customer->hasaccount = 0;
            	// might we try to catch external customers that are already users inside ? 
            	if ($user = get_record_select('user', " email = '$mail' AND LOWER(firstname) = LOWER('$firstname') AND LOWER(lastname) = LOWER('$lastname') " )){
            		$customer->hasaccount = $user->id;
            	}
	            $customer->id = insert_record('courseshop_customer', $customer);
	        } else {
	            $customer->address = $address;
	            $customer->firstname = $firstname;
	            $customer->lastname = $lastname;
		        $customer->organisation = $organisation;
	            $customer->city = $city;
	            $customer->zip = $zip;
	            $customer->country = strtoupper($country);
	            update_record('courseshop_customer', $customer);
	        }
        }
    }
    
/// Calculating shipping - Collects all products based on catalog description in explicit variables sent by the shop

    // converts this varset in a product code keyed hash

    if (!empty($CFG->block_courseshop_useshipping) || $applyshipping){
        $shipping = courseshop_calculate_shipping($catalogid, $country, $zip, $order, $transid);
    } else {
        $shipping->value = 0;
        $shipping->taxedvalue = 0;
    }

    if ($shipping->value == -1){
        include($CFG->wwwroot."/blocks/courseshop/lang/".current_language()."/outofshipping.html");
        $backtoshopstr = get_string('backtoshop', 'block_courseshop');
        echo "<p><a href=\"{$CFG->wwwroot}/blocks/courseshop/shop/view.php?id={$id}&pinned={$pinned}\">$backtoshopstr</a></p>";

    }    

    $totalEuros = 0;
    $totalUntaxed = 0;
    $orderAmount = 0;

/// first record of order (unconfirmed)

    if ($cmd == 'order'){

        // set tax status for insert

        $ignoreTax = 1;
        if (strtolower($country) == 'fr'){
            $ignoreTax = 0;
        }

        $bill = get_record('courseshop_bill', 'transactionid', $transid);
   
    // create new bill

        $now = time();

        if (!$bill){
			
            $bill = new StdClass;
			$bill->autobill = 1;
            $bill->userid = $customer->id;
            $bill->title = "{$SITE->fullname} Online Bill";
            $bill->worktype = 'OTHER';
            $bill->status = 'DELAYED';
            $bill->emissiondate = $now;
            $bill->lastactiondate = $now;
            $lastordering = get_field_sql('SELECT max(ordering) FROM '.$CFG->prefix.'courseshop_bill');
			$lastordering = $lastordering + 1;
			$bill->ordering = $lastordering;
			$bill->assignedto = 0;
            $bill->timetodo = 7; 
            $bill->taxes = 0;
            $bill->amount = 0;
            $bill->currency = (!@$theBlock->currency) ? $CFG->block_courseshop_defaultcurrency : $theBlock->currency ;
            $bill->convertedamount = 0;
            $bill->transactionid = $transid;
            $bill->expectedpaiement = time() + DAYSECS * 15;
            $bill->paiedamount = 0;
            $bill->paymode = $paymode;
            $bill->ignoretax = $ignoreTax;
            if (!$bill->id = insert_record('courseshop_bill', $bill)){
                error("Could not record bill");
            } else {
            	courseshop_trace("[{$bill->transactionid}] Courseshop Transaction Start : $customer->lastname $customer->firstname ($bill->amount $bill->currency) ");
            }

            $i = 1;
            // create bill items depending on variables exist or not for product shortnames and calculate taxes
            $totalTaxes = 0;

            foreach(array_keys($shopproducts) as $anItem){
                // echo "{$order[$anItem]} {$anItem} ";

             // this inserts standalone products
                if ($order[$anItem] != 0 && $products[$shopproducts[$anItem]]->isset == 0){
                    $totalLinePrice = $order[$anItem] * $products[$shopproducts[$anItem]]->price1;
                    $itemrec = new StdClass;
                    $itemrec->billid = $bill->id;
                    $itemrec->ordering = $i;
                    $itemrec->type = 'BILLING';
                    $itemrec->itemcode = $products[$shopproducts[$anItem]]->code;
                    $itemrec->abstract = addslashes($products[$shopproducts[$anItem]]->name);
                    $itemrec->description = addslashes($products[$shopproducts[$anItem]]->description);
                    $itemrec->delay = 0;
                    $itemrec->unitcost = $products[$shopproducts[$anItem]]->price1;
                    $itemrec->quantity = $order[$anItem];
                    $itemrec->totalprice = $totalLinePrice;
                    $itemrec->taxcode = $products[$shopproducts[$anItem]]->taxcode;
                    $itemrec->bundleid = 0;
                    $result = insert_record('courseshop_billitem', $itemrec);

					$totalUntaxed += $totalLinePrice;
		            $totalEuros += $order[$anItem] * round($products[$shopproducts[$anItem]]->taxedprice, 2);
                    $totalTaxes += $order[$anItem] * $products[$shopproducts[$anItem]]->taxamount;

                    $i++;
                 }
                 // bundles must be exploded in indivual stock entries 
                 elseif ($order[$anItem] != 0 && $products[$shopproducts[$anItem]]->isset == 'B'){
                    // get product names that make the bundle
                    $bundleParts = get_records('courseshop_catalogitem', 'shortname', 'setid', $products[$shopproducts[$anItem]]->id, 'id, shortname');
                    
                    // insert a comment to introduce parts :
                    $bundleComment = get_string('elementsinbundle', 'blocks_courseshop') . " : " . $anItem . ' -' . count($bundleParts) . ' ' . get_string('iems', 'block_courseshop');
                    $billitem = new StdClass;
                    $billitem->billid = $bill->id;
                    $billitem->ordering = $i;
                    $billitem->type = 'COMMENT';
                    $billitem->itemcode = '';
                    $billitem->abstract = $bundleComment;
                    $billitem->description = '';
                    $billitem->delay = 0;
                    $billitem->unitcost = 0;
                    $billitem->quantity = 0;
                    $billitem->totalprice = 0;
                    $billitem->taxcode = '';
                    $billitem->bundleid = NULL;
                    $result = insert_record('courseshop_billitem', $billitem);
                    $i++;
                        
                    // insert parts within bill
                    foreach($bundleParts as $aPart){
                        $totalLinePrice = $order[$anItem] * $products[$shopproducts[$aPart]]->price1;
                        $billitem = new StdClass;
                        $billitem->billid = $bill->id;
                        $billitem->ordering = $i;
                        $billitem->type = 'BILLING';
                        $billitem->itemcode = $products[$shopproducts[$aPart]]->code;
                        $billitem->abstract = $products[$shopproducts[$aPart]]->name;
                        $billitem->description = $products[$shopproducts[$aPart]]->description;
                        $billitem->delay = 0;
                        $billitem->unitcost = $products[$shopproducts[$aPart]]->price1;
                        $billitem->quantity = $order[$anItem];
                        $billitem->totalprice = $totalLinePrice;
                        $billitem->taxcode = $products[$shopproducts[$aPart]]->taxcode;
                        $billitem->bundleid = $products[$shopproducts[$aPart]]->id;
                        $result = insert_record('courseshop_billitem', $billitem);

						$totalUntaxed += $totalLinePrice;
		            	$totalEuros += $anItem->quantity * round($products[$shopproducts[$aPart]]->taxedprice, 2);
                        $totalTaxes += $order[$anItem] * $products[$shopproducts[$aPart]]->taxamount;

                        $i++;
                    }
                    
                    // insert a blank comment to separate bundle :
                    $billitem = new StdClass;
                    $billitem->billid = $bill->id;
                    $billitem->ordering = $i;
                    $billitem->type = 'COMMENT';
                    $billitem->itemcode = '';
                    $billitem->abstract = '';
                    $billitem->description = '';
                    $billitem->delay = 0;
                    $billitem->unitcost = 0;
                    $billitem->quantity = 0;
                    $billitem->totalprice = 0;
                    $billitem->taxcode = 0;
                    $billitem->bundleid = NULL;  
                    $result = insert_record('courseshop_billitem', $billitem);
                    $i++;
                }
            }
      
            // updates bill with taxes
            $bill->transactionid = $transid;
            $bill->taxes = $totalTaxes;
            $bill->untaxedamount = $totalUntaxed;
            $bill->amount = $totalEuros;
            $result = update_record('bill', $bill);
    
            // inserting shipping as a product line
            if ((!empty($CFG->block_courseshop_useshipping) || $applyshipping) && (!empty($shipping) && $shipping->value > 0)){
            	// echo "adding shipping line ";
                $shippingLabel = get_string($shipping->code);
                $billitem = new StdClass;
                $billitem->billid = $bill->id;
                $billitem->ordering = $i;
                $billitem->type = 'BILLING';
                $billitem->itemcode = $shipping->code;
                $billitem->abstract = $shippingLabel;
                $billitem->description = '';
                $billitem->delay = 0;
                $billitem->unitcost = $shipping->value;
                $billitem->quantity = 1;
                $billitem->totalprice = $shipping->value;
                $billitem->taxcode = $shipping->taxcode;
                $billitem->bundleid = NULL;
                $result = insert_record('courseshop_billitem', $billitem);

				$totalUntaxed += $shipping->value;
            	$totalEuros += $shipping->taxedvalue;
                $totalTaxes += $billitem->totalprice - $billitem->unitcost;
                $i++;
            }
        
            // inserting discount as a product line
		    if (!empty($CFG->block_courseshop_discountthreshold) && $totalEuros > $CFG->block_courseshop_discountthreshold){
		       $bill->discounted = ($totalEuros * (1 - ($CFG->block_courseshop_discountrate / 100)));
		       $discount = $CFG->block_courseshop_discountrate / 100 * $totalEuros;
		    } else {
		       $bill->discounted = $totalEuros;
		       $discount = 0;
		    }

            if ($discount != 0){
                $billitem = new StdClass;
                $billitem->billid = $bill->id;
                $billitem->ordering = $i;
                $billitem->type = 'BILLING';
                $billitem->itemcode = 'DISCOUNT';
                $billitem->abstract = 'DISCOUNT';
                $billitem->description = '';
                $billitem->delay = 0;
                $billitem->unitcost = "-$discount";
                $billitem->quantity = 1;
                $billitem->totalprice = "-$discount";
                $billitem->taxcode = '';
                $billitem->bundleid = 0;
                $result = insert_record('courseshop_billitem', $billitem);
            }

			// finalize bills summators
	       	$bill->amount = $bill->discounted;
			set_field('courseshop_bill', 'amount', $bill->amount, 'id', $bill->id);
			set_field('courseshop_bill', 'untaxedamount', $totalUntaxed, 'id', $bill->id);
			set_field('courseshop_bill', 'taxes', $totalTaxes, 'id', $bill->id);
        }
   } else {
        $transactionFail = 1;
        $bill->id = $billid;
   }

/// all data come back from database

    $aFullBill = courseshop_get_full_bill($transid, $theBlock);    
    
// notify sales forces and administrator
    if ($cmd == 'order'){
       $billViewUrl = $CFG->wwwroot . "/blocks/courseshop/bills/view.php?id={$id}&pinned={$pinned}&view=viewBill&billid={$aFullBill->id}";
       $notification = compile_mail_template('transactionInput', array('SERVER' =>  $SITE->fullname,
                                                                    'SERVER_URL' =>  $CFG->wwwroot,
                                                                    'SELLER' => @$CFG->block_courseshop_sellername,
                                                                    'FIRSTNAME' =>  $aFullBill->customer->firstname,
                                                                    'LASTNAME' =>  $aFullBill->customer->lastname,
                                                                    'MAIL' =>  $aFullBill->customer->email,
                                                                    'CITY' =>  $aFullBill->customer->city,
                                                                    'COUNTRY' =>  $aFullBill->customer->country,
                                                                    'PAYMODE' =>  $aFullBill->paymode,
                                                                    'ITEMS' =>  count($aFullBill->itemcount),
                                                                    'AMOUNT' =>  $aFullBill->totalamount,
                                                                    'TAXES' =>  $aFullBill->totaltaxes,
                                                                    'TTC' =>  $aFullBill->totaltaxedamount
                                                                     ), "block_courseshop/paymodes/{$aFullBill->paymode}" );

        add_to_log(0, 'block_courseshop', 'orderinput', "blocks/courseshop/bills/view.php?view=viewBill&billid={$bill->id}&amp;id={$id}&amp;pinned={$pinned}", $aFullBill->customer->email, $theBlock->instance->id);

        if ($salesrole = get_record('role', 'shortname', 'sales')){
            // TODO : create custom role on block install
            $sender->firstname = $firstname;
            $sender->lastname = $lastname;
            $sender->email = $mail;
            $sender->emailstop = false;
            $sender->maildisplay = true;
            ticket_notifyrole($salesrole->id, $context, $sender, $SITE->shortname . ' : ' . get_string('orderinput', 'block_courseshop'), $notification, $notification, $billViewUrl); 
			courseshop_trace("[{$aFullBill->transactionid}] Courseshop Transaction Start Notification to sales");
        }
    }

?>