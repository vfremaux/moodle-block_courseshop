<?php print_heading(get_string('customer', 'block_courseshop'), 'center', 2); ?>
<table class="generalbox" width="100%">
    <tr>
        <td class="cell">
           <?php echo $portlet->firstname ?> <?php echo $portlet->lastname ?> <b> <?php print_string('identifiedby', 'block_courseshop') ?></b> (<a href="mailto:<?php echo  $portlet->email ?>"><?php echo $portlet->email ?></a>)<br />
           <b><?php echo $portlet->city ?> (<?php echo $portlet->country ?>)<br />           
        </td>
        <td align="right">
        	<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/customers/view.php?id={$id}&pinned={$pinned}&view=viewCustomer&customer={$portlet->id}" ?>" target="_blank"><?php print_string('seethecustomerdetail', 'block_courseshop') ?></a>
        </td>
    </tr>
</table>