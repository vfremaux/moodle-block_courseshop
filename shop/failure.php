<?php

    // Security

    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    require_once $CFG->dirroot.'/blocks/courseshop/mailtemplatelib.php';
    require_once $CFG->dirroot.'/blocks/courseshop/locallib.php';

    echo '<center>';

    print_box_start();
    echo $CFG->block_courseshop_sellername.' ';
    $courseshopurl = $CFG->wwwroot.'/blocks/courseshop/shop/view.php?view=shop&id='.$id.'&pinned='.$pinned;
    echo compile_mail_template('cancelMessage', array('URL' => $courseshopurl), 'block_courseshop');        
    print_box_end();

	echo '<br/>';
	echo '<br/>';