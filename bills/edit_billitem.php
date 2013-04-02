<?php

    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_billitem.class.php';
	include_once $CFG->dirroot.'/blocks/courseshop/forms/form_product.class.php';
	include_once $CFG->dirroot."/blocks/courseshop/classes/Catalog.class.php";

/// get the block reference and key context

	$id = required_param('id', PARAM_INT); // the blockid
	$pinned = optional_param('pinned', 0, PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);

/// Security

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);
    
    $billid = required_param('billid', PARAM_INT);
    $billitemid = optional_param('billitemid', 0, PARAM_INT);
    	
    $theCatalog = new Catalog($theBlock->config->catalogue);
    
    $context = get_context_instance(CONTEXT_BLOCK, $id);
    
    require_capability('block/courseshop:salesadmin', $context);

/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), '', $navigation);

    $bill = get_record('courseshop_bill', 'id', $billid);

    if ($billitemid){
        $billitem = get_record('courseshop_billitem', 'id', $billitemid);
        $mform = new BillItem_Form('edit', $theCatalog, $CFG->wwwroot."/blocks/courseshop/bills/edit_billitem.php?id={$id}&pinned={$pinned}&billid={$billid}");
    	$mform->set_data($billitem);
    } else {
        $mform = new BillItem_Form('new', $bill, $CFG->wwwroot."/blocks/courseshop/bills/edit_billitem.php?id={$id}&pinned={$pinned}&billid={$billid}&billitemid={$billitemid}");
    }

    if ($mform->is_cancelled()){
        redirect($CFG->wwwroot."/blocks/courseshop/bills/view.php?view=viewBill&amp;id={$id}&amp;pinned={$pinned}&amp;billid={$billid}");
    }

    if ($billitem = $mform->get_data()){
        
		$billitem->totalprice = $billitem->quantity * $billitem->unitcost;
		
		$bill->totalprice += ($billitem->unitcost * $billitem->quantity);
		
		$bill->untaxedamount += $bill->totalprice;
		
        $billitem->id = optional_param('billitemid', '', PARAM_INT);
		
		if ($bill->ignoretax == 0) {
			$tax = get_record('courseshop_tax', 'id', $billitem->taxcode);
			$bill->taxes += (($bill->totalprice*$tax->ratio)/100);
			}
		$bill->amount = $bill->amount + ($bill->untaxedamount + $bill->taxes);
        $billitem->billid = $billid;
		
		update_record('courseshop_bill', $bill);
			
		if (empty($billitem->id)){
            if (!$newid = insert_record('courseshop_billitem', $billitem)){
                error("could not record bill item");
            }        
        } else {
            if (!$newid = update_record('courseshop_billitem', $billitem)){
                error("could not update product");
            }
        }

        redirect($CFG->wwwroot."/blocks/courseshop/bills/view.php?view=viewBill&amp;id={$id}&amp;pinned={$pinned}&amp;billid={$billid}");
    } else {
        $mform->display();
    }

?>