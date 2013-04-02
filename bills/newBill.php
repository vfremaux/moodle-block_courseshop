<?php
   
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_billitem.class.php'; //form import

/// get the block reference and key context

/// get the block reference and key context

	$id = required_param('id', PARAM_INT); // the blockid
	$pinned = optional_param('pinned', 0, PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);

/// Security

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);
    
	$billid = optional_param('billid', 0, PARAM_INT);
    
    $context = get_context_instance(CONTEXT_BLOCK, $id);
    
    require_capability('block/courseshop:salesadmin', $context);

/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    $bill = get_record('courseshop_bill', 'id', $billid);
    if ($billid){
        $bill = get_record('courseshop_bill', 'id', $billid);
		$mform = new billitem_Form('edit', $bill); 
    	$mform->set_data($bill);
    } else {
        $mform = new billitem_Form('new', $bill, $CFG->wwwroot."/blocks/courseshop/bills/view.php?id=$id&view=viewallBills");
	}
///////////////////////////!!!!!!!!!!!!!!!!!!!!!!!/////////////////////////
    if ($data = $mform->get_data()){
        $bill->id = optional_param('billid', 0, PARAM_INT);
        $bill->billtitle = addslashes(required_param('billtitle', PARAM_TEXT));
        $bill->orderingstatic = optional_param('orderingstatic', PARAM_TEXT);
        $bill->ordering = required_param('ordering', PARAM_TEXT);
		$bill->itemcode = required_param('item_code', PARAM_TEXT);
		$bill->abstract = required_param('abstract', PARAM_TEXT);
		$bill->description = required_param('description', PARAM_CLEANHTML);
		$bill->itemcode = required_param('item_code', PARAM_TEXT);
		$bill->timetodo = required_param('timetodo', PARAM_TEXT);
		$bill->unitcost = required_param('unitcost', PARAM_TEXT);
		$bill->quantity = required_param('quantity', PARAM_TEXT);
		$bill->taxcode = required_param('taxcode', PARAM_INT);
		  
        if (empty($bill->id)){
            $newid = insert_record('courseshop_bill', $bill);
        } else {
            $updateid = update_record('courseshop_bill', $bill);
        }
         redirect($CFG->wwwroot."/blocks/courseshop/bills/view.php?view=viewAllBills&id=$id");
    } else {
        $mform->display();
    }


?>