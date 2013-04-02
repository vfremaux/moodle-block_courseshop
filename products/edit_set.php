<?php

    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_set.class.php';
    include_once $CFG->dirroot."/blocks/courseshop/classes/Catalog.class.php";

/// get the block reference and key context

    $id = required_param('id', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);

/// Security    

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);
    
    $catalogid = optional_param('catalogid', $theBlock->config->catalogue, PARAM_INT);
    $setid = optional_param('setid', 0, PARAM_INT);
    $theCatalog = new Catalog($catalogid);

/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), '', $navigation);


    if ($setid){
        $set = get_record('courseshop_catalogitem', 'id', $productid);
        $mform = new Set_Form('edit', $theCatalog, $CFG->wwwroot."/blocks/courseshop/products/edit_set.php?id={$id}&pinned={$pinned}&setid={$setid}");
    	$mform->set_data($set);
    } else {
        $mform = new Set_Form($setid, 'new', $theCatalog, $CFG->wwwroot."/blocks/courseshop/products/edit_set.php?id={$id}&pinned={$pinned}");
    }

    if ($mform->is_cancelled()){
        redirect($CFG->wwwroot."/blocks/courseshop/products/view.php?view=viewAllProducts&id={$id}&pinned={$pinned}&catalogid=".$catalogid);
    }

    if ($catalogitem = $mform->get_data()){
        
        $catalogitem->id = optional_param('setid', '', PARAM_INT);

        $catalogitem->catalogid = $theCatalog->id;
        $catalogitem->isset = PRODUCT_SET;

        if (empty($catalogitem->id)){
            if (!$newid = insert_record('courseshop_catalogitem', $catalogitem)){
                error("could not record set");
            }

            // we have items in the set. update relevant products    
            $productsinset = optional_param('productsinset', array(), PARAM_INT);
            if (is_array($productsinset)){
                foreach($productsinset as $productid){
                    $record->id = $productid;
                    $record->setid = $newsetid;
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
                error("could not update set");
            }
        }

        redirect($CFG->wwwroot."/blocks/courseshop/products/view.php?view=viewAllProducts&id={$id}&pinned={$pinned}&catalogid=".$catalogid);
    } else {
        $mform->display();
    }

?>