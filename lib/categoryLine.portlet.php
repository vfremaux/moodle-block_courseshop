

<tr class="category" valign="top" >
   <td class="cell c2">
      <?php echo $portlet->name ?>
   </td>
   <td class="cell c3">
       <?php echo $portlet->description ?>
   </td>
   
   <td align="right" class="cell lastcol">
   
      <a class="activeLink" href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/category/edit_category.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;categoryid={$portlet->id}&cmd=updatecategory"; ?>"><img src="<?php echo $CFG->pixpath.'/t/edit.gif' ?>" border="0"></a>
	  <a class="activeLink" href="<?php echo $portlet->url."&categoryid={$portlet->id}&cmd=deletecategory"; ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0"></a>
   </td>
</tr>
