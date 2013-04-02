<?php

    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    $cmd = optional_param('cmd', '', PARAM_TEXT);		

    if ($cmd != ''){
       include_once ($CFG->dirroot.'/blocks/courseshop/customers/viewAllCustomers.controller.php');
    }

    $order = optional_param('order', 'lastname', PARAM_TEXT);
    $dir = optional_param('dir', 'ASC', PARAM_TEXT);
    $offset = optional_param('offset', 0, PARAM_INT);

    $url = $CFG->wwwroot."/blocks/courseshop/customers/view.php?view=viewAllCustomers&id=$id&pinned={$pinned}&order={$order}&dir={$dir}";

    $customersCount = count_records_select('courseshop_customer', " UPPER(email) NOT LIKE 'test%' "); // eliminate tests
    
    $CFG->block_courseshop_maxcustomersperpage = 20;

    $sql = "
        SELECT 
           c.*,
           COUNT(b.id) as billCount,
           SUM(b.amount) as totalAccount
        FROM 
           {$CFG->prefix}courseshop_customer as c
        LEFT JOIN 
           {$CFG->prefix}courseshop_bill as b
        ON
           b.userid = c.id
        WHERE
           UPPER(c.email) NOT LIKE 'test%'
        GROUP BY 
           c.id
        ORDER BY
           $order $dir
        LIMIT
           {$offset}, {$CFG->block_courseshop_maxcustomersperpage}
    ";
	
    $customers = get_records_sql($sql);
	
    print_heading(get_string('customeraccounts', 'block_courseshop'), 'center', 1);

    if (empty($customers)) {
        print_box_start();
        echo get_string('nocustomers', 'block_courseshop');
        print_box_end();
    
    } else {
?>
<table width="100%" class="generaltable">
<tr valign="top" >
   <th align="left" class="header c0">
   </th>
   <th align="left" class="header c1">
       <?php print_string('customer', 'block_courseshop') ?>
   </th>
   <th align="left" class="header c2">
       <?php print_string('lastname') ?>
   </th>
   <th align="left" class="header c3">
       <?php print_string('firstname') ?>
   </th>
   <th align="left" class="header c4">
       <?php print_string('purchases', 'block_courseshop') ?>
   </th>
   <th align="right" class="header c5">
      <?php print_string('totalamount', 'block_courseshop') ?>
   </th>
   <th align="left" class="header lastcol">
   &nbsp;
   </th>
</tr>
<?php
   $emptyAccounts = 0;
   foreach ($customers as $portlet){
       if ($portlet->billCount == 0) $emptyAccounts++;
       $portlet->url = $url;
       include $CFG->dirroot.('/blocks/courseshop/lib/customerLine.portlet.php');
   }
}
?>
</table>
</form>
<table width="100%">
   <tr>
      <td align="left">
      </td>
      <td align="center">
<?php
unset($portlet);
$portlet->url = $url;
$portlet->total = $customersCount;
$portlet->pageSize = $CFG->block_courseshop_maxcustomersperpage;
include($CFG->dirroot.'/blocks/courseshop/lib/pagingResults.portlet.php'); 
?>
      </td>
      <td align="right">
         <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/customers/edit_customer.php?id={$id}&pinned={$pinned}"?>"><?php print_string('newcustomeraccount', 'block_courseshop') ?></a>
      </td>
   </tr>
</table>
<br />
<br />
