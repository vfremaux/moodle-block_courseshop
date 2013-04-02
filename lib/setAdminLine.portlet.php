<tr class="<?php echo strtolower($subportlet->status) ?>line">
    <td class="<?php echo (@$subportlet->masterrecord == 0) ? "" : "engraved" ; ?>">
       	<img src="<?php echo $subportlet->thumb ?>" vspace="10" border="0" height="50">
    </td>
    <td class="setElementCode <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
	     <b><?php echo $subportlet->code ?></b>
    </td>
    <td class="setElementCode <?php echo (@$subportlet->masterrecord == 0) ? '' : 'slaved' ; ?>">
        <?php echo $subportlet->shortname ?>
    </td>
    <td class="setElementAttribute <?php echo (@$subportlet->masterrecord == 0) ? '' : 'slaved' ; ?>">
        <?php echo sprintf("%.2f", round($portlet->price1, 2)) ?><br>
        (<?php echo $subportlet->taxcode ?>)
    </td>
    <td class="setElementAttribute <?php echo (@$subportlet->masterrecord == 0) ? '' : 'slaved' ; ?>">
        <?php print_string($subportlet->status, 'block_courseshop') ?>
    </td>
    <td align="right" width="10">
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/edit_product.php?id={$id}&amp;pinned={$pinned}&amp;productid={$subportlet->id}" ?>"><img src="<?php echo $CFG->pixpath.'/t/edit.gif' ?>" border="0" title="<?php print_string('editproduct', 'block_courseshop') ?>"></a><br/>
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;view=viewAllProducts&amp;cmd=unlinkproduct&amp;productid={$subportlet->id}" ?>"><img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/unlink.gif' ?>" border="0" title="<?php print_string('removeproductfromset', 'block_courseshop') ?>"></a><br/>
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;view=viewAllProducts&amp;cmd=deleteItems&amp;items={$subportlet->id}" ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0" title="<?php print_string('deleteproduct', 'block_courseshop') ?>"></a>
<?php
if ($subportlet->catalog->isslave){
    if ($subportlet->masterrecord == 1){
?>
     <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;view=viewAllProducts&amp;cmd=makecopy&productid={$subportlet->id}" ?>"><img src="<?php $CFG->pixpath.'/t//copy.gif' ?>" border="0" title="<?php print_string('createlocalversion', 'block_courseshop') ?>"></a>
<?php
    } else {
?>
     <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;view=viewAllProducts&amp;cmd=freecopy&productid={$portlet->id}" ?>"><img src="images/icons/uncopy.gif" border="0" title="<?php print_string('removelocalversion', 'block_courseshop') ?>"></a>
<?php
    }
}
?>
    </td>
</tr>