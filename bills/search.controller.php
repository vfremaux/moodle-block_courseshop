<?php
if ($cmd == 'search'){
    $error = false;
    $by = get('by', 'REQUEST');
    $billId = get_safe('billId', 'REQUEST');
    $billKey = get_safe('billKey', 'REQUEST');
    $customerName = get_safe('customerName', 'REQUEST');
    $datefrom = get_safe('dateFrom', 'REQUEST') . ' ' . get('timeFrom', 'REQUEST');
    $during = get_safe('during', 'REQUEST');
    switch($by){
        case "id":
           $whereClause = "   Id = '{$billId}' ";
           break;
        case "name":
           $whereClause = "   UPPER(firstname) LIKE '{$customerName}%' ";
           break;
        case "key":
           $whereClause = "   UPPER(transactionId) LIKE '{$billKey}%' ";
           break;
        case "date":
           $whereClause = "   emissionDate > '{$datefrom}' AND TO_DAYS(emissionDate) < TO_DAYS(emissionDate) + $during ";
           break;
        default:
           $error = true;
    }
    if (!$error){
        $query = "
           SELECT
              b.*
           FROM
              {$CFG->prefix}courseshop_bill as b,
              {$CFG->prefix}courseshop_customer as c
           WHERE
              b.userId = c.Id AND
              $whereClause
        ";
        $results = mysql_execute($query);
        if (mysql_num_rows($results) == 1){
            $billRecord = mysql_fetch_object($results);
            $billId = $billRecord->Id;
            // one only result. Switch directely to intranet/bills/viewBill with adequate Id.
            header("Location: spage.php?p=intranet/bills/viewBill&billId=$billId");
        }
        elseif (mysql_num_rows($results) > 1){
            $bills = array();
            while($aBill = mysql_fetch_object($results)){
                $bills[] = $aBill;
            }
        }
    }
    
}
?>