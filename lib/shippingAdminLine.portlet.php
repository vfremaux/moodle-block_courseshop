<tr class="">
   <td valign="top" class="c0">
       <input type="checkbox" name="items[]" value="<?php echo $portlet->id ?>" />
   </td>
   <td valign="top" class="c1" width="150">
       <?php echo $portlet->productcode ?>
   </td>
   <td valign="top" class="c2" width="150">
       <?php echo $portlet->value ?>
   </td>
   <td valign="top" class="c3" width="150">
       <?php echo $portlet->formula ?>
   </td>
   <td valign="top" class="c4" width="150">
       <?php echo $portlet->a ?>
   </td>
   <td valign="top" class="c5" width="150">
       <?php echo $portlet->b ?>
   </td>
   <td valign="top" class="c6" width="150">
       <?php echo $portlet->c ?>
   </td>
   <td valign="top" class="c7">
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/shipzones/edit_shipping.php?id=$id&amp;pinned=$pinned&amp;cmd=edit&amp;shippingid={$portlet->id}&zoneid={$portlet->zoneid}&productcode={$portlet->productcode}"; ?>"><img src="<?php echo $CFG->pixpath.'/t/edit.gif' ?>" border="0"></a>
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/shipzones/zoneindex.php?id=$id&amp;pinned=$pinned&amp;cmd=deleteItems&amp;items[]={$portlet->id}&zoneid={$portlet->zoneid}"; ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0"></a>
   </td>
</tr>
