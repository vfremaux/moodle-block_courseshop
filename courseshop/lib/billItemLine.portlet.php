<tr valign="top" class="row">
  
   <td width="30" class="cell c0">
      <a class="activeLink" href="<?php echo $CFG->wwwroot."/blocks/coursehop/bills/view.php?id={$id}&pinned={$pinned}&view=editBillItem&item={$portlet->id}" ?>"><?php echo $portlet->ordering . ". " ?></a>
   </td>
   <td width="60" class="cell c1">
      <?php echo $portlet->itemcode ?>
   </td>
   <td width="*" class="cell c2">
      <?php $portlet->description ?>
   </td>
   <td width="40" class="cell c3">
       <?php echo $portlet->delay ?>
   </td>
   <td width="80" class="cell c4">
       <?php echo sprintf("%.2f", round($portlet->unitcost, 2)) ?>
   </td>
   <td width="30" class="cell c5">
       <?php echo $portlet->quantity ?>
   </td>
   <td width="80" class="cell c6">
	   <span id="price_<?php echo $portlet->ordering ?>"><?php echo sprintf("%.2f", round($portlet->totalprice, 2)) ?></span>
   	</td>
   	<td width="30" class="cell c7">
       	<?php echo $portlet->taxcode ?>
   	</td>
   	<td width="60" class="cell lastcol">
		<?php 
   		if (empty($bill->idnumber)){
		?>
      	<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/bills/view.php?id={$id}&view=viewBill&cmd=relocating&relocated={$portlet->id}&z={$portlet->ordering}" ?>"><img src="<?php echo $CFG->pixpath.'/t/move.gif' ?>" border="0" alt="<?php print_string('move') ?>"></a>
      	<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/bills/edit_billitem.php?id={$id}&billid={$billid}&billitemid={$portlet->id}" ?>"><img src="<?php echo $CFG->pixpath.'/i/edit.gif' ?>" border="0" alt="<?php print_string('edit') ?>"></a>
      	<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/bills/view.php?id={$id}&view=viewBill&cmd=deleteItem&billitemid={$portlet->id}&z={$portlet->ordering}&billid={$billid}" ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0" alt="<?php print_string('delete') ?>"></a>
      	<?php
	    }
	    ?>
   </td>
</tr>
<tr>
   <td valign="top">&nbsp;
   </td>
   <td valign="top" class="itemDescription" colspan="9">
       <?php echo $portlet->description ?>
   </td>
</tr>
