<tr valign="top">
   <td width="30" class="cell c0">
   &nbsp;
   </td>
   <td width="100" class="cell c1">
     <a class="activeLink" href="<?php echo $CFG->wwwroot."/blocks/courseshop/bills/view.php?view=viewBill&id={$id}&pinned={$pinned}&billid={$portlet->id}" ?>"><?php echo  'B-' . $portlet->date . '-' . $portlet->id ?></a>
   </td>
   <td width="*" class="cell c2">
      <?php echo $portlet->title ?>
   </td>
   <td width="120" class="cell c3">
       <code><a href="<?php echo $CFG->wwwroot."/blocks/courseshop/shop/scantrace.php?transid={$portlet->transactionid}&id=$id&pinned=$pinned" ?>" title="<?php print_string('scantrace', 'block_courseshop') ?>"><?php echo $portlet->transactionid ?></a></code>
   </td>
   <td width="100" align="right" class="cell lastcol">
		<?php echo  sprintf("%.2f", round($portlet->amount, 2)) ?> <?php echo get_string($portlet->currency.'_symb', 'block_courseshop') ?>
   </td>
</tr>
<tr valign="top">
   <td width="30" class="cell c1">
      &nbsp;
   </td>
   <td width="100" class="cell c2">
      &nbsp;
   </td>
   <td width="*" class="cell c3" colspan="3">
      <a class="activeLink" href="<?php echo $CFG->wwwroot."/blocks/courseshop/customers/view.php?id=$id&pinned=$pinned&view=viewCustomer&userid=$portlet->userid" ?>"><?php echo $portlet->firstname . ' ' . $portlet->lastname ?></a>
      (<a href="mailto:<?php echo $portlet->email ?>"><?php echo $portlet->email ?></a>)
   </td>
</tr>
