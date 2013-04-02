<tr class="">
  	<td colspan="10">
		<table width="100%">
			<tr valign="top">
			  	<td width="30" class="<?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
					<!-- input type="checkbox" name="items[]" value="<?php echo $portlet->id ?>" / -->
			  	</td>
			  	<td class="<?php echo (@$portlet->masterrecord == 0) ? "" : "engraved" ; ?>">
			     	<img src="<?php echo $portlet->thumb ?>" vspace="10" border="0" height="50">
			  	</td>
			  	<td class="name <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
			     	<b><?php echo $portlet->code ?></b><br/>
			     	(<?php echo $portlet->shortname ?>)
			 	</td>
			  	<td class="name <?php echo (@$portlet->masterrecord == 0) ? '' : 'slaved' ; ?>">
			      	<?php echo $portlet->name ?>
			  	</td>
			   	<td class="list" colspan="6">
					<?php
					if (count($portlet->set) == 0){
			    		echo get_string('noproductinset', 'block_courseshop');
					} else {
					?>
     				<table width="98%">
        				<tr>
				            <td class="columnHead font10">
				                <?php print_string('image', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php print_string('code', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php print_string('label', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php print_string('price', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php print_string('dispo', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10" width="10">
				                <?php print_string('controls', 'block_courseshop') ?>
				            </td>
				        </tr>
						<?php
					    foreach($portlet->set as $subportlet){
			                include $CFG->dirroot."/blocks/courseshop/lib/setAdminLine.portlet.php";
					    }
						?>
    				</table>
					<?php
					}
					?>
	  			</td>
	  			<td align="right" width="10" class="setcontrols">
	     			<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/edit_set.php?id={$id}&amp;pinned={$pinned}&amp;setid={$portlet->id}" ?>"><img src="<?php echo $CFG->pixpath.'/t/edit.gif' ?>" border="0" title="<?php print_string('editset', 'block_courseshop') ?>"></a><br/>
	     			<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;view=viewAllProducts&amp;cmd=deleteset&amp;setid={$portlet->id}" ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0" title="<?php print_string('removealllinkedproducts', 'block_courseshop') ?>"></a><br/>
	     			<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;view=viewAllProducts&amp;cmd=unlinkset&amp;setid={$portlet->id}" ?>"><img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/unlink.gif' ?>" border="0" title="<?php print_string('removeset', 'block_courseshop') ?>"></a><br/>
					<?php
					if ($portlet->catalog->isslave){
					    if ($portlet->masterrecord == 1){
					?>
					     <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;view=viewAllProducts&amp;cmd=makecopy&productid={$portlet->id}" ?>"><img src="images/icons/copy.gif" border="0" title="<?php print_string('createlocalversion', 'block_courseshop') ?>"></a>
					<?php
	    			} else {
					?>
					     <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/view.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;view=viewAllProducts&amp;cmd=freecopy&productid={$portlet->id}" ?>"><img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/uncopy.gif' ?>" border="0" title="<?php print_string('removeleocalversion', 'block_courseshop') ?>"></a>
					<?php
					    }
					}
					?>
		  		</td>
			</tr>
		</table>
	</td>
</tr>