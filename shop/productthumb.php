<?php

/**
* This script opens the suitable One Unit Icon for the product if defined.
*
*
*/

include '../../../config.php';
require_once $CFG->libdir.'/filelib.php'; 

$code = optional_param('code', '', PARAM_TEXT);

$catalogitem = get_record('courseshop_catalogitem', 'code', $code);

if (!$catalogitem || empty($catalogitem->thumb)){
	$pathname = $CFG->dirroot.'/blocks/courseshop/pix/oneunit.gif';
	$filename = 'oneunit.gif';
} else {
	if (!empty($catalogitem->thumb)){
		if (preg_match('#^'.$CFG->wwwroot.'#', $catalogitem->thumb)){
			str_replace($CFG->wwwroot, $CFG->dataroot, $catalogitem->thumb);
		}
	}
}

send_file($pathname, $filename, 1440, false, false, false);
