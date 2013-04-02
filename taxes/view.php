<?php

    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';

/// get the block reference and key context

    $id = required_param('id', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
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
    
    $catalogid = (!empty($theBlock->config->catalogue)) ? $theBlock->config->catalogue : $defaultcatalogue ;
    include $CFG->dirroot."/blocks/courseshop/classes/Catalog.class.php";
    $theCatalog = new Catalog($catalogid);

/// get other contextual params

    $view = optional_param('view', 'viewAllCustomers', PARAM_TEXT);
    
/// make page header and navigation    
    
    $navlinks = array(
            array('name' => get_string('salesservice', 'block_courseshop'),
                  'link' => $CFG->wwwroot."/blocks/courseshop/index.php?id={$id}&pinned={$pinned}",
                  'type' => 'link'),
            array('name' => get_string('bills', 'block_courseshop'),
                  'link' => $CFG->wwwroot."/blocks/courseshop/customers/view.php?id={$id}&pinned={$pinned}&view=viewAllCustomers",
                  'type' => 'link'),
    );
    
    if ($view == "viewCustomer"){
        $navlinks[] = array('name' => get_string('customer', 'block_courseshop'),
                  'link' => '',
                  'type' => 'title');
    }
    
    $navigation = build_navigation($navlinks);
    
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), $navigation, '', '', true);

/// make page content
    
    include $CFG->dirroot."/blocks/courseshop/taxes/$view.php";

/// make footer
    
    print_footer();
?>

