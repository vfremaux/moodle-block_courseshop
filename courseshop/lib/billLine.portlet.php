<tr class="billBloc {$portlet->status}">
   <td valign="top" style="padding : 2px" colspan="4" class="billCategoryTitleBox">
      <?php echo get_string('billStatusTitle_{$portlet->status}', 'block_courseshop') ?>
   </td>
</tr>
<tr class="bill">
   <td width="120" valign="top" style="padding : 2px" class="billId">
      <a class="activeLink" href="<?php echo $CFG->wwwroot.'/blocks/courseshop/bills/view.php?id={$id}&pinned={$pinned}&view=viewBill&billId=' . $portlet->Id) ?>"><?php echo  'B-' . $portlet->date . '-' . $portlet->Id ?></a>
   </td>
   <td width="*" valign="top" style="padding : 2px" class="billTitle">
      <?php echo  $portlet->title ?>
   </td>
   <td width="100" valign="top" style="padding : 2px" class="billStatus">
       <?php echo  $portlet->status ?>
   </td>
   <td width="100" valign="top" style="padding : 2px" class="billAmount">
       <?php echo  sprintf("%.2f", round($portlet->amount, 2)) ?> <?php echo $portlet->currency ?>
   </td>
</tr>
