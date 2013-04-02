<tr class="<?php echo strtolower($subportlet->status) ?>line">
    <td class="<?php echo (@$portlet->masterrecord == 0) ? "" : "engraved" ; ?>">
       <img src="<?php echo $subportlet->thumb ?>" vspace="10" border="0" height="50">
    </td>
    <td class="bundleElementCode <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
    	<?php echo $subportlet->code ?>
    </td>
    <td class="setElementCode <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo $subportlet->shortname ?>
    </td>
    <td class="setElementCode <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo $subportlet->name ?>
    </td>
    <td class="setElementAttribute <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo sprintf("%.2f", round($subportlet->price1, 2)) ?><br>
        (<?php echo $subportlet->taxcode ?>)
    </td>
    <td class="setElementAttribute <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo sprintf("%.2f", round($subportlet->TTCprice, 2)) ?><br>
    </td>
    <td class="setElementAttribute <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo get_string($subportlet->status, 'block_courseshop') ?>
    </td>
    <td align="right">
		<?php
		if (@$portlet->masterrecord == 0){
			echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/products/edit_product.php?id={$id}&amp;pinned={$pinned}&amp;productid={$subportlet->id}\"><img src=\"{$CFG->pixpath}/t/edit.gif\" border=\"0\" title=\"".get_string('edit', 'block_courseshop')."\"></a><br/>";
		}
		?>
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;view=viewAllProducts&amp;cmd=unlinkproduct&amp;productid={$subportlet->id}" ?>"><img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/unlink.gif' ?>" border="0" title="<?php print_string('removeproductfrombundle', 'block_courseshop') ?>"></a><br/>
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;view=viewAllProducts&amp;cmd=deleteitems&amp;items={$subportlet->id}" ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0" title="<?php print_string('delete') ?>"></a><br/>
<?php
if ($portlet->catalog->isslave){
    if ($portlet->masterrecord == 1){
?>
     <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;view=viewAllProducts&amp;cmd=makecopy&amp;productId={$subportlet->id}" ?>"><img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/copy.gif' ?>" border="0" title="<?php print_string('createoverride', 'block_courseshop') ?>"></a>
<?php
    } else{
?>
     <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/viewAllProducts.php?id=$id&pinned=$pinned&cmd=freecopy&productid={$subportlet->id}" ?>"><img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/uncopy.gif' ?>" border="0" title="<?php print_string('removeoverride', 'block_courseshop') ?>"></a>
<?php
    }
}
?>
    </td>
</tr>