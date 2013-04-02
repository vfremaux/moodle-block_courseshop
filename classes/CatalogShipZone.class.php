<?php
/*
CatalogShipZone object is provided for direct Object Mapping of the _catalogshipzone database model
*/
class CatalogShipZone {
   var $id;
   var $catalogid;
   var $zoneCode;
   var $description;
   
   function CatalogShipZone($ID = ''){
      global $CFG;
      
      if (empty($ID)){
      } else {
            $aCatalogShipZone = get_record('catalogshipzone', 'id', $id);
            $this->id = $aCatalogShipZone->id;
            $this->catalogid = $aCatalogShipZone->catalogid;
            $this->zonecode = $aCatalogShipZone->zonecode;
            $this->description = $aCatalogShipZone->description;
            $this->billscopeamount = $aCatalogShipZone->billscopeamount;
            $this->taxid = $aCatalogShipZone->taxid;
        }
    }
    
    function get_zones($catalogid = ''){
        global $CFG;
        
        if ($catalogid == '')
            $catalogid = $this->catalogid;
        if ($catalogid == '') return null;

        $zones = get_records('catalogshipzone', 'catalogid', $catalogid);
        return array_values($zones);
    }

    function get_zones_hash($catalogid = ''){
        global $CFG;
        
        if ($catalogid == '')
            $catalogid = $this->catalogid;
        if ($catalogid == '') return null;

        $zones = get_records('catalogshipzone', 'catalogid', $catalogid);
        return $zones;
    }
}
?>