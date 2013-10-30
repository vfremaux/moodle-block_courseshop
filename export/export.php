<?php

	include '../../../config.php';
	include $CFG->dirroot.'/blocks/courseshop/locallib.php';

	$id = required_param('id', PARAM_INT); // the blockid
	$pinned = optional_param('pinned', 0, PARAM_INT);
    $theBlock = courseshop_get_block_instance($id, $pinned);
    $blockcontext = get_context_instance(CONTEXT_BLOCK, $id);

    // security
	require_capability('block/courseshop:salesadmin', $blockcontext);

	$what = required_param('what', PARAM_TEXT);	
	$format = required_param('format', PARAM_TEXT);	

	if (file_exists($CFG->dirroot.'/blocks/courseshop/export/extractors/export_'.$what.'.php')){
		require_once($CFG->dirroot.'/blocks/courseshop/export/extractors/export_'.$what.'.php');
	} else {
		error('No data source for export');
	}

	if (file_exists($CFG->dirroot.'/blocks/courseshop/export/formats/export_'.$format.'.php')){
		require_once($CFG->dirroot.'/blocks/courseshop/export/formats/export_'.$format.'.php');
	} else {
		error('No format renderer for export');
	}

	$extractorclass = "courseshop_export_source_$what";
	$extractor = new $extractorclass();
	
	$datadesc = $extractor->get_data_description($theBlock);
	$data = $extractor->get_data($theBlock);

	$rendererclass = "courseshop_export_$format";
	$renderer = new $rendererclass($data, $datadesc, array('addtimestamp' => 1));
	
	$renderer->open_export();
	$renderer->render();
	$renderer->close_export();
	
	