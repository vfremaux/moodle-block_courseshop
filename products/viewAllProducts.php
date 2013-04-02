<?php

    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    $cmd = optional_param('cmd', '', PARAM_ALPHA);
    $order = optional_param('order', 'code', PARAM_ALPHA);
    $dir = optional_param('dir', 'ASC', PARAM_ALPHA);

    // execute controller
    //echo "[$view:$cmd]";

    $hashandlersstr = get_string('hashandlers', 'block_courseshop');

    if ($cmd != ''){
       include $CFG->dirroot.'/blocks/courseshop/products/viewAllProducts.controller.php';
    }

    $products = array();

    // if slave get entries in master catalog and then overrides whith local descriptions
    $masterproducts = array();
    if ($theCatalog->isslave){
        $sql = "
            SELECT 
               ci.code as code,
               ci.*,
               IF(t.id IS NULL, 0, t.ratio) as tax,
               1 as masterrecord
            FROM 
               {$CFG->prefix}courseshop_catalogitem as ci
            LEFT JOIN
               {$CFG->prefix}courseshop_tax as t
            ON 
               ci.taxcode = t.id
            WHERE
               setid = 0 AND
               catalogid = '$theCatalog->groupid'
            ORDER BY
               $order $dir
        ";
        if (!$masterproducts = get_records_sql($sql)){
            $masterproducts = array();
        }
    }

    // local override
    $sql = "
        SELECT 
           ci.*,
           IF(t.id IS NULL, 0, t.ratio) as tax
        FROM 
           {$CFG->prefix}courseshop_catalogitem as ci
        LEFT JOIN
           {$CFG->prefix}courseshop_tax as t
        ON 
           ci.taxcode = t.id
        WHERE
           setid = 0 AND
           catalogid = '$catalogid'
        ORDER BY
           $order $dir
    ";
    if ($localproducts = get_records_sql($sql)){
        if ($theCatalog->isslave){
            foreach($localproducts as $code => $product){
                $localproducts[$code]->masterid = $masterproducts[$product->code]->id;
            }
        }
    } else {
        $localproducts = array();
    }
    
    $products = array_merge($masterproducts, $localproducts);
?>
<style>
.providingline { background-color : transparent ; color : #000000 }
.previewline { background-color : transparent ; color : #808080 }
.suspendedline { background-color : #fefefe ; color : #A0A0A0 }
.abandonnedline { background-color : #600000 ; color : #A00000 }
.slaved { filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=50, FinishOpacity=50, Style=0, StartX=0,  FinishX=100, StartY=0, FinishY=100) }
.engraved { filter:progid:DXImageTransform.Microsoft.Engrave() progid:DXImageTransform.Microsoft.Alpha(Opacity=50, FinishOpacity=50, Style=0, StartX=0,  FinishX=100, StartY=0, FinishY=100) }
</style>
<script type="text/javascript">
var productSelector;
function __init(){
   productSelector = new objectSelector('selection', 'productSelector');
}
</script>

<?php 
print_heading(get_string('catalogue', 'block_courseshop'), 'center', 1);
print_box_start();
?> 

<table width="100%">
    <tr>
        <td class="param"><?php print_string('name', 'block_courseshop') ?></td>
        <td class="value"><?php echo $theCatalog->name ?></td>
        <td rowspan="2" width="120" align="center" style="font-weight : bolder">
<?php
if ($theCatalog->ismaster){
?>
            <?php print_string('master', 'block_courseshop') ?>
<?php
} elseif ($theCatalog->isslave){
?>
            <?php print_string('slave', 'block_courseshop') ?>
<?php
} else {
?>
            <?php print_string('standalone', 'block_courseshop') ?>
<?php
}
?>
        </td>
    </tr>
    <tr>
        <td class="param"><?php print_string('description') ?></td>
        <td class="value"><?php echo $theCatalog->description ?></td>
    </tr>
</table>

<?php
print_box_end();
?>

<form name="selection" action="<?php $CFG->wwwroot.'/blocks/courseshop/products/view.php' ?>" method="get">
<input type="hidden" name="id" value="<?php p($id) ?>" />
<input type="hidden" name="pinned" value="<?php p($pinned) ?>" />
<input type="hidden" name="catalogid" value="<?php p($catalogid) ?>" />
<input type="hidden" name="view" value="viewAllProducts" />
<input type="hidden" name="items" value="" />
<input type="hidden" name="cmd" value="" />

<table width="100%" class="generaltable">
<?php
if (count(array_keys($products)) == 0) {
?>
<tr>
   <td colspan="4" class="productRow">
   <?php print_string('noproducts', 'block_courseshop') ?>
   </td>
</tr>
<?php
} else {
?>
<tr class="product" valign="top">
   <!--<th class="header c0">
       <?php //print_string('sel', 'block_courseshop') ?>
   </th>-->
   <th class="header c0">
       <?php print_string('image', 'block_courseshop') ?>
   </th>
   <th class="header c1">
       <?php print_string('code', 'block_courseshop') ?>
   </th>
   <th class="header c2" width="150" align="center">
       <?php print_string('label', 'block_courseshop') ?> 
   </th>
   <th class="header c3">
       <?php print_string('designation', 'block_courseshop') ?> 
   </th>
   <th class="header c4">
       <?php print_string('price', 'block_courseshop') ?>
   </th>
   <th class="header c5">
       <?php print_string('TTC', 'block_courseshop') ?>
   </th>
<?php
if (@$CFG->block_courseshop_prices > 1){
?>
   <td class="header c6">
       <?php print_string('price2', 'block_courseshop') ?> 
   </td>
<?php
}
if (@$CFG->block_courseshop_prices > 2){
?>
   <td class="header c7">
       <?php print_string('price3', 'block_courseshop') ?>
   </td>
<?php
}
?>
   <th class="header c8">
      <?php print_string('status', 'block_courseshop') ?>
   </th>
   <th class="header c9">
      <?php print_string('sales', 'block_courseshop') ?>
   </th>

   <th class="header lastcol">
      <?php print_string('stock', 'block_courseshop') ?>
   </th>
</tr>
<?php
    foreach (array_values($products) as $portlet){
        $portlet->selector = 'productSelector';
        $portlet->catalog = &$theCatalog;
        
        if (file_exists($CFG->dirroot.'/blocks/courseshop/datahandling/handlers/'.$portlet->code.'.class.php')){
        	if ($portlet->enablehandler){
	        	$portlet->code .= " <img title=\"{$hashandlersstr}\" src=\"{$CFG->wwwroot}/blocks/courseshop/pix/hashandler.jpg\" />";
	        } else {
	        	$portlet->code .= " <img title=\"{$hashandlersstr}\" src=\"{$CFG->wwwroot}/blocks/courseshop/pix/hashandlerdisabled.jpg\" />";
	        }
        }
       
        // product is standalone
        if (!$portlet->isset){
            if ($portlet->thumb  == '') {
                if ($portlet->image  == ''){
                    $lang = current_language();
                    $portlet->thumb = $CFG->wwwroot."/blocks/courseshop/pix/defaultProduct.gif";
                } else {
                    $portlet->thumb = $portlet->image;
                }
            }
            include $CFG->dirroot.'/blocks/courseshop/lib/productAdminLine.portlet.php';
        }

        // product is either a set or a bundle 
        else {
            $portlet->set = array();

            // get products in the master catalog for this set
            if ($theCatalog->isslave){
                // Get the code of the clone set (in the current catalog)
                $theCode = get_field('courseshop_catalogitem', 'code', 'id', '$portlet->id');

                // get the id of the cloned set in master catalog
                $theSet = get_field_select('courseshop_catalogitem', 'id', " code = '{$theCode}' AND catalogid = '{$theCatalog->groupid}' ");

                // get products attached to it in the master catalog
                $sql = "
                    SELECT
                        ci.*,
                        1 as masterrecord
                     FROM
                        {$CFG->prefix}courseshop_catalogitem as ci
                     WHERE
                        setid = '{$theSet}' AND
                        catalogid = '{$theCatalog->groupid}'
                ";
                if ($sets = get_records_sql($sql)){

                    foreach($sets as $setid => $aSetElement){
                        if (!isset($portlet->set)) $portlet->set = array();
                        $aSetElement->catalog = &$theCatalog;
                        if ($sets[$setid]->thumb  == '') {
                            if ($sets[$setid]->image  == ''){
                                $lang = current_language();
                                $sets[$setid]->thumb = $CFG->wwwroot."/blocks/courseshop/pix/defaultProduct.gif";
                            } else {
                                $aSetElement->thumb = $aSetElement->image;
                            }
                        }
                        $portlet->set[$aSetElement->code] = $aSetElement;
                    }
                } else {
                    $sets = array();
                }
            }

            // get products overrides in the local or standalone catalog 
            $set = (!$theCatalog->isslave) ? $portlet->id : $theSet ;
            $sql = "
                SELECT
                    ci.*,
                    0 as masterrecord
                 FROM
                    {$CFG->prefix}courseshop_catalogitem as ci
                 WHERE
                    setid = '{$set}' AND
                    catalogid = '$catalogid'
            ";
            if ($localsets = get_records_sql($sql)){

                foreach($localsets as $setid => $aSetElement){
                    if (!isset($portlet->set)) $portlet->set = array();
                    $localsets[$setid]->catalog = &$theCatalog;
                    if ($localsets[$setid]->thumb  == '') {
                        if ($localsets[$setid]->image  == ''){
                            $lang = current_language();
                            $localsets[$setid]->thumb = $CFG->wwwroot."/blocks/courseshop/pix/defaultProduct.gif";
                        } else {
                            $localsets[$setid]->thumb = $aSetElement->image;
                        }
                    }
                    $portlet->set[$aSetElement->code] = $localsets[$setid];
                }
            }

            // is a product set
            if ($portlet->isset == PRODUCT_SET){
                $portlet->thumb = $CFG->wwwroot."/blocks/courseshop/pix/productset.gif";
                include $CFG->dirroot.'/blocks/courseshop/lib/productAdminSet.portlet.php';

            // is a product bundle
            } else {
                // update bundle price info
                $bundlePrice = 0;
                $bundleTTCPrice = 0;
                foreach(array_values($portlet->set) as $aBundleElement){
                    // accumulate untaxed
                    $bundlePrice += $aBundleElement->price1;
                    // accumulate taxed after tax transform
                    $price = $aBundleElement->price1;
                    $aBundleElement->TTCprice = courseshop_calculate_taxed($aBundleElement->price1, $aBundleElement->taxcode);
                    $bundleTTCPrice += $aBundleElement->TTCprice;
                }
                // update bundle price in database for other applications. Note that only visible product entry 
                // is updated.
                $record->id = $portlet->id;
                $record->price1 = $bundlePrice;
                update_record('courseshop_catalogitem', $record);

                $portlet->price1 = $bundlePrice;
                $portlet->bundleTTCPrice = $bundleTTCPrice;
                if ($portlet->thumb == ''){
                    $portlet->thumb = 'blocks/courseshop/pix/productbundle.gif';
                }
                include $CFG->dirroot.'/blocks/courseshop/lib/productAdminBundle.portlet.php';
          }
       }
   }
}
?>
</table>
</form>
<table>
   <tr>
      <td align="left">
      </td>
      <td align="right">
		 <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/category/view.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;view=viewAllCategory" ?>"><?php print_string('edit_categories', 'block_courseshop') ?></a> -
		 <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/edit_product.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}" ?>"><?php print_string('newproduct', 'block_courseshop') ?></a> - 
         <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/edit_set.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}" ?>"><?php print_string('newset', 'block_courseshop') ?></a> - 
         <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/edit_bundle.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}" ?>"><?php print_string('newbundle', 'block_courseshop') ?></a>		 
      </td>
   </tr>
</table>
<br />
<br />
