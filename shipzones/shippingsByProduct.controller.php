<?php
if ($cmd == 'edit'){
    function keyFilter($a){
        if (ereg("zone_[^_]+$", $a)){
            return true;
        }
        return false;
    }
   
    $zoneKeys = array_keys($_REQUEST);
    $zoneKeys = array_filter($zoneKeys, "keyFilter");
    foreach($zoneKeys as $aKey){
        $aValue = get_safe($aKey, 'REQUEST');
        $aFormula = get_safe($aKey . "_formula", 'REQUEST');
        $a = get_safe($aKey . "_a", 'REQUEST');
        $b = get_safe($aKey . "_b", 'REQUEST');
        $c = get_safe($aKey . "_c", 'REQUEST');
        $aKey = str_replace('zone_', '', $aKey);
        $query = "
            REPLACE INTO
                {$CFG->prefix}courseshop_catalogshipping
            VALUES(
               '{$theProduct->code}',
               '{$aKey}',
               '{$aValue}',
               '{$aFormula}',
               '{$a}',
               '{$b}',
               '{$c}'
            )
        ";
        mysql_execute($query);
   }
}
if ($cmd == 'deleteItems'){
    $items = get('items', 'REQUEST');
    $items = str_replace(",", "','", $items);
    
    $query = "
        DELETE FROM
            {$CFG->prefix}courseshop_catalogshipping
        WHERE
            CONCAT(productCode, '::', zoneId) IN ('$items')
    ";
    mysql_execute($query);
}
?>