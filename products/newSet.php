<?php
	include "../../../config.php";
	include $CFG->dirroot."/blocks/courseshop/classes/Catalog.class.php";
	
   	$catalogid = required_param('catalogid', PARAM_INT);

    // get catalog object
    
    $theCatalog = new Catalog($catalogid);

    // get unlinked products
    $unlinkedProducts = get_records_select('courseshop_catalogitem', " setid = 0 AND catalogid = $catalogid ", 'id, shortname');
    
    // get all categories
    $categories = get_records('courseshop_catalogcategory', 'catalogid', $catalogid);

?>
<form name="newSet" action="<?php echo $CFG->wwwroot.'/blocks/courseshop/products/view.php' ?>" method="POST">
<input type="hidden" name="id" value="<?php p($id) ?>" />
<input type="hidden" name="pinned" value="<?php p($pinned) ?>" />
<input type="hidden" name="view" value="viewAllProducts">
<input type="hidden" name="cmd" value="addproduct">
<input type="hidden" name="catalogid" value="<?php p($catalogid) ?>">
<input type="hidden" name="isSet" value="1">
<input type="hidden" name="status" value="PREVIEW">
<table class="setBox" width="100%">
    <tr>
       <td valign="top" style="padding : 2px" class="listTitle" colspan="2">
          <h1><?php print_string('catalogue', 'block_courseshop') ?>: <?php print_string('newset', 'block_courseshop') ?></h1>
       </td>
    </tr>
    <tr>
        <td class="param" width="25%" valign="top"><?php print_string('productid', 'block_courseshop') ?>:</td>
        <td class="data" width="*">
            <input type="text" name="code" value="" maxlength="30">
        </td>
    </tr>
    <tr>
        <td class="param" valign="top"><?php print_string('label', 'block_courseshop') ?>:</td>
        <td class="data">
            <input type="text" name="shortname" value="" maxlength="40">
        </td>
    </tr>
    <tr>
        <td class="param" valign="top"><?php print_string('title', 'block_courseshop', 'block_courseshop') ?>:</td>
        <td class="data">
            <input type="text" name="name" value="" maxlength="255" style="width : 100%">
        </td>
    </tr>
    <tr height="300">
        <td class="param" valign="top"><?php print_string('description') ?>:</td>
        <td class="data">
            <textarea name="description" id="description" style="width : 100% ; height : 100%"></textarea>
        </td>
    </tr>
    <tr>
        <td class="param" valign="top"><?php print_string('section', 'block_courseshop') ?>:</td>
        <td class="data">
<?php
if(count($categories) == 0){
            print_string('nocategories', 'block_courseshop');
}
else{
?>
            <select name="categoryid">
                <option value=""><?php print_string('outofcategory', 'block_courseshop') ?></option>
<?php
    foreach($categories as $aCategory){
?>
                <option value="<?php echo $aCategory->id ?>"><?php echo  $aCategory->name ?></option>
<?php
    }
?>
        </select>
<?php
}
?>
        </td>
    </tr>
    <tr>
        <td class="param" valign="top"><?php print_string('image', 'block_courseshop') ?>:</td>
        <td class="data">
            <input type="text" name="image" value="" maxlength="255" style="width : 100%">
        </td>
    </tr>
    <tr>
        <td class="param" valign="top"><?php print_string('thumbnail', 'block_courseshop') ?>:</td>
        <td class="data">
            <input type="text" name="thumb" value="" maxlength="255" style="width : 100%">
        </td>
    </tr>
    <tr height="300">
        <td class="param" valign="top"><?php print_string('notes', 'block_courseshop') ?>:</td>
        <td class="data">
            <textarea name="notes" id="notes" style="width : 100% ; height : 100%"></textarea>
        </td>
    </tr>
    <tr>
        <td class="param"><?php print_string('products', 'block_courseshop') ?>:</td>
        <td class="data">
            <select name="productsinset[]" multiple size="10" style="width : 180px">
<?php
foreach($unlinkedProducts as $aProduct){
?>
                <option value="<?php echo $aProduct->id ?>"><?php echo $aProduct->shortname ?></option>
<?php
}
?>
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="right">
            <input type="submit" name="go_btn" value="<?php print_string('save', 'block_courseshop'); ?>" />
            <input type="button" name="cancel_btn" value="<?php print_string('cancel'); ?>" onclick="document.forms['newSet'].cmd.value = ''; document.forms['newSet'].submit();" />
        </td>
    </tr>
</table>
<p>
