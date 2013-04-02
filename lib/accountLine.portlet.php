<tr>
    <td width="30">
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/bills/view.php?view=viewBill&billid={$portlet->id}&pinned=$pinned" ?>"><?php echo $portlet->idnumber ?></a>
    </td>
    <td width="120">
        <?php echo userdate($portlet->emissiondate) ?>
    </td>
    <td width="120">
        <?php echo $portlet->lastactiondate ?>
    </td>
    <td width="*">
        <?php echo $portlet->title ?>
    </td>
    <td align="right">
        <b><?php echo sprintf("%.2f", round($portlet->amount, 2)) ?> <?php echo $CFG->block_courseshop_defaultcurrency ?></b>
    </td>
    <td align="right">
<?php
if ($portlet->status == 'PENDING'){
?>
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/view.php?view=viewCustomer&cmd=sellout&billid={$portlet->id}&customer={$portlet->userid}&pinned=$pinned" ?>" alt="<?php print_string('mark', 'block_courseshop') ?>"><img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/mark.gif' ?>" border="0"></a>
<?php
} elseif ($portlet->status == 'SOLDOUT'){
?>
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/view.php?view=viewCustomer&cmd=unmark&billid={$portlet->id}&customer={$portlet->userid}&pinned=$pinned" ?>" alt="<?php print_string('unmark', 'block_courseshop') ?>"><img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/unmark.gif' ?>" border="0"></a>
<?php
}
?>
    </td>
</tr>
