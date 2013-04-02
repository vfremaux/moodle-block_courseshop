<tr class="<?php echo strtolower($portlet->status) ?>line">

  <td class="productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "engraved slaved" ; ?>"align="center">
     <img src="<?php echo $portlet->thumb ?>" vspace="10" border="0" height="50">
  </td>
  <td class="code productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>"align="left">
     <b><?php echo $portlet->code ?></b><br/>
     (<?php echo $portlet->shortname ?>)
  </td>
  <td class="name productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>" align="left">
      <?php echo $portlet->name ?>
  </td>
  <td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>" align="right">
     <?php echo sprintf("%.2f", round($portlet->price1, 2)) ?><br>
     (<?php echo $portlet->taxcode ?>)
  </td>
  <td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>"align="right">
     <?php echo sprintf("%.2f", round($portlet->price1 * (1 + ($portlet->tax / 100)), 2)) ?>
  </td> 
  <td class="status productLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>" align="right">
     <?php print_string($portlet->status, 'block_courseshop') ?>
  </td> 
  <td class="amount productLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>" align="center">
     <?php echo $portlet->sold ?>
  </td> 
  <td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>" align="center">
     <?php echo $portlet->stock ?>
  </td>
  <td align="right">
<?php
if (@$portlet->masterrecord == 0){
     echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/products/edit_product.php?productid={$portlet->id}&amp;id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}\"><img src=\"{$CFG->pixpath}/t/edit.gif\" border=\"0\"/></a> ";
}

$deletestr = get_string('deleteproduct', 'block_courseshop');
echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;view=viewAllProducts&amp;cmd=deleteItems&amp;items={$portlet->id}\"><img src=\"{$CFG->pixpath}/t/delete.gif\" border=\"0\" title=\"{$deletestr}\"></a>";

$createlocalstr = get_string('createlocalversion', 'block_courseshop');
$deletelocalversionstr = get_string('deletelocalversion', 'block_courseshop');

if ($portlet->catalog->isslave){
    if ($portlet->masterrecord == 1){
     	echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;view=viewAllProducts&amp;cmd=makecopy&amp;productid={$portlet->id}\"><img src=\"{$CFG->pixpath}/t/copy.gif\" border=\"0\" title=\"$createlocalstr\"></a>";
    } else {
     	echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;view=viewAllProducts&amp;cmd=freecopy&amp;productid={$portlet->id}\"><img src=\"{$CFG->pixpath}/t/uncopy.gif\" border=\"0\" title=\"{$deletelocalversionstr}\"></a>";
    }
}
?>
  </td>
</tr>
