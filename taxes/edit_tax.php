<?php

    include "../../../config.php";
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';
    include_once $CFG->dirroot.'/blocks/courseshop/forms/form_tax.class.php'; //imports of Form tax

/// get the block reference and key context

    $id = required_param('id', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);

/// Security

    $context = get_context_instance(CONTEXT_BLOCK, $id);
    require_capability('block/courseshop:salesadmin', $context);
    
	$taxid = optional_param('taxid', 0, PARAM_INT);

/// make page header and navigation

    $navlinks = array();
    $navigation = build_navigation('');
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), '', $navigation);


    if ($taxid){
        $tax = get_record('courseshop_tax', 'id', $taxid);
        $mform = new Tax_Form('edit', $CFG->wwwroot."/blocks/courseshop/taxes/edit_tax.php?id={$id}&pinned={$pinned}&taxid={$taxid}"); 
    	$mform->set_data($tax);
    } else {
        $mform = new Tax_Form('new', $CFG->wwwroot."/blocks/courseshop/taxes/edit_tax.php?id={$id}&pinned={$pinned}");
	}

	if ($mform->is_cancelled()){
         redirect($CFG->wwwroot."/blocks/courseshop/taxes/view.php?view=viewAllTaxes&amp;id={$id}&amp;pinned={$pinned}");
	}
    if ($data = $mform->get_data()){
        $tax->id = optional_param('taxid', 0, PARAM_INT);
        $tax->title = addslashes(required_param('title', PARAM_TEXT));
        $tax->country = optional_param('country', PARAM_TEXT);
        $tax->ratio = required_param('ratio', PARAM_CLEANHTML);
		$tax->formula = required_param('formula', PARAM_TEXT);
    
        if (empty($tax->id)){
            $newid = insert_record('courseshop_tax', $tax);
        } else {
            $updateid = update_record('courseshop_tax', $tax);
        }
         redirect($CFG->wwwroot."/blocks/courseshop/taxes/view.php?id={$id}&amp;pinned={$pinned}&amp;view=viewAllTaxes");
    } else {
        $mform->display();
    }


?>