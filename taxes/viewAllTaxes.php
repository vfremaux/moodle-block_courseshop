<?php

    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    $cmd = optional_param('cmd','',PARAM_TEXT);
    
 
    if ($cmd != ''){
       include ($CFG->dirroot.'/blocks/courseshop/taxes/viewAllTaxes.controller.php');
    }

    $order = optional_param('order', 'country', PARAM_TEXT);
    $dir = optional_param('dir', 'ASC', PARAM_TEXT);
    $offset = optional_param('offset', 0, PARAM_INT);

    $url = $CFG->wwwroot."/blocks/courseshop/taxes/view.php?view=viewAllTaxes&id={$id}&pinned={$pinned}&order=$order&dir=$dir";

    $taxesCount = count_records_select('courseshop_tax', " UPPER(title) NOT LIKE 'test%' "); // eliminate tests
    
    $CFG->block_courseshop_maxtaxesperpage = 20;

    $sql = "
        SELECT 
           t.*
        FROM 
            {$CFG->prefix}courseshop_tax as t
       
        LIMIT
           {$offset}, {$CFG->block_courseshop_maxtaxesperpage}
    ";
    $taxes = get_records_sql($sql);

    print_heading(get_string('taxes', 'block_courseshop'), 'center', 1);

    if (empty($taxes)) {
        print_box_start();
        echo get_string('notaxes', 'block_courseshop');
        print_box_end();
    
    } else {
?>
<table width="100%" class="generaltable">
<tr valign="top" >
   <th align="left" class="header c1">
       <?php print_string('nametax', 'block_courseshop') ?>
   </th>
   <th align="left" class="header c2">
       <?php print_string('countrytax', 'block_courseshop') ?>
   </th>
   <th align="left" class="header c3">
       <?php print_string('ratiotax', 'block_courseshop') ?>
   </th>
   <th align="left" class="header lastcol">
   <?php print_string('orders', 'block_courseshop') ?>
   </th>
</tr>
<?php
   //$emptyAccounts = 0;
   foreach ($taxes as $portlet){
       //if ($portlet->billCount == 0) $emptyAccounts++;
       $portlet->url = $url;
       include $CFG->dirroot.'/blocks/courseshop/lib/taxLine.portlet.php';
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
$portlet->total = $taxesCount;
$portlet->pageSize = $CFG->block_courseshop_maxtaxesperpage;
include($CFG->dirroot.'/blocks/courseshop/lib/pagingResults.portlet.php'); 
?>

      </td>
      <td align="right">
         <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/taxes/edit_tax.php?id={$id}&amp;pinned={$pinned}" ?>"><?php print_string('newtax', 'block_courseshop') ?></a>
      </td>
   </tr>
</table>
<br>
<br>

