<?php

// Security
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

//Delete a category
if ($cmd == 'deletecategory'){
	
	$categoryid = required_param('categoryid', PARAM_INT);
	$categoryidlist = $categoryid;
	delete_records_select('courseshop_catalogcategory', " id IN ('$categoryidlist') ");
}
/******************************* Raises a question in the list ****************/
else if ($cmd == 'up'){
    $cid = required_param('categoryid', PARAM_INT);

    courseshop_list_up($courseshop, $cid, 'courseshop_catalogcategory');
}
/******************************* Lowers a question in the list ****************/
else if ($cmd == 'down'){
    $cid = required_param('categoryid', PARAM_INT);

    courseshop_list_down($courseshop, $cid, 'courseshop_catalogcategory');
}
?>