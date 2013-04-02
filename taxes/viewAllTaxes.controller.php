<?php

// Security
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

//Delete a tax
if ($cmd == 'deletetax'){
	
	$taxid = required_param('taxid', PARAM_INT);
	$taxidlist = $taxid;
	delete_records_select('courseshop_tax', " id IN ('$taxidlist') ");
}
?>