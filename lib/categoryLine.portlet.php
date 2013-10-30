

<tr class="category" valign="top" >
   <td class="cell c2">
      <?php echo $portlet->name ?>
   </td>
   <td class="cell c3">
       <?php echo $portlet->description ?>
   </td>
    <td width="5%">
        <?php 
        if ($portlet->visible) {
        	$pixurl = $CFG->pixpath.'/t/hide.gif';
        } else {
        	$pixurl = $CFG->pixpath.'/t/show.gif';
        }
        echo "<img src=\"$pixurl\" />";
        ?>
    </td>   
   	<td align="right" class="cell lastcol">   
      	<a class="activeLink" href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/category/edit_category.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;categoryid={$portlet->id}&cmd=updatecategory"; ?>"><img src="<?php echo $CFG->pixpath.'/t/edit.gif' ?>" border="0"></a>
	  	<a class="activeLink" href="<?php echo $portlet->url."&categoryid={$portlet->id}&cmd=deletecategory"; ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0"></a>
		<?php
		if ($portlet->sortorder > 1){
      		echo " <a class=\"activeLink\" href=\"{$portlet->url}&categoryid={$portlet->id}&cmd=up\"><img src=\"{$CFG->pixpath}/t/down.gif\" border=\"0\" /></a>";
      	}
		if ($portlet->sortorder < $portlet->maxorder){
      		echo " <a class=\"activeLink\" href=\"{$portlet->url}&categoryid={$portlet->id}&cmd=down\"><img src=\"{$CFG->pixpath}/t/up.gif\" border=\"0\" /></a>";
      	}
      	?>
   	</td>
</tr>
