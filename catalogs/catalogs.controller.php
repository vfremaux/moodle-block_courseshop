<?php

// Security
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

// Note that other use cases are handled by the edit_catalogue.php script

if ($cmd == 'deletecatalog'){
    $catalogid = required_param('catalogid', PARAM_INT);
    $catalogidlist = $catalogid;
    
    // if master catalog, must delete all slaves
    include "classes/Catalog.class.php";
    $theCatalog = new Catalog($catalogid);
    if ($theCatalog->ismaster){
        $catalogids = get_records_select_menu('courseshop_catalog', " groupid = '{$catalogid}' ", 'id', 'id,name');
        $catalogidlist = implode("','", array_keys($catalogids));
    }
    // deletes products entries in candidate catalogs
    delete_records_select('courseshop_catalogitem', " catalogid IN ('$catalogidlist') ");
    delete_records_select('courseshop_catalogcategory', " catalogid IN ('$catalogidlist') ");
    delete_records_select('courseshop_catalog', " id IN ('$catalogidlist') ");

    return -1;
}

return 0;
?>