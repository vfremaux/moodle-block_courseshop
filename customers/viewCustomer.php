<?php

    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    $customerid = required_param('customer', PARAM_INT);
    $order = optional_param('order', 'emissionDate', PARAM_TEXT);
    $dir = optional_param('dir', 'DESC', PARAM_ALPHA);
    $cmd = optional_param('cmd', PARAM_TEXT);

    if ($cmd != ''){
        include ($CFG->dirroot . '/blocks/courseshop/customers/viewCustomer.controller.php');
    }

    $customer = get_record('courseshop_customer', 'id', $customerid);
	$customerbills = array();
    $customerbills = get_records_select('courseshop_bill', " userid = '$customerid' ORDER BY status ASC, {$order} {$dir} ");
	
	if (is_array($customerbills)) {
		foreach($customerbills as $aBill) {
			if (!isset($bills[$aBill->status])) {
            $bills[$aBill->status] = array();
			$bills[$aBill->status][] = $aBill;
			}
		}
	} else {
		$bills = array();
	}
?>
<h1><?php print_string('customeraccount', 'block_courseshop') ?></h1>
<table width="100%">
    <tr>
        <td class="databox">
            <b><?php print_string('identification', 'block_courseshop') ?>:</b> <span class="data"><a href="mailto:<?php echo $customer->email ?>"><?php echo  $customer->email ?></a></span>
        </td>
        <td class="databox" align="left">
            <b><?php print_string('customer', 'block_courseshop') ?>:</b> <span class="data"><?php echo fullname($customer) ?><br/>
            <b><?php print_string('moodleaccount', 'block_courseshop') ?>:</b>
            	<?php
            	if ($customer->hasaccount){
            		$viewmoodleacocuntstr = get_string('viewmoodleaccount', 'block_courseshop');
					echo "<a href=\"{$CFG->wwwroot}/user/view.php?id={$customer->hasaccount}\">$viewmoodleacocuntstr</a>";            		
            	} else {
            		print_string('nomoodleaccount', 'block_courseshop');
            	}
            	?>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="address databox">
            <span class="city"><?php echo $customer->city ?> (<?php echo $customer->country ?>)</span>
        </td>
    </tr>
</table>

<table width="100%" class="generaltable">
<?php
if (count($bills) == 0) {
?>
<tr>
   <td colspan="5" class="customerRow">
   <?php echo get_string('nobillsinaccount', 'block_courseshop') ?>
   </td>
</tr>
<?php
} else {
?>
    <tr>
        <th class="header c0" align="left">
            <?php print_string('num', 'block_courseshop') ?>
        </th>
        <th class="header c1" align="left">
            <?php print_string('emissiondate', 'block_courseshop') ?>
        </th>
        <th class="header c2" align="left">
            <?php print_string('lastmove', 'block_courseshop') ?>
        </th>
        <th class="header c3" align="left">
            <?php print_string('title', 'block_courseshop') ?>
        </th>
        <th class="header c4" align="left">
            <?php print_string('amount', 'block_courseshop') ?>
        </th>
        <th class="header lastcol">
        &nbsp;
        </th>
    </tr>
<?php
    foreach (array_keys($bills) as $aStatus){
?>
    <tr>
        <td colspan="6" class="statusGroupHead" align="left">
            <h3><?php print_string('bill_' . $aStatus.'s', 'block_courseshop') ?><h3>
        </td>
<?php
        foreach ($bills[$aStatus] as $portlet){
            include($CFG->dirroot.'/blocks/courseshop/lib/accountLine.portlet.php');
        }
   }
}
?>
</table>
</form>
<br />
<br />
