<?php
/*
User object is provided for direct Object Mapping of the _user database model
*/
class Catalog {
    var $id;
    var $name;
    var $description;
    var $groupid;
    var $isslave;
    var $ismaster;
	
    function Catalog($ID = ''){
        global $CFG;
   		
		if(!empty($ID) || $ID === 0) {
            if ($aCatalog = get_record('courseshop_catalog', 'id', $ID, '', '', '', '', '*,id = groupid as ismaster, groupid IS NOT NULL AND groupid != 0 AND id != groupid as isslave')){
                $this->id = $aCatalog->id;
                $this->name = $aCatalog->name;
                $this->description = $aCatalog->description;
                $this->isslave = $aCatalog->isslave;
                $this->ismaster = $aCatalog->ismaster;
                $this->groupid = $aCatalog->groupid;
            } else {
                error("Error fetching catalog $ID");
            }
        } else {
			$this->name = get_string('newcatalog', 'block_courseshop');
			$this->description = '';
			$this->isslave = 0;
			$this->ismaster = $aCatalog->ismaster;
			$this->groupid = $aCatalog->groupid;
        } 
    }
   
    function getGroupMembers($catalogid){
        global $CFG;

        $members = array();
        $sql = "
            SELECT 
                id,
                id = groupid as ismaster
            FROM 
                {$CFG->prefix}courseshop_catalog
            WHERE
                groupid IS NOT NULL AND groupid = '$catalogid'
            ORDER BY
                ismaster DESC
        ";
        $members = get_records_sql($sql);
        if (count($members) == 0) $members[] = $catalogid;
        return $members;
    }
}
?>