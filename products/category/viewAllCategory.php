<?php
	
    // Security
    if (!defined('MOODLE_INTERNAL')) die("You are not authorized to run this file directly");

    $cmd = optional_param('cmd','',PARAM_TEXT);
    
 
    if ($cmd != ''){
       include ($CFG->dirroot.'/blocks/courseshop/products/category/viewAllCategory.controller.php');
    }

    $order = optional_param('order', 'namecategory', PARAM_TEXT);
    $dir = optional_param('dir', 'ASC', PARAM_TEXT);
    $offset = optional_param('offset', 0, PARAM_INT);

    $url = $CFG->wwwroot."/blocks/courseshop/products/category/view.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;view=viewAllCategory&amp;order={$order}&amp;dir={$dir}";

    $categoryCount = count_records_select('courseshop_catalogcategory', " catalogid = $catalogid AND UPPER(name) NOT LIKE 'test%' "); // eliminate tests
    
    $CFG->block_courseshop_maxcategoryperpage = 20;

    $sql = "
        SELECT 
           c.*
        FROM 
            {$CFG->prefix}courseshop_catalogcategory as c
        WHERE
       		catalogid = {$catalogid}
        LIMIT
           {$offset}, {$CFG->block_courseshop_maxcategoryperpage}
    ";
    print_heading(get_string('category', 'block_courseshop'), 'center', 1);

    if (!$categories = get_records_sql($sql)){    
	    print_box_start();
	    echo get_string('nocats', 'block_courseshop');
        ?>
		<table width="100%" class="generaltable">
			<tr>
				<td align="right">
			        <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/category/edit_category.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}" ?>"><?php print_string('newcategory', 'block_courseshop') ?></a>
			    </td>
			</tr>
		</table>
		<?php
	    print_box_end();
	} else {
    
    
?>
<table width="100%" class="generaltable">
<tr valign="top" >
   <th align="left" class="header c2">
       <?php print_string('catname', 'block_courseshop') ?>
   </th>
   <th align="left" class="header c3">
       <?php print_string('catdescription', 'block_courseshop') ?>
   </th>
   <th align="left" class="header lastcol">
   &nbsp;
   </th>
</tr>
<?php
	if (!empty($categories)){
		foreach ($categories as $portlet){
			//if ($portlet->billCount == 0) $emptyAccounts++;
			$portlet->url = $url;
			include $CFG->dirroot.'/blocks/courseshop/lib/categoryLine.portlet.php';
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
$portlet->total = $categoryCount;
$portlet->pageSize = $CFG->block_courseshop_maxcategoryperpage;
include($CFG->dirroot.'/blocks/courseshop/lib/pagingResults.portlet.php'); 
?>

      </td>
      <td align="right">
         <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/category/edit_category.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}" ?>"><?php print_string('newcategory', 'block_courseshop') ?></a>
      </td>
   </tr>
</table>
<br>
<br>

<?php
}
?>