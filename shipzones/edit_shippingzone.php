<?php

    include "../../../config.php";
    include $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include $CFG->dirroot.'/blocks/courseshop/forms/form_shippingzone.class.php'; // imports of Form shipping

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
    
	$zoneid = optional_param('item', 0, PARAM_INT);
      
/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), '', $navigation);


    if ($zoneid){
        $zone = get_record('courseshop_catalogshipzone', 'id', $zoneid);
        $mform = new ShippingZone_Form('edit', $CFG->wwwroot."/blocks/courseshop/shipzones/edit_shippingzone.php?id={$id}&pinned={$pinned}&zoneid={$zoneid}"); 
    	$mform->set_data($zone);
    } else {
        $mform = new ShippingZone_Form('new', $CFG->wwwroot."/blocks/courseshop/shipzones/edit_shippingzone.php?id={$id}&pinned={$pinned}");
	}
	
	if ($mform->is_cancelled()){
         redirect($CFG->wwwroot."/blocks/courseshop/shipzones/index.php?id={$id}&amp;pinned={$pinned}");
	}

    if ($data = $mform->get_data()){
        $zone->id = optional_param('zoneid', 0, PARAM_INT);
        $zone->catalogid = $catalogid;
        $zone->zonecode = addslashes(required_param('zonecode', PARAM_TEXT));
        $zone->description = required_param('description', PARAM_CLEANHTML);
        $zone->billscopeamount = required_param('billscopeamount', PARAM_NUMBER);
        $zone->taxid = required_param('taxid', PARAM_INT);
        $zone->applicability = addslashes(required_param('applicability', PARAM_TEXT));
    
        if (empty($zone->id)){
            $zone->id = insert_record('courseshop_catalogshipzone', $zone);
        } else {
            update_record('courseshop_catalogshipzone', $zone);
        }
         redirect($CFG->wwwroot."/blocks/courseshop/shipzones/index.php?id={$id}&amp;pinned={$pinned}");
    } else {
        $mform->display();
    }

	print_footer();

?>