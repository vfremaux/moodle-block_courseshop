<tr class="taxe" valign="top">
   <td align="left" class="cell c0">
      <?php echo $portlet->code ?>
   </td>
   <td align="left" class="cell c1">
      <?php echo $portlet->title ?>
   </td>
   <td align="left" class="cell c2">
       <?php 
       $tax = get_record('courseshop_tax ', 'id', $portlet->code);
	   $portlet->country = $tax->country;
	   echo '('.$portlet->country.')' 
	   ?>
	</td>
   <td align="left" class="cell c3">
       <?php echo $portlet->ratio ?>
   </td>
   <td align="right" class="cell lastcol">
      <a class="activeLink" href="<?php echo $CFG->wwwroot."/blocks/courseshop/taxes/edit_tax.php?id={$id}&pinned={$pinned}&taxid={$portlet->code}&cmd=updatetax"; ?>"><img src="<?php echo $CFG->pixpath.'/t/edit.gif' ?>" border="0"></a>
	  <a class="activeLink" href="<?php echo $CFG->wwwroot."/blocks/courseshop/taxes/view.php?id={$id}&pinned={$pinned}&view=viewAllTaxes&id=$id&taxid={$portlet->code}&cmd=deletetax"; ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0"></a>
   </td>
</tr>




