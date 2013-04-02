<?php

    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';

/// get the block reference and key context

	$id = required_param('id', PARAM_INT); // the blockid
	$pinned = optional_param('pinned', 0, PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);
    
/// security

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);
     
    if (count_records('courseshop_catalog') == 1){
        $cats = get_records('courseshop_catalog');
        $catids = array_keys($cats);
        $defaultcatalogue = $catids[0];
        $theBlock->config->catalogue = $defaultcatalogue;
        $theBlock->instance_config_save($theBlock->config);
    } 
    
    if (!$catalogid = optional_param('catalogid', null, PARAM_INT)){
	    $catalogid = (!empty($theBlock->config->catalogue)) ? $theBlock->config->catalogue : $defaultcatalogue ;
	}
    include $CFG->dirroot."/blocks/courseshop/classes/Catalog.class.php";
    $theCatalog = new Catalog($catalogid);

/// get other contextual params

    $view = optional_param('view', '', PARAM_TEXT);
    
/// make page header and navigation    
    
    $navlinks = array(
            array('name' => get_string('salesservice', 'block_courseshop'),
                  'link' => $CFG->wwwroot."/blocks/courseshop/index.php?id={$id}&pinned={$pinned}",
                  'type' => 'url'),
            array('name' => get_string('catalog', 'block_courseshop'),
                  'link' => '',
                  'type' => 'title'),
    );
    
    $navigation = build_navigation($navlinks);
    
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), $navigation, '', '', true);

/// make page content
    
    include $CFG->dirroot."/blocks/courseshop/products/$view.php";

/// make footer
    
    print_footer();
?>