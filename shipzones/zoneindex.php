<?php

    include '../../../config.php';
    include_once $CFG->dirroot.'/blocks/courseshop/locallib.php';

    $id = required_param('id', PARAM_INT);
    $pinned = required_param('pinned', PARAM_INT);
    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
    if (!$instance = get_record($blocktable, 'id', $id)){
        error('Invalid block');
    }
    $theBlock = block_instance('courseshop', $instance);
	$context = get_context_instance(CONTEXT_BLOCK, $theBlock->instance->id);
	
	if (empty($theBlock->config->catalogue)) {
		error('Bad block configuration');
	}
	
    // Security 
    // Service is available to unconnected people.

    // get active catalog from block 

    if (isset($theBlock->config)){
        $catalogid = $theBlock->config->catalogue;
		include_once $CFG->dirroot.'/blocks/courseshop/classes/Catalog.class.php';
	
		$theCatalog = new Catalog($catalogid);
		if ($theCatalog->isslave){
		    $theCatalog = new Catalog($theCatalog->groupid);
		    $catalogid = $theCatalog->groupid;
		}
   } else {
        error("This block is not configured");
    }

	$zoneid = optional_param('zoneid', PARAM_INT);
	$cmd = optional_param('cmd', PARAM_TEXT);

	include_once $CFG->dirroot.'/blocks/courseshop/classes/CatalogShipZone.class.php';

	// execute controller
	if (!empty($cmd)){
	   include "zoneindex.controller.php";
	}
	
	$zone = get_record('courseshop_catalogshipzone', 'id', $zoneid);

    $navlinks = array(array('name' => get_string('shipzones', 'block_courseshop'), 'url' => '', 'type' => 'title'));
    $navigation = build_navigation($navlinks);
    print_header(get_string('blockname', 'block_courseshop'), get_string('blockname', 'block_courseshop'), $navigation, '', '', false);

	// if slave get entries in master catalog and then overrides whith local descriptions
	$sql = "
	    SELECT 
	    	*
	    FROM 
	       {$CFG->prefix}courseshop_catalogshipping
	    WHERE
	       zoneid = '{$zoneid}'
	";
	if ($shippings = get_records_sql($sql)){

		print_heading(get_string('shipzone', 'block_courseshop'));
		print_heading(get_string('shippings', 'block_courseshop'), 2);
?>	
<table width="100%" class="generaltable">
    <tr>
        <td class="param"><?php print_string('catalogue', 'block_courseshop') ?></td>
        <td class="value"><?php echo $theCatalog->name ?></td>
        <td rowspan="3" width="120" align="center" style="font-weight : bolder">
        </td>
    </tr>
    <tr>
        <td class="param"><?php print_string('description') ?></td>
        <td class="value"><?php echo  $theCatalog->description ?></td>
    </tr>
    <tr>
        <td class="param"><?php print_string('shipzone', 'block_courseshop') ?></td>
        <td class="value"><?php echo  $zone->description ?></td>
    </tr>
</table>
<form name="selection" action="zoneindex.php" method="get">
<input type="hidden" name="items" value="">
<input type="hidden" name="cmd" value="">
<table width="100%">
<?php
		if (count($shippings) == 0) {
?>
<tr>
   <td colspan="5">
   <?php echo get_string('noshippings', 'block_courseshop') ?>
   </td>
</tr>
<?php
		} else {
?>
<tr class="zones" valign="top">
   <td class="header c0">
       <?php print_string('sel', 'block_courseshop') ?>
   </td>
   <td class="header c1">
       <?php print_string('productcode', 'block_courseshop') ?>
   </td>
   <td class="header c2">
       <?php print_string('value', 'block_courseshop') ?>
   </td>
   <td class="header c3" width="150">
       <?php print_string('formula', 'block_courseshop') ?>
   </td>
   <td class="header c4">
       <?php print_string('a', 'block_courseshop') ?>
   </td>
   <td class="header c5">
       <?php print_string('b', 'block_courseshop') ?>
   </td>
   <td class="header c6">
       <?php print_string('c', 'block_courseshop') ?>
   </td>
   <td class="header c7">
   &nbsp;
   </td>
</tr>
<?php
		    foreach ($shippings as $portlet){
		        include ($CFG->dirroot.'/blocks/courseshop/lib/shippingAdminLine.portlet.php');
		    }
		}
	}

	$addshippingstr = get_string('addshipping', 'block_courseshop');
?>
</table>
</form>
<table width="100%">
   <tr>
      <td align="right">
      	<?php
         	echo "<a href=\"edit_shipping.php?cmd=add&id=$id&pinned=$pinned&zoneid={$zoneid}\">$addshippingstr</a>";
         ?>
      </td>
   </tr>
</table>
<br/>
	<?php 
	echo '<center>';
	$options['id'] = $id;
	$options['pinned'] = $pinned;	
	print_single_button($CFG->wwwroot.'/blocks/courseshop/shipzones/index.php', $options, get_string('backtoadmin', 'block_courseshop')); 
	echo '</center>';
	?>
<br/>

<?php
print_footer();
?>
