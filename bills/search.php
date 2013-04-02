<?php


$cmd = optional_param('cmd', '', PARAM_TEXT);
if ($cmd != ''){
    include $CFG->dirroot . '/blocks/courseshop/bills/search.controller.php';
}

$billCount = count_records('block_courseshop_bill');

?>
<script type="text/javascript">
function searchBy(criteria){
    document.search.by.value = criteria;
    document.search.submit();
}
</script>

<?php 
print_heading(get_string('billsearch', 'block_courseshop'), 'center', 3); 

if (empty($bills)){
    print_string('errorsearchbillfailed');
} else {
?>
    <?php print_heading(get_string('results', 'block_courseshop'), 'center', 2) ?>
    <p><?php print_string('Plusieurs factures sont candidates pour les critères que vous avez utilisés. Vous pouvez choisir parmi ', 'block_courseshop') ?>:
    <table>
<?php
    foreach($bills as $portlet){
        include ($CFG->dirroot.'/blocks/courseshop/lib/shortBillLine.php');
    }
?>
    </table>
<?php
}
?>

<form name="search" action="#" method="get">
<input type="hidden" name="id" value="<?php p($blockinstance->id) ?>">
<input type="hidden" name="by" value="">
<input type="hidden" name="cmd" value="search">
<table>
<?php
if ($billCount == 0) {
?>
<tr>
   <td colspan="4" class="billRow">
   <?php print_string('nobills', 'block_courseshop') ?>
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
            <?php print_heading(get_string('uniquetransactionkey', 'block_courseshop'), 'center', 3) ?>
            <input type="text" name="billkey" style="font-family : 'Courier New', monospace ; width : 30em"><br/>
            <p class="petit"><?php print_string('Cette clef vous est donnée par un client. vous pouvez tenter une recherche en tapant les quelques premiers chiffres de la clef', 'block_courseshop') ?>.
        </td>
    </tr>
   <tr>
      <td align="right">
         <a href="Javascript:searchBy('key');"><?php print_string('search') ?></a>
      </td>
   </tr>
    <tr>
        <td align="center">
            <?php print_heading(get_string('orclientname', 'block_courseshop'), 'center', 3) ?>
            <input type="text" name="customerName" width="50" maxlength="60"><br>
            <p class="petit"><?php print_string('Le nom du client mentionné sur la facture', 'block_courseshop') ?>.
        </td>
    </tr>
   <tr>
      <td align="right">
         <a href="Javascript:searchBy('name');"><?php print_string('search') ?></a>
      </td>
   </tr>
    <tr>
        <td align="center">
            <?php print_heading(get_string('orbillid', 'block_courseshop'), 'center', 3) ?>
            <input type="text" name="billid" width="5" maxlength="10"><br>
            <p class="petit"><?php print_string('Le numéro d\'ordre de la facture', 'block_courseshop') ?>.
        </td>
    </tr>
   <tr>
      <td align="right">
         <a href="Javascript:searchBy('id');"><?php print_string('search') ?></a>
      </td>
   </tr>
    <tr>
        <td align="center">
            <?php print_heading(get_string('oremissiondate', 'block_courseshop'), 'center', 3) ?>
            <?php print_string('rom (date)', 'block_courseshop') ?> 
            <input type="text" name="dateFrom" width="10" maxlength="10"> 
            <?php print_string('hour', 'block_courseshop') ?> <input type="text" name="timeFrom" width="10" maxlength="10"> <?php print_string('until', 'block_courseshop') ?>
            <select name="during">
                <option value="h"><?php print_string('onehour', 'block_courseshop') ?></option>
                <option value="d"><?php print_string('oneday', 'block_courseshop') ?></option>
                <option value="10d" SELECTED ><?php print_string('tendays', 'block_courseshop') ?></option>
                <option value="m"><?php print_string('onemonth', 'block_courseshop') ?></option>
                <option value="3m"><?php print_string('threemonths', 'block_courseshop') ?></option>
            </select> <?php print_string('after', 'block_courseshop') ?>.<br>
            <p class="petit"><?php print_string('La période probable de la transaction', 'block_couseshop') ?>.
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
