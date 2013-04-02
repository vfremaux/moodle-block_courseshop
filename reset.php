<?php

    include "../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_reset.php';

    $cmd = optional_param('cmd', '', PARAM_TEXT);
    $id = required_param('id', PARAM_INT); // the block ID
    $pinned = required_param('pinned', PARAM_INT); // the block ID
    
    $navlinks[] = array('name' => get_string('salesservice', 'block_courseshop'), 'url' => $CFG->wwwroot."/blocks/courseshop/index.php?id={$id}&pinned={$pinned}" , 'type' => 'link');
    $navlinks[] = array('name' => get_string('reset', 'block_courseshop'), 'url' => '' , 'type' => 'title');

    $navigation = build_navigation($navlinks);

    print_header('courseshop', 'courseshop', $navigation, '', '', '');
    
/// Security

    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
    if (!$instance = get_record($blocktable, 'id', $id)){
        error('Invalid block');
    }
    $theBlock = block_instance('courseshop', $instance);
	$context = get_context_instance(CONTEXT_BLOCK, $theBlock->instance->id);
    require_capability('block/courseshop:salesadmin', $context);

	print_heading(get_string('reset', 'block_courseshop'), 2);
	
	print_box_start();
	print_string('resetguide', 'block_courseshop');	

	$mform = new ResetForm($id, $pinned);
	
	if ($mform->is_cancelled()){
		redirect($CFG->wwwroot."/blocks/courseshop/index.php?id={$id}&amp;pinned={$pinned}");
	} elseif ($data = $mform->get_data()){
		if (!empty($data->bills) || !empty($data->customers) || !empty($data->catalogs)){
			notify(get_string('billsdeleted', 'block_courseshop'));
			delete_records('courseshop_bill', '', '');
			delete_records('courseshop_billitem', '', '');
		}
		if (!empty($data->customers)){
			notify(get_string('customersdeleted', 'block_courseshop'));
			delete_records('courseshop_customer', '', '');
		}
		if (!empty($data->catalogs)){
			notify(get_string('catalogsdeleted', 'block_courseshop'));
			delete_records('courseshop_catalogitem', 'catalogid', $theBlock->config->catalogid);
			delete_records('courseshop_catalog', 'id', $theBlock->config->catalogid);
		}
	}
	$mform->display();
	
	print_box_end();

	print_footer();