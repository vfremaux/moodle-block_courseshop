<?php

// Security
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

//Delete a category
if ($cmd == 'deletecategory'){
	
	$categoryid = required_param('categoryid', PARAM_INT);
	$categoryidlist = $categoryid;
	delete_records_select('courseshop_catalogcategory', " id IN ('$categoryidlist') ");
}
?>