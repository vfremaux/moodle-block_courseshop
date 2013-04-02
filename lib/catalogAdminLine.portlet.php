<tr class="<?php echo ($portlet->items == 0) ? "empty" : "" ; ?>" valign="top">
    <td>
<?php  
if ($portlet->isslave){ 
?>
        <img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/link.gif' ?>" /> 
<?php
}
?>
    <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?view=viewAllProducts&catalogid={$portlet->id}&id={$id}&pinned=$pinned" ?>"><?php echo $portlet->name ?></a>
    </td>
    <td>
        <?php echo $portlet->description ?>
    </td>
    <td>
        <?php echo $portlet->items ?>
    </td>
    <td>
    <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/catalogs/edit_catalogue.php?id={$id}&catalogid={$portlet->id}&pinned=$pinned" ?>"><img src="<?php echo $CFG->pixpath.'/t/edit.gif' ?>" border="0"></a>
        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/index.php?catalogid={$portlet->id}&id={$id}&cmd=deletecatalog&pinned=$pinned" ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0"></a>
		<!--<a href="Javascript:deleteDialog('<?php print_string('deleteCatalogDialog', 'block_courseshop') ?>', 'index.php?cmd=deletecatalog&catalogid=<?php echo $portlet->id ?>')"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0"></a>-->
    </td>
</tr>