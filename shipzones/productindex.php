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

	$sql = "
		SELECT 
			ci.id,
			ci.code,
			ci.shortname,
			ci.name
		FROM
			{$CFG->prefix}courseshop_catalogshipping cs,
			{$CFG->pre,fix}courseshop_catalogitem ci
		WHERE
			ci.isset = 0 AND
			ci.catalogid = $catalogid AND
			ci.code = cs.productcode
	";
	if ($allproductswithshipping = get_records_sql($sql)){
		foreach($allproductswithshipping as $p){
			$productoptions[$p->id] = '['.$p->shortname.'] '.$p->name;
		}
		choose_from_menu($productoptions, 'productid', $productid, 'choose');
	} else {
		notify(get_string('noshippings', 'block_courseshop'));
	}

	$sql = "
		SELECT 
			cs.*
		FROM
			{$CFG->prefix}courseshop_catalogshipping cs,
			{$CFG->prefix}courseshop_catalogitem ci
		WHERE
			ci.isset = 0 AND
			ci.code = cs.productcode AND
			ci.id = $productid
	";
	if (!$productshippingzones = get_records_sql($sql)){
		notify(get_string('noshippedproducts', 'block_courseshop'));
	} else {
		echo '<table width="100%">';
		foreach($productshippingzones as $portlet){
			include $CFG->dirroot.'/blocks/courseshop/lib/shippingZoneAdminLine.portlet.php';
		}
		echo '</table>';
	}
	
	print_footer();

?>