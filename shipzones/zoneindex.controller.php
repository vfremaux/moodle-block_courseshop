<?php

if ($cmd == 'deleteItems'){
    $shippingids = optional_param('items', '', PARAM_INT);
    
    if (!empty($shippingids)){
    	foreach($shippingids as $sid)
	    
	    delete_records('courseshop_catalogshipping', 'id', $sid);
	}
}
?>