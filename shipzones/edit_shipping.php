<?php

    include "../../../config.php";
    include $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include $CFG->dirroot.'/blocks/courseshop/forms/form_shipping.class.php'; // imports of Form shipping

/// get the block reference and key context
	$cmd = optional_param('cmd', '', PARAM_TEXT);
    $id = required_param('id', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);

    if (isset($theBlock->config)){
        $catalogid = $theBlock->config->catalogue;
		include_once $CFG->dirroot.'/blocks/courseshop/classes/Catalog.class.php';
	
		$theCatalog = new Catalog($catalogid);
		if ($theCatalog->isslave){
		    $theCatalog = new Catalog($theCatalog->groupid);
		    $catalogid = $theCatalog->groupid;
		}
   } else {
        error("This block is not configured");
    }

/// Security

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);
    
	$zoneid = optional_param('zoneid', 0, PARAM_INT);
	$productcode = optional_param('productcode', '', PARAM_TEXT);
	$shippingid = optional_param('shippingid', 0, PARAM_INT);
      
/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), '', $navigation);

    if ($shippingid){
        $shipping = get_record('courseshop_catalogshipping', 'id', $shippingid);
        $mform = new ProductShipping_Form('edit', $catalogid, $zoneid, $productcode, $CFG->wwwroot."/blocks/courseshop/shipzones/edit_shipping.php?id={$id}&pinned={$pinned}"); 
    	$mform->set_data($shipping);
    } else {
        $mform = new ProductShipping_Form('new', $catalogid, $zoneid, $productcode, $CFG->wwwroot."/blocks/courseshop/shipzones/edit_shipping.php?id={$id}&pinned={$pinned}");
	}
	
	if ($mform->is_cancelled()){
         redirect($CFG->wwwroot."/blocks/courseshop/shipzones/zoneindex.php?id={$id}&amp;pinned={$pinned}&amp;zoneid={$zoneid}");
	}

    if ($data = $mform->get_data()){
        $shipping->id = optional_param('shippingid', 0, PARAM_INT);
        $shipping->productcode = addslashes(required_param('productcode', PARAM_TEXT));
        $shipping->zoneid = required_param('zoneid', PARAM_INT);
        $shipping->value = optional_param('value', '', PARAM_NUMBER);
        $shipping->formula = addslashes(optional_param('formula', '', PARAM_TEXT));
        $shipping->a = addslashes(optional_param('a', '', PARAM_NUMBER));
        $shipping->b = addslashes(optional_param('b', '', PARAM_NUMBER));
        $shipping->c = addslashes(optional_param('c', '', PARAM_NUMBER));
    
    	// print_object($shipping);
        if (empty($shipping->id)){
            $shipping->id = insert_record('courseshop_catalogshipping', $shipping);
        } else {
            update_record('courseshop_catalogshipping', $shipping);
        }
         redirect($CFG->wwwroot."/blocks/courseshop/shipzones/zoneindex.php?id={$id}&amp;pinned={$pinned}&amp;zoneid={$zoneid}");
    } else {
        $mform->display();
    }
    
	print_footer();

?>