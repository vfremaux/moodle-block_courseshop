<?php

// Security
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

if ($cmd == 'addcatalog'){
} elseif ($cmd == 'updatecatalog'){
    $catalog->id = required_param('item', PARAM_INT);
    $catalog->name = required_param('name', PARAM_TEXT);
    $catalog->description = addslashes(optional_param('description', '', PARAM_CLEANHTML));
    $catalog->linked = optional_param('linked', 'free', PARAM_ALPHA);
    $catalog->groupid = optional_param('groupid', 0, PARAM_INT);
    
    update_record('courseshop_catalog', $catalog);

    if ($catalog->linked != 'free'){
        if ($catalog->linked == 'master') 
            $groupidvalue = $id;
        elseif ($catalog->linked == 'slave') 
            $groupidvalue = $catalog->groupid;
        
        $sql = "
           UPDATE 
              {$CFG->prefix}courseshop_catalog
           SET
              groupid = '{$groupidvalue}'
           WHERE
              id = '{$catalog->id}'
        ";
        execute_sql($sql);
    }

    redirect($CFG->wwwroot.'/blocks/courseshop/index.php');
}
if ($cmd == 'deletecatalog'){
    $catalogid = required_param('catalogid', PARAM_INT);
    $catalogidlist = $catalogid;
    
    // if master catalog, must delete all slaves
    include "classes/Catalog.class.php";
    $theCatalog = new Catalog($catalogid);
    if ($theCatalog->ismaster){
        $catalogids = get_records_select_menu('courseshop_catalog', " groupid = '{$catalogid}' AND id != groupid ", '', 'id,id');
        $catalogidlist = implode("','", array_values($catalogids));
    }
    // deletes products entries in candidate catalogs
    delete_records_select('courseshop_catalogitem', " id IN ('$catalogidlist') ");
    delete_records_select('courseshop_catalogcategory', " id IN ('$catalogidlist') ");
    delete_records_select('courseshop_catalog', " id IN ('$catalogidlist') ");

}
?>