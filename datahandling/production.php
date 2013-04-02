<?php

    /**
    * we have to organize a per product handler.
    * products dedicated handles are in the "handlers" directory.
    *
    */

    /*
    * production handler that is called after order is confirmed but before paiement 
    * is complete
    */
    
	define('NO_HANDLER', 0);
	define('EMPTY_HANDLER', '');
	define('SPECIFIC_HANDLER', 1);
    
    function produce_prepay(&$data){
        global $CFG;
                
        $response->public = '';
        $response->private = '';
        $response->salesadmin = '';

        foreach($data->items as $anItem){
            if (debugging() && $CFG->block_courseshop_test) echo "preproducing for $anItem->itemcode <br/>";
            $enablehandler = get_field('courseshop_catalogitem', 'enablehandler', 'code', $anItem->itemcode);
            $handlerlabel = get_field('courseshop_catalogitem', 'shortname', 'code', $anItem->itemcode);
            if(empty($enablehandler)) {
            	if (debugging() && $CFG->block_courseshop_test) echo "...handler disabled<br/>";
            	continue;
            } elseif ($enablehandler == SPECIFIC_HANDLER){
            	$thehandler = $anItem->itemcode;
            } else {
            	$thehandler = $enablehandler;
            	$data->params = courseshop_decode_params($anItem->itemcode);
            }

            if (!empty($thehandler) && is_readable($CFG->dirroot.'/blocks/courseshop/datahandling/handlers/'.$thehandler.'.class.php')){
                include_once($CFG->dirroot.'/blocks/courseshop/datahandling/handlers/'.$thehandler.'.class.php');
                $classtype = "shop_handler_{$thehandler}";
                $handler = new $classtype($handlerlabel);
                // we feed data with required information got from order form and store this info in the customer's relevant billitem
                if (method_exists($handler, 'process_required')){
                	$handler->process_required($data);
                	$anItem->customerdata = base64_encode(json_encode($data->required));
                	update_record('courseshop_billitem', addslashes_object($anItem));
                }
                if (method_exists($handler, 'produce_prepay')){
                    if ($itemresponse = $handler->produce_prepay($data)){
				        $response->public .= "<br/>\n".$itemresponse->public;
				        $response->private .= "<br/>\n".$itemresponse->private;
				        $response->salesadmin .= "<br/>\n".$itemresponse->salesadmin;
                    }
                }
            }
        }
        
        return $response;               
    }

    /*
    * production handler that is called after paiement is complete
    */
    function produce_postpay(&$data){
        global $CFG;
        
        $hasworked = false;

        $response->public = '';
        $response->private = '';
        $response->salesadmin = '';

        foreach($data->items as $anItem){
            if (debugging() && $CFG->block_courseshop_test) echo "postproducing for $anItem->itemcode <br/>";
            $enablehandler = get_field('courseshop_catalogitem', 'enablehandler', 'code', $anItem->itemcode);
            $handlerlabel = get_field('courseshop_catalogitem', 'shortname', 'code', $anItem->itemcode);
            if(empty($enablehandler)) {
            	if (debugging()) echo "...handler disabled<br/>";
            	continue;
            } elseif ($enablehandler == SPECIFIC_HANDLER){
            	$thehandler = $anItem->itemcode;
            } else {
            	$thehandler = $enablehandler;
            	$data->actionparams = courseshop_decode_params($anItem->itemcode);
            }

        	$data->required = (array)json_decode(base64_decode($anItem->customerdata));
            
            if (!empty($thehandler) && is_readable($CFG->dirroot.'/blocks/courseshop/datahandling/handlers/'.$thehandler.'.class.php')){
                include_once($CFG->dirroot.'/blocks/courseshop/datahandling/handlers/'.$thehandler.'.class.php');
                $classtype = "shop_handler_{$thehandler}";
                $handler = new $classtype($handlerlabel);
                if (method_exists($handler, 'produce_postpay')){
                    if ($itemresponse = $handler->produce_postpay($data)){
        				$hasworked = true;
				        $response->public .= "<br/>\n".$itemresponse->public;
				        $response->private .= "<br/>\n".$itemresponse->private;
				        $response->salesadmin .= "<br/>\n".$itemresponse->salesadmin;
                    }
                }
            } else {
		        $response->public .= get_string('defaultpublicmessagepostpay', 'courseshop', $anItem);
		        $response->private .= get_string('defaultprivatemessagepostpay', 'courseshop', $anItem);
		        $response->salesadmin .= get_string('defaultsalesadminmessagepostpay', 'courseshop', $anItem);
            }
        }        

		// set the final COMPLETE status if has worked
		if ($hasworked){
			set_field('courseshop_bill', 'status', 'COMPLETE', 'id', $data->id);    
		}

       return $response;
    }

	/**
	* Aggregates production data to the existing stored production data.
	* @param objectref $abill
	* @param object $productiondata a composite object with strings generated by handlers for each user class
	* @param boolean $interactive if true, feeds back production data information in the full bill so following
	* code can print the full updated production track.
	*/
	function courseshop_aggregate_production(&$abill, $productiondata, $interactive = false){
	    $billrec->id = $abill->id;
	    $previousdata = (empty($aFullBill->productiondata)) ? '' : json_decode(base64_decode($aFullBill->productiondata));
	    $previousdata->public .= $productiondata->public;
	    $previousdata->private .= $productiondata->private;
	    $previousdata->salesadmin .= $productiondata->salesadmin;
	    $billrec->productiondata = base64_encode(json_encode($previousdata));
	    update_record('courseshop_bill', $billrec);
	    
	    // if interactive, we need all productiondata accumulated to sync the recorded information so we can print it out to
	    // actual user on transaction feedback.
	    if ($interactive) $abill->productiondata = $previousdata;
	}
?>