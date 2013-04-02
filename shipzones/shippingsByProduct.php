<!-- <menu id="shippingsByProduct" label="Frais d'envoi" target="./shippingsByProduct" index="2" editable="no" loader="spage" /> -->
<?php
// $_CFG['debug'] = 'DB';

$catalogId = captureInSession('catalogId', 1);
$productId = captureInSession('productId');
$cmd = get('cmd', 'REQUEST');
$order = captureInSession('order', 'cz.Id');
$dir = captureInSession('dir', 'ASC');

include_once "classes/CatalogShipZone.class.php";
include_once "classes/Catalog.class.php";
include_once "classes/CatalogItem.class.php";
include_once "inc/display/stdforms.inc.php";

$theCatalog = new Catalog($catalogId);
if ($theCatalog->isslave){
    $theCatalog = new Catalog($theCatalog->groupId);
}

$theProduct = new CatalogItem($productId);

// execute controller
if ($cmd != ''){
   include includePage("{$_CXT->level1}/{$_CXT->level2}/shippingsByProduct.controller.php");
}

// get master or independant catalogs
$query = "
   SELECT
      Id,
      name
   FROM
       {$CFG->prefix}courseshop_catalog
   WHERE
       groupId = Id OR groupId IS NULL
";
$catalogs = array();
$results = mysql_execute($query);
while($aCatalogInfo = mysql_fetch_object($results))
   $catalogs[] = $aCatalogInfo;

// get shipping information
$query = "
    SELECT 
       cs.productCode as productCode,
       cs.zoneId as zoneId,
       cs.value as value,
       cs.formula as formula,
       cs.a as a,
       cs.b as b,
       cs.c as c,
       cz.zoneCode as zoneCode,
       cz.description as zone,
       ci.name as pName,
       ci.code as pCode
    FROM 
       {$CFG->prefix}courseshop_catalogshipping as cs,
       {$CFG->prefix}courseshop_catalogshipzone as cz,
       {$CFG->prefix}courseshop_catalogitem as ci
    WHERE
       cz.Id = cs.zoneId AND
       ci.code = cs.productCode AND
       cz.catalogId = '{$theCatalog->Id}' AND
       ci.catalogId = '{$theCatalog->Id}' AND
       cs.productCode = '{$theProduct->code}'
    ORDER BY
       $order $dir
";
$results = mysql_execute($query);
$shippings = array();
while ($aShipping = mysql_fetch_object($results)) {
   $aShipping->Id = $aShipping->productCode . "::" . $aShipping->zoneId;
   $shippings[] = $aShipping;
}

/* get Products */
$query = "
    SELECT 
        ci.Id,
        ci.code,
        ci.shortname
    FROM
       {$CFG->prefix}courseshop_catalogitem as ci
    WHERE
       ci.catalogId = '{$theCatalog->Id}' AND
       ci.isSet = 'N'
";
$results = mysql_execute($query);
$products = array();
while ($aProduct = mysql_fetch_object($results)) {
   $products[] = $aProduct;
}
?>
<script type="text/javascript">
var shippingSelector;
function __init(){
   shippingSelector = new objectSelector('selection', 'shippingSelector');
}
</script>

<?php echo  getTitle('h1', getTranslation('Catalogue Produits')) ?><br>
<?php echo  getTitle('h2', getTranslation('Frais d\'envoi (par produit)')) ?> 

<table style="border : 1px solid #606060 ; background-color : #404040">
    <tr>
        <td class="colHead" colspan="2">
            <?php print_string('Catalogue') ?>
        </td>
        <td class="colHead" align="right">
<?php
fixObject($catalogs, 'catalog', '<%%name%%>', $theCatalog->Id);
?>
        </td>
    </tr>
    <tr>
        <td class="param"><?php print_string('Nom :') ?></td>
        <td class="value"><?php echo  $theCatalog->name ?></td>
        <td rowspan="2" width="120" align="center" style="font-weight : bolder">
<?php
if ($theCatalog->ismaster){
?>
            <?php print_string('Catalogue<br>maître') ?>
<?php
}
elseif ($theCatalog->isslave){
?>
            <?php print_string('Catalogue<br>lié') ?>
<?php
}
else{
?>
            <?php print_string('Catalogue<br>indépendant') ?>
<?php
}
?>
        </td>
    </tr>
    <tr>
        <td class="param"><?php print_string('Description :') ?></td>
        <td class="value"><?php echo  $theCatalog->description ?></td>
    </tr>
</table>

<p><table style="border : 1px solid #606060 ; background-color : #404040">
    <tr>
        <td class="colHead" width="50%">
            <?php print_string('Produit') ?>
        </td>
        <td class="colHead" align="right" width="50%">
<?php
if ($productId != ''){
    fixObject($products, 'product', '<%%code%%> - <%%shortname%%>', $productId);
}
?>
        </td>
    </tr>
    <tr>
        <td class="param"><?php print_string('Code :') ?></td>
        <td class="value"><?php echo  $theProduct->code ?></td>
    </tr>
    <tr>
        <td colspan="2"  class="value" align="left" style="font-weight : normal">
            <?php echo  $theProduct->shortname ?>
        </td>
    </tr>
</table>

<?php
if ($productId == ''){
    fixObject($products, 'product', '<%%code%%> - <%%shortname%%>');
    exit(0);
}
?>

<form name="selection" action="<?php echo  getUrl("spage.php") ?>" method="get">
<input type="hidden" name="p" value="<?php echo  $_CXT->level1 ?>/<?php echo  $_CXT->level2 ?>/shippingsByProduct">
<input type="hidden" name="items" value="">
<input type="hidden" name="cmd" value="">
<table>
<?php
if (count($shippings) == 0) {
?>
<tr>
   <td colspan="5">
   <?php echo  loadText('noProductShippings') ?>
   </td>
</tr>
<?php
}
else {
?>
<tr class="shippings">
   <td valign="top" class="columnHead font11" rowspan="2">
       <?php print_string('Sél.') ?>
   </td>
   <td valign="top" class="columnHead font11" rowspan="2">
       <?php print_string('Zone') ?>
   </td>
   <td valign="top" class="columnHead font11" width="150" rowspan="2">
       <?php print_string('Poids') ?>
   </td>
   <td valign="top" class="columnHead font11" width="150" rowspan="2">
       <?php print_string('Formule') ?>
<?php
$portlet->topic = 'commerce:shipping:formula';
getPortlet('lib/help/invoke.portlet.php', $portlet);
?>
   </td>
   <td valign="top" class="columnHead font11" width="150" colspan="3">
       <?php print_string('Paramètres') ?>
   </td>
   <td valign="top" class="columnHead font11" rowspan="2">
   &nbsp;
   </td>
</tr>
<tr>
   <td valign="top" class="columnHead font11" width="50">
       <?php print_string('a') ?>
   </td>
   <td valign="top" class="columnHead font11" width="50">
       <?php print_string('b') ?>
   </td>
   <td valign="top" class="columnHead font11" width="50">
       <?php print_string('c') ?>
   </td>
</tr>
<?php
    foreach ($shippings as $portlet){
        $portlet->selector = 'shippingSelector';
        getPortlet('lib/product/shippingAdminLine.portlet.php', $portlet);
    }
}
?>
</table>
</form>
<table>
   <tr>
      <td align="left">
         <a id="selectall_link" href="Javascript:eval('shippingSelector.selectall()')"><img name="selectall_img" src="images/icons/selectall.gif" border="0" alt="<?php echo  getString('selectAll') ?>"></a>
         <a id="selectnone_link" href="Javascript:;"><img name="selectnone_img" src="images/icons/selectnone_shadow.gif" border="0" alt="<?php echo  getString('selectNone') ?>"></a>
         <a id="deleteItems_link" href="Javascript:;"><img name="deleteItems_img" src="images/icons/delete_shadow.gif" border="0" alt="<?php echo  getString('deleteItems') ?>"></a>
      </td>
      <td align="right">
         <a href="<?php echo  getUrl("spage.php?p={$_CXT->level1}/{$_CXT->level2}/editShipping&cmd=edit&mode=ByProduct") ?>"><img src="images/icons/add.gif" border="0"></a>
      </td>
   </tr>
</table>
<br>
<br>
