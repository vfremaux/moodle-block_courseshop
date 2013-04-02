<tr valign="top">
   <td class="c0" width="10" rowspan="2">
       <input type="checkbox" name="items[]" value="<?php echo $portlet->id ?>" />
   </td>
   <td class="c1" width="20%">
       <?php echo $portlet->zonecode ?>
   </td>
   <td class="c2" width="50%">
       <?php echo $portlet->description ?>
   </td>
   <td class="c2">
       <?php echo $portlet->billscopeamount ?>
   </td>
   <td class="c2" width="10%">
       <?php echo $portlet->tax ?>
   </td>
   <td class="c3" width="5%">
       <?php echo $portlet->entries ?>
   </td>
   <td class="c4" width="20%">
<?php
if ($portlet->entries == 0){
   echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/shipzones/index.php?id={$id}&amp;pinned={$pinned}&amp;cmd=deleteItems&amp;items={$portlet->id}\"><img src=\"{$CFG->pixpath}/t/delete.gif\" border=\"0\"></a>";
	$addzonestr = get_string('newshipping', 'block_courseshop');
   	echo " <a href=\"{$CFG->wwwroot}/blocks/courseshop/shipzones/edit_shipping.php?id={$id}&amp;pinned={$pinned}&amp;zoneid={$portlet->id}\">$addzonestr</a>";
} else {
	$editzonestr = get_string('editshippingzone', 'block_courseshop');
   	echo " <a href=\"{$CFG->wwwroot}/blocks/courseshop/shipzones/zoneindex.php?id={$id}&amp;pinned={$pinned}&amp;zoneid={$portlet->id}\">$editzonestr</a>";
}
echo " <a href=\"{$CFG->wwwroot}/blocks/courseshop/shipzones/edit_shippingzone.php?id={$id}&amp;pinned={$pinned}&amp;cmd=update&amp;item={$portlet->id}\"><img src=\"{$CFG->pixpath}/t/edit.gif\" border=\"0\"></a>";
?>
   </td>
</tr>
<tr valign="top">
   <td class="c1">
   	<b><?php print_string('applicability', 'block_courseshop') ?>: </b>
   </td>
   <td class="c2" colspan="1" style="font-family:monospace">
       <?php echo $portlet->applicability ?>
   </td>
</tr>