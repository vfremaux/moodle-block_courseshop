<?php

    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_product.class.php';
    include_once $CFG->dirroot."/blocks/courseshop/classes/Catalog.class.php";

/// get the block reference and key context

    $id = required_param('id', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);
    
/// Security    

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);

    // $catalogid = $theBlock->config->catalogue;
    $catalogid = optional_param('catalogid', $theBlock->config->catalogue, PARAM_INT);
    $productid = optional_param('productid', 0, PARAM_INT);
    $theCatalog = new Catalog($catalogid);

/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), '', $navigation);


    if ($productid){
        $product = get_record('courseshop_catalogitem', 'id', $productid);
        $mform = new Product_Form('edit', $theCatalog, $id, $pinned, $CFG->wwwroot."/blocks/courseshop/products/edit_product.php?id={$id}&pinned={$pinned}&productid={$productid}");
    	$mform->set_data($product);
    } else {
        $mform = new Product_Form('new', $theCatalog, $id, $pinned, $CFG->wwwroot."/blocks/courseshop/products/edit_product.php?id={$id}&pinned={$pinned}");
    }

    if ($mform->is_cancelled()){
        redirect($CFG->wwwroot."/blocks/courseshop/products/view.php?view=viewAllProducts&id={$id}&pinned={$pinned}&catalogid=".$catalogid);
    }

    if ($catalogitem = $mform->get_data()){
        
        $catalogitem->id = optional_param('productid', '', PARAM_INT);
		$catalogitem->enablehandler = optional_param('enablehandler', 0, PARAM_TEXT);

        $catalogitem->catalogid = $theCatalog->id;

        if (empty($catalogitem->id)){
            if (!$newid = insert_record('courseshop_catalogitem', $catalogitem)){
                error("could not record product");
            }
        
            // we have items in the set. update relevant products    
            $productsinset = optional_param('productsinset', array(), PARAM_INT);
            if (is_array($productsinset)){
                foreach($productsinset as $productid){
                    $record->id = $productid;
                    $record->setid = $newid;
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
                error("could not update product");
            }
        }
		
        redirect($CFG->wwwroot."/blocks/courseshop/products/view.php?view=viewAllProducts&id={$id}&pinned={$pinned}&catalogid=".$catalogid);
    } else {
        $mform->display();
    }

?>