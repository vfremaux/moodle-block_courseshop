<tr class="taxe" valign="top" >
   <td class="cell c1">
      <?php echo $portlet->title ?>
   </td>
   <td class="cell c2">
       <?php echo $portlet->country ?>
   </td>
   <td class="cell c3">
       <?php echo $portlet->ratio ?>
   </td>
   <td align="right" class="cell lastcol">
      <a class="activeLink" href="<?php echo $CFG->wwwroot."/blocks/courseshop/taxes/edit_tax.php?id={$id}&pinned={$pinned}&taxid={$portlet->id}&cmd=updatetax"; ?>"><img src="<?php echo $CFG->pixpath.'/t/edit.gif' ?>" border="0"></a>
	  <a class="activeLink" href="<?php echo $portlet->url."&taxid={$portlet->id}&cmd=deletetax"; ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0"></a>
   </td>
</tr>
