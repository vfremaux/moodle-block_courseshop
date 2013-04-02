<?php
// $_CFG['debug'] = 'DB';

$catalogId = captureInSession('catalogId', 1);
if ($cmd == 'search'){
    $error = false;
    $by = get('by', 'REQUEST');
    $code = get_safe('code', 'REQUEST');
    $shortname = get_safe('shortname', 'REQUEST');
    $name = get_safe('name', 'REQUEST');
    switch($by){
        case "code":
           $whereClause = "   code = '{$code}' ";
           break;
        case "shortname":
           $whereClause = "   shortname LIKE '{$shortname}%' ";
           break;
        case "name":
           $whereClause = "   UPPER(name) LIKE UPPER('{$name}') ";
           break;
        default:
           $error = true;
    }
    if (!$error){
        $query = "
           SELECT
              ci.*
           FROM
              {$CFG->prefix}courseshop_catalogitem as ci
           WHERE
              ci.catalogId = '$catalogId' AND
              $whereClause
        ";
        $results = mysql_execute($query);
        if (mysql_num_rows($results) == 1){
            $aProduct = mysql_fetch_object($results);
            $productId = $aProduct->Id;
            // one only result. Switch directely to {level1}/products/viewProduct with adequate Id.
            header("Location: spage.php?p={$_CXT->level1}/products/viewProduct&productId=$productId");
        }
        elseif (mysql_num_rows($results) > 1){
            $products = array();
            while($aProduct = mysql_fetch_object($results)){
                $products[] = $aProduct;
            }
        }
    }
}
?>