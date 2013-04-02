<?php

// Security
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

if ($cmd == 'addproduct'){
    $catalogitem->code = required_param('code', PARAM_TEXT);
    $catalogitem->catalogid = required_param('catalogid', PARAM_INT);
    $catalogitem->shortname = required_param('shortname', PARAM_TEXT);
    $catalogitem->name = required_param('name', PARAM_TEXT);
    $catalogitem->description = addslashes(optional_param('description', '', PARAM_CLEANHTML));
    $catalogitem->price1 = 0 + optional_param('price1', 0, PARAM_NUMBER);
    $catalogitem->price2 = 0 + optional_param('price2', 0, PARAM_NUMBER);
    $catalogitem->price3 = 0 + optional_param('price3', 0, PARAM_NUMBER);
    $catalogitem->taxcode = optional_param('taxcode', '', PARAM_TEXT);
    if ($catalogitem->taxcode == '') $catalogitem->taxcode = 'NULL';
    $catalogitem->stock = 0 + optional_param('stock', 0, PARAM_INT);
    $catalogitem->catalogitem->sold = 0 + optional_param('sold', 0, PARAM_INT);
    $catalogitem->categoryid = optional_param('categoryid', 'NULL', PARAM_TEXT);
    if ($catalogitem->categoryid == '') $catalogitem->categoryid = 'NULL';
    $catalogitem->status = required_param('status', PARAM_ALPHA);
    $catalogitem->leafleturl = optional_param('leafleturl', '', PARAM_TEXT);
    $catalogitem->image = optional_param('image', '', PARAM_TEXT);
    $catalogitem->thumb = optional_param('thumb', '', PARAM_TEXT);
    $catalogitem->notes = addslashes(optional_param('notes', '', PARAM_CLEANHTML));
    $catalogitem->setid = optional_param('setid', 0, PARAM_INT);
    $catalogitem->showsnameinset = optional_param('showsnameinset', 0, PARAM_INT);
    $catalogitem->showsdescriptioninset = optional_param('showsdescriptioninset', 0, PARAM_INT);
    $catalogitem->isset = optional_param('isset', 0, PARAM_INT);
    $catalogitem->enablehandler = optional_param('enablehandler', 0, PARAM_BOOL);
    
    if (!$newid = insert_record('courseshop_catalogitem', $catalogitem)){
        
        error("could not record product");
    }

    // we have items in the set. update relevant products    
    $productsinset = optional_param('productsinset', array(), PARAM_INT);
    if (is_array($productsinset)){
        foreach($productsinset as $productid){
            $record->id = $productid;
            $record->setid = $newid;
            update_record('courseshop_catalogitem', $record);
        }
    }

    // if slave catalogwe must insert a master copy
    if ($theCatalog->isslave){
        $catalogitem->catalogid = $theCatalog->groupid;        
        insert_record('courseshop_catalogitem', $catalogitem);
    }
}

if ($cmd == 'updateproduct'){
    // update only current copy, wether slave or master.
    $catalogitem->id = required_param('productid', PARAM_INT);
    $catalogitem->catalogid = required_param('catalogid', PARAM_INT);
    $catalogitem->code = required_param('code', PARAM_TEXT);
    $catalogitem->shortname = required_param('shortname', PARAM_TEXT);
    $catalogitem->name = required_param('name', PARAM_TEXT);
    $catalogitem->description = addslashes(optional_param('description', '', PARAM_CLEANHTML));
    $catalogitem->price1 = 0 + optional_param('price1', 0, PARAM_NUMBER);
    $catalogitem->price2 = 0 + optional_param('price2', 0, PARAM_NUMBER);
    $catalogitem->price3 = 0 + optional_param('price3', 0, PARAM_NUMBER);
    $catalogitem->taxcode = optional_param('taxcode', '', PARAM_TEXT);
    if ($catalogitem->taxcode == '') $catalogitem->taxcode = 'NULL';
    $catalogitem->stock = 0 + optional_param('stock', 0, PARAM_INT);
    $catalogitem->catalogitem->sold = 0 + optional_param('sold', 0, PARAM_INT);
    $catalogitem->categoryid = optional_param('categoryid', 'NULL', PARAM_TEXT);
    if ($catalogitem->categoryid == '') $catalogitem->categoryid = 'NULL';
    $catalogitem->status = required_param('status', PARAM_ALPHA);
    $catalogitem->leafleturl = optional_param('leafleturl', '', PARAM_TEXT);
    $catalogitem->image = optional_param('image', '', PARAM_TEXT);
    $catalogitem->thumb = optional_param('thumb', '', PARAM_TEXT);
    $catalogitem->notes = addslashes(optional_param('notes', '', PARAM_CLEANHTML));
    $catalogitem->setid = optional_param('setid', 0, PARAM_INT);
    $catalogitem->showsnameinset = optional_param('showsnameinset', 0, PARAM_INT);
    $catalogitem->showsdescriptionnset = optional_param('showsdescriptioninset', 0, PARAM_INT);
    $catalogitem->isset = optional_param('isset', 0, PARAM_INT);
    $catalogitem->enablehandler = optional_param('enablehandler', 0, PARAM_BOOL);
    
    if (!update_record('courseshop_catalogitem', $catalogitem)){
        
        error("could not record product");
    }
}

if ($cmd == 'deleteItems'){
    $productid = required_param('items', PARAM_INT);
    $productidlist = $productid; // for unity operations
	delete_records_select('courseshop_catalogitem', " id IN ('$productidlist') ");
	
    
    // catalog is not independant, delete in all group (by getting product code)
    if ($theCatalog->groupid != ''){
        // get product code so that all clones Id can be found
        $theCode = get_field('courseshop_catalogitem', 'code', 'id', $productid);

		$groupid = $theCatalog->groupid;

        $sql = "
           SELECT 
              ci.id,
              ci.id
           FROM
              {$CFG->prefix}courseshop_catalogitem as ci,
              {$CFG->prefix}courseshop_catalog as c
           WHERE
              c.id = ci.catalogid AND
              ci.code = '$theCode' AND
              c.groupid = '$groupid'
        ";
        if ($products = get_records_sql_menu($sql)){
            $productidlist = implode("','", array_values($products));
        } else {
            $productidlist = '';
        }
    }
    
    $relatedids = implode("','", array_keys($theCatalog->getGroupMembers($theCatalog->groupid))); // this is as a security
    delete_records_select('courseshop_catalogitem', " id IN ('$productidlist') AND catalogid IN ('$relatedids') ");
};

if ($cmd == 'deleteset'){
    $setid = required_param('setid', PARAM_INT);

    // if catalog is not independant, all copies should be removed
    $setidlist = '';
    if ($theCatalog->groupid != ''){

        // get setcode by Id
        get_record('courseshop_catalogitem', 'id', $setid);

        $query = "
           SELECT 
              Id
           FROM
              {$CFG->prefix}
           WHERE
        ";
        $results = mysql_execute($query);
        while($aRow = mysql_fetch_row($results)){
            $setids = get_records_select_menu('courseshop_catalogitem', " code = '$theCode' AND id != '$setId' ", 'id,id');
            $setidlist = implode("','", $setids); 
        }
        // setids is now the list of all clone sets
    }
        
    // delete all attached products in cloned sets
    delete_records_select('courseshop_catalogitem', " id IN ('$setidlist') ");
    delete_records_select('courseshop_catalogitem', 'id', $setid);

}

if ($cmd == 'unlinkset'){
    $setid = required_param('setid', PARAM_INT);

    // change all attached products
    $sql = "
       UPDATE
         {$CFG->prefix}courseshop_catalogitem
       SET 
         setid = 0
       WHERE
         setid = '$setid'
    ";
    execute_sql($sql);
}

if ($cmd == 'unlinkproduct'){
    $productid = required_param('productid', PARAM_INT);

    // change all attached products
    $sql = "
       UPDATE
         {$CFG->prefix}courseshop_catalogitem
       SET 
           setid = 0
       WHERE
         id = '$productid'
    ";
   	execute_sql($sql);
}

if ($cmd == 'makecopy'){
    $productid = required_param('productid');
    
    // get source product in master catalog
    $sourceid = get_field('courseshop_catalog', 'groupid', 'id', $catalogid);
    
    // make record copy
    $sql = "
       INSERT INTO 
          {$CFG->prefix}courseshop_catalogitem(
            catalogid, 
            code,
            shortname,
            name,
            description,
            price1,
            price2,
            price3,
            taxcode,
            stock,
            sold,
            categoryid,
            status,
            leafleturl,
            image,
            thumb,
            notes,
            isSet,
            setid,
            showsdescriptioninset,
            showsnameinset 
          )
       SELECT
          '$catalogid', 
          code,
          shortname,
          name,
          description,
          price1,
          price2,
          price3,
          taxcode,
          stock,
          sold,
          categoryid,
          status,
          leafleturl,
          image,
          thumb,
          notes,
          isSet,
          setid,
          showsdescriptioninset,
          showsnameinset
       FROM
          {$CFG->prefix}courseshop_catalogitem
       WHERE
          id = '$productid'
    ";
    $result = execute_sql($sql);
}

if ($cmd == 'freecopy'){
    $productid = required_param('productid', PARAM_INT);
    delete_records_select('courseshop_catalogitem', " id = '$productid' AND catalogid = '$catalogid' ");
}
?>