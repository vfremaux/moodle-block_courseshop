<?php

$cmd = optional_param('cmd', '', PARAM_TEXT);

if ($cmd != ''){
    include_once $CFG->dirroot.'/blocks/courseshop/customer/search.controller.php';
}

$sql = "
   SELECT 
        COUNT(*)
   FROM
        {$CFG->prefix}courseshop_bill
";

$billCount = count_records_sql($sql);

?>
<script type="text/javascript">
function searchBy(criteria){
    document.search.by.value = criteria;
    document.search.submit();
}
</script>
<h1><?php print_string('searchbycustomer', 'block_courseshop') ?></h1>

<?php
if ($cmd == 'search'){
    echo get_string('searchCustomerFailed', 'block_courseshop');
}
?>

<?php
if (isset($customers)){
?>
    <h2><?php print_string('results', 'block_courseshop') ?></h2>
    <p><?php print_string('chooseinmultipleressults', 'block_courseshop') ?>:
    <table>
<?php
    foreach($customers as $portlet){
        include $CFG->dirroot.'/block/courseshop/lib/bill/shortCustomerLine.php';
    }
?>
    </table>
    <h2><?php print_string('search', 'block_courseshop') ?></h2>
<?php
}
?>

<form name="search" action="<?php echo $CFG->wwwroot.'/blocks/courseshop/customer/search.php' ?>" method="get">
<input type="hidden" name="by" value="" />
<input type="hidden" name="cmd" value="search" />
<table>
<?php
if ($billCount == 0) {
?>
<tr>
   <td colspan="4" class="customerRow">
<?php
   print_string('nocustomers', 'block_courseshop');
?>
   </td>
</tr>
<?php
}
else {
?>
    <tr>
        <td valign="top">
           <h2><?php print_string('searchby', 'block_courseshop') ?></h2>
        </td>
    </tr>
    <tr>
        <td align="center">
            <h3><span class="disabled"><?php print_string('or') ?></span> &nbsp;&nbsp;<?php print_string('customername', 'block_courseshop') ?></h3>
            <input type="text" name="lastname" width="40" maxlength="64"><br>
            <p class="smalltext"><?php print_string('customernameonbill', 'block_courseshop') ?>.
        </td>
    </tr>
   <tr>
      <td align="right">
            <a href="Javascript:searchBy('lastname');"><?php print_string('search') ?></a>
      </td>
   </tr>
    <tr>
        <td align="center">
            <h3><span class="disabled"><?php print_string('or') ?></span> &nbsp;&nbsp;<?php print_string('firstname') ?></h3>
            <input type="text" name="firstname" width="40" maxlength="60"><br>
            <p class="smalltext"><?php echo  getTransaction('customerfirstnameonbill', 'block_courseshop') ?>.
        </td>
    </tr>
   <tr>
      <td align="right">
         <a href="Javascript:searchBy('firstname');"><?php print_string('Rechercher') ?></a>
      </td>
   </tr>
    <tr>
        <td align="center">
            <h3><span class="disabled"><?php print_string('or') ?></span> &nbsp;&nbsp;<?php print_string('billnumber', 'block_courseshop') ?></h3>
            <input type="text" name="billid" width="5" maxlength="10"><br>
            <p class="smalltext"><?php echo  getTransaction('billnumber', 'block_courseshop') ?>.
        </td>
    </tr>
   <tr>
      <td align="right">
         <a href="Javascript:searchBy('id');"><?php print_string('search') ?></a>
      </td>
   </tr>
    <tr>
        <td align="center">
            <h3><?php print_string('uniquetransactionkey', 'block_courseshop') ?></h3>
            <input type="text" name="billKey" style="font-family : 'Courier New', monospace ; width : 30em"><br>
            <p class="smalltext">
            <?php print_string('typepartofthekeytofind', 'block_courseshop') ?>.<br>
        </td>
    </tr>
   <tr>
      <td align="right">
            <a href="Javascript:searchBy('key');"><?php print_string('search') ?></a>
      </td>
   </tr>
   <tr>
        <td align="center">
            <h3><span class="disabled"><?php print_string('ou') ?></span> &nbsp;&nbsp;<?php print_string('emissiondate', 'block_courseshop') ?></h3>
            <?php print_string('from', 'block_courseshop') ?> <input type="text" name="dateFrom" width="10" maxlength="10"> 
            <?php print_string('hours', 'block_courseshop') ?> <input type="text" name="timeFrom" width="10" maxlength="10"> <?php print_string('till', 'block_courseshop') ?>
            <select name="during">
                <option value="h"><?php print_string('onehour', 'block_courseshop') ?></option>
                <option value="d"><?php print_string('oneday', 'block_courseshop') ?></option>
                <option value="10d" SELECTED ><?php print_string('tendays', 'block_courseshop') ?></option>
                <option value="m"><?php print_string('amonth', 'block_courseshop') ?></option>
                <option value="3m"><?php print_string('threemonths', 'block_courseshop') ?></option>
            </select> <?php print_string('later', 'block_courseshop') ?>.<br>
            <p class="smalltext"><?php print_string('possibletimerange', 'block_courseshop') ?>.
        </td>
    </tr>
   <tr>
      <td align="right">
         <a href="Javascript:searchBy('date');"><?php print_string('search') ?></a>
      </td>
   </tr>
<?php
}
?>
</table>
</form>
<table>
</table>
