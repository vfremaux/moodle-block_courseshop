<?php

    include "../../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_category.class.php';
    include_once $CFG->dirroot."/blocks/courseshop/classes/Catalog.class.php";

/// get the block reference and key context

    $id = required_param('id', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);

/// Security    

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);

    $catalogid = required_param('catalogid', PARAM_INT);
		
    $categoryid = optional_param('categoryid', 0, PARAM_INT);
    $theCatalog = new Catalog($catalogid);


/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), '', $navigation);


    if ($categoryid){
        $category = get_record('courseshop_catalogcategory', 'id', $categoryid);
        $mform = new Category_Form('edit', $CFG->wwwroot."/blocks/courseshop/products/category/edit_category.php?id={$id}&pinned={$pinned}&catalogid={$catalogid}}&categoryid={$categoryid}");
    	$mform->set_data($category);
    } else {
        $mform = new Category_Form('new', $CFG->wwwroot."/blocks/courseshop/products/category/edit_category.php?id={$id}&pinned={$pinned}&catalogid={$catalogid}");
    }

    if ($mform->is_cancelled()){
        redirect($CFG->wwwroot."/blocks/courseshop/products/view.php?view=viewAllProducts&amp;id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}");
    }

    if ($catalogcategory = $mform->get_data()){
        
        $catalogcategory->id = optional_param('categoryid', '', PARAM_INT);

        $catalogcategory->catalogid = $theCatalog->id;

        if (empty($catalogcategory->id)){
            if (!$newid = insert_record('courseshop_catalogcategory', $catalogcategory)){
                error("could not record category");
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
                $catalogcategory->catalogid = $theCatalog->groupid;        
                insert_record('courseshop_catalogcategory', $catalogcategory);
            }
        } else {
            if (!$newid = update_record('courseshop_catalogcategory', $catalogcategory)){
                error("could not update product");
            }
        }

        redirect($CFG->wwwroot."/blocks/courseshop/products/category/view.php?view=viewAllCategory&amp;id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}");
    } else {
        $mform->display();
    }

?>