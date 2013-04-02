<?php

	/**
	*
	*/

    include '../../../config.php';
    include_once 'lib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
	
    $view = optional_param('view', 'shop', PARAM_ALPHA);
    
/// get block information

    $id = required_param('id', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
    if (!$instance = get_record($blocktable, 'id', $id)){
        error('Invalid block');
    }
    $theBlock = block_instance('courseshop', $instance);
	$context = get_context_instance(CONTEXT_BLOCK, $theBlock->instance->id);
	
	if (empty($theBlock->config->catalogue)) {
		error('Bad block configuration');
	}
	
    // Security 
    // Service is available to unconnected people.

    // get active catalog from block 

    if (isset($theBlock->config)){
        $catalogid = $theBlock->config->catalogue;
    } else {
        error("This block is not configured");
    }
	
/// make page header

    $navlinks = array(array('name' => get_string('shop', 'block_courseshop'), 'url' => '', 'type' => 'title'));
    $navigation = build_navigation($navlinks);
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), $navigation, '', '', false);

/// Fetch view
    
    if (empty($CFG->block_courseshop_sellername)){
        error("Shop parameters have not been defined.");
    }
    
    if (is_readable($CFG->dirroot."/blocks/courseshop/shop/{$view}.php")){
        include($CFG->dirroot."/blocks/courseshop/shop/{$view}.php");
    } else {
         error("Bad view");
    }

    print_footer();
?>