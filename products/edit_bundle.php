<?php

    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot."/blocks/courseshop/classes/Catalog.class.php";
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_bundle.class.php';

/// get the block reference and key context

	$id = required_param('id', PARAM_INT); // the blockid
	$pinned = optional_param('pinned', 0, PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);
    
/// Security    

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);

    $catalogid = optional_param('catalogid', $theBlock->config->catalogue, PARAM_INT);
    $bundleid = optional_param('bundleid', 0, PARAM_INT);
    $theCatalog = new Catalog($catalogid);

/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), '', $navigation);


    if ($bundleid){
        $bundle = get_record('courseshop_catalogitem', 'id', $bundleid);
        $mform = new Bundle_Form('edit', $theCatalog, $CFG->wwwroot."/blocks/courseshop/products/edit_bundle.php?id={$id}&pinned={$pinned}&productid={$productid}");
    	$mform->set_data($bundle);
    } else {
        $mform = new Bundle_Form('new', $theCatalog, $CFG->wwwroot."/blocks/courseshop/products/edit_bundle.php?id={$id}&pinned={$pinned}");
    }

    if ($mform->is_cancelled()){
        redirect($CFG->wwwroot."/blocks/courseshop/products/view.php?view=viewAllProducts&amp;id={$id}&pinned={$pinned}&catalogid=".$catalogid);
    }

    if ($catalogitem = $mform->get_data()){
        
        $catalogitem->id = optional_param('bundleid', '', PARAM_INT);

        $catalogitem->catalogid = $theCatalog->id;
        $catalogitem->isset = PRODUCT_BUNDLE;

        if (empty($catalogitem->id)){
            if (!$newbundleid = insert_record('courseshop_catalogitem', $catalogitem)){
                error("could not record bundle");
            }
        
            // we have items in the set. update relevant products    
            $productsinbundle = optional_param('productsinset', array(), PARAM_INT);
            if (is_array($productsinbundle)){
                foreach($productsinbundle as $productid){
                    $record->id = $productid;
                    $record->setid = $newbundleid;
                    update_record('courseshop_catalogitem', $record);
                }
            }
        
            // if slave catalogue must insert a master copy
            if ($theCatalog->isslave){
                $catalogitem->catalogid = $theCatalog->groupid;        
                insert_record('courseshop_catalogitem', $catalogitem);
            }
        } else {
            if (!$newid = update_record('courseshop_catalogitem', $catalogitem)){
                error("could not update bundle");
            }
        }

        redirect($CFG->wwwroot."/blocks/courseshop/products/view.php?view=viewAllProducts&id={$id}&pinned={$pinned}&catalogid=".$catalogid);
    } else {
        $mform->display();
    }

?>