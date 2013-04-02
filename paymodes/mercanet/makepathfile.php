<?php

include '../../../../config.php';

require_capability('moodle/site:doanything', get_context_instance(CONTEXT_SYSTEM));

require_once $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/mercanet.class.php';

print_header_simple();

if (empty($CFG->block_courseshop_mercanet_processor_type)){
	set_config('block_courseshop_mercanet_processor_type', 32);
}

$data->proctype = (!empty($CFG->block_courseshop_mercanet_processor_type)) ? $CFG->block_courseshop_mercanet_processor_type : '32' ;
$data->os = $CFG->os;

print_heading(get_string('generatingpathfile', 'block_courseshop', $data, $CFG->dirroot.'/blocks/courseshop/paymodes/mercanet/lang/'));

$blockinstance = null;
$payhandler = new courseshop_paymode_mercanet($blockinstance);
$payhandler->generate_pathfile();

print_footer();
?>