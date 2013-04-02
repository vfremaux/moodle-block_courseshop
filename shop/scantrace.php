<?php

	/**
	* This script is a simple tool for salesadmins for 
	* extracting and inspecting a transaton backtrace.
	* It is provided for problem or claim solving.
	*
	*/

	include '../../../config.php';

    $transid = optional_param('transid', '', PARAM_TEXT);

    $id = required_param('id', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
    if (!$instance = get_record($blocktable, 'id', $id)){
        error('Invalid block');
    }
    $theBlock = block_instance('courseshop', $instance);
	$context = get_context_instance(CONTEXT_BLOCK, $theBlock->instance->id);

	require_capability('block/courseshop:salesadmin', $context);
	
	print_header_simple($SITE->fullname, $SITE->fullname, '');

	$scanstr = get_string('scantrace', 'block_courseshop');
	
	$transids = get_records('courseshop_bill', '', '', 'id', 'transactionid, amount');
	
	print_box_start();
	echo "<form name=\"transidform\" method=\"POST\" >";
	print_string('pastetransactionid', 'block_courseshop');
	echo "<input type=\"text\" name=\"transid\" size=\"40\" />";
	echo "<input type=\"submit\" name=\"g_btn\" value=\"$scanstr\" />";
	echo '</form>';
	echo get_string('or', 'block_courseshop');
	echo "<form name=\"transidform\" method=\"POST\" >";
	print_string('picktransactionid', 'block_courseshop');
	echo "<select name=\"transid\" />";
	foreach($transids as $trans){
		echo "<option value=\"{$trans->transactionid}\" >$trans->transactionid   ($trans->amount)</option>";
	}
	echo '</select>';
	echo "<input type=\"submit\" name=\"g_btn\" value=\"$scanstr\" />";
	echo '</form>';
	print_box_end();

	if ($transid) {
		$tracecontent = file($CFG->dataroot.'/merchant_trace.log');
		$trace = preg_grep("/\\[$transid\\]/", $tracecontent);

		if ($trace) {
			echo '<pre>';
			foreach($trace as $tr){
				echo $tr;
			}
			echo '</pre>';
		} else {
			print_string('notrace', 'block_courseshop');
		}
	}
	
	echo '<br/>';
	
	echo '<center>';
	$options['id'] = $id;
	$options['pinned'] = $pinned;
	print_single_button($CFG->wwwroot.'/blocks/courseshop/index.php', $options, get_string('backtoshopadmin', 'block_courseshop'));
	$options['view'] = 'shop';
	print_single_button($CFG->wwwroot.'/blocks/courseshop/shop/view.php', $options, get_string('backtoshop', 'block_courseshop'));
	echo '</center>';

	print_footer();

?>