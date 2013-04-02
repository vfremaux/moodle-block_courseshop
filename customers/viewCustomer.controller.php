<?php

// Security
if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

if ($cmd == "sellout"){
   $billId = get('billId', 'REQUEST');
   $query = "
      UPDATE
         {$CFG->prefix}courseshop_bill
      SET
         status = 'SOLDOUT'
      WHERE
         id = '$billid'
   ";
   mysql_execute($query);
}
elseif ($cmd == "unmark"){
   $billid = get('billid', 'REQUEST');
   $query = "
      UPDATE
         {$CFG->prefix}courseshop_bill
      SET
         status = 'PENDING'
      WHERE
         Id = '$billid'
   ";
   mysql_execute($query);
}
?>