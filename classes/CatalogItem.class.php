<?php
/*
CatalogItem object is provided for direct Object Mapping of the _catalogitem database model
*/
class CatalogItem {
    var $Id;
    var $catalogId;
    var $code;
    var $shortname;
    var $name;
    var $description;
    var $price1;
    var $price2;
    var $price3;
    var $taxCode;
    var $stock;
    var $sold;
    var $categoryId;
    var $status;
    var $leafletUrl;
    var $image;
    var $thumb;
    var $notes;
    var $isSet;
    var $setId;
    var $showsDescriptionInSet;
    var $showsNameInSet;
    var $isOptionInBundle;
    var $bundleShowsContent;
    var $productionData;
    var $setContent;
    
    function CatalogItem($ID = ''){
        global $_CFG;
        
        if ($ID == ''){
        }
        else{
            $query = "
                SELECT 
                    *
                FROM
                    " . $CFG->prefix . "catalogitem
                WHERE
                    id = {$ID}
            ";
            $result = mysql_execute($query);
            $aCatalogItem = mysql_fetch_object($result);
            $this->id = $aCatalogItem->id;
            $this->catalogid = $aCatalogItem->catalogid;
            $this->code = $aCatalogItem->code;
            $this->shortname = $aCatalogItem->shortname;
            $this->name = $aCatalogItem->name;
            $this->description = $aCatalogItem->description;
            $this->price1 = $aCatalogItem->price1;
            $this->price2 = $aCatalogItem->price2;
            $this->price3 = $aCatalogItem->price3;
            $this->taxcode = $aCatalogItem->taxCode;
            $this->stock = $aCatalogItem->stock;
            $this->sold = $aCatalogItem->sold;
            $this->categoryid = $aCatalogItem->categoryId;
            $this->status = $aCatalogItem->status;
            $this->leafletUrl = $aCatalogItem->leafletUrl;
            $this->image = $aCatalogItem->image;
            $this->thumb = $aCatalogItem->thumb;
            $this->notes = $aCatalogItem->notes;
            $this->isSet = $aCatalogItem->isSet;
            $this->setid = $aCatalogItem->setId;
            $this->showsDescriptionInSet = $aCatalogItem->showsdescriptioninset;
            $this->showsNameinset = $aCatalogItem->showsnameinset;
            $this->isOptionInBundle = $aCatalogItem->isOptionInBundle;
            $this->bundleShowsContent = $aCatalogItem->bundleShowsContent;
            $this->productionData = $aCatalogItem->productionData;
            if ($this->isSet != 'N'){
                $this->setContent = array();
                $this->loadSetContent();
            }
        }
    }
    
    function loadSetContent(){
    }
}
?>