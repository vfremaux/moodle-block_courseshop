<?php

if ($cmd == 'deleteItems'){
    $zoneids = optional_params('items', '', PARAM_INT);
    
    if (!empty($zoneids)){
	    $zoneidlist = implode(",", $zoneids);
	    
	    delete_records_list('courseshop_catalogshipzone', 'zoneid', $zoneidlist);
	}
}
?>