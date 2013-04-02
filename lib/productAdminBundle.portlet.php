<tr class="">
  	<td colspan="10">
		<table width="100%">
            <tr>
			  	<td width="30" class="<?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
			      	<!-- input type="checkbox" name="items[]" value="<?php echo $portlet->id ?>" / -->
			  	</td>
			  	<td class="<?php echo (@$portlet->masterrecord == 0) ? "" : "engraved" ; ?>">
			     	<img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/productBundle.gif' ?>" vspace="10" border="0" height="50" />
			  	</td>
  			  	<td class="name <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
				     <b><?php echo $portlet->code ?></b><br/>
				     (<?php echo $portlet->shortname ?>)
  				</td>
			  	<td class="name productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
			      	<?php echo $portlet->name ?>
			  	</td>
  				<td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
     				<?php echo sprintf("%.2f", round($portlet->price1, 2)) ?><br>
     				(<?php echo $portlet->taxcode ?>)
  				</td>
  				<td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
     				<?php echo sprintf("%.2f", round($portlet->bundleTTCPrice, 2)) ?>
  				</td>
  				<td class="status productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
     				<?php echo get_string($portlet->status, 'block_courseshop') ?>
  				</td>
  				<td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>" align="center">
     				<?php echo $portlet->sold ?>
  				</td>
  				<td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>" align="center">
     				<?php echo $portlet->stock ?>
  				</td>
  				<td align="right">
 					<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/edit_bundle.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;bundleid={$portlet->id}" ?>"><img src="<?php echo $CFG->pixpath.'/t/edit.gif' ?>" border="0" title="<?php echo get_string('deletebundle', 'block_courseshop') ?>"></a><br/>
     				<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/viewwAllProducts.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;cmd=unlinkset&amp;bundleid={$portlet->id}" ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0" title="<?php echo get_string('deletebundle', 'block_courseshop') ?>"></a><br/>
     				<a href="<?php echo $CFG->wwwroot."/blocks/courseshop/products/viewwAllProducts.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;cmd=deleteset&amp;bundleid={$portlet->id}" ?>"><img src="<?php echo $CFG->wwwroot.'/blocks/courseshop/pix/unlink.gif' ?>" border="0" title="<?php echo get_string('deletealllinkedproducts', 'block_courseshop') ?>"></a><br/>
					<?php
					if ($portlet->catalog->isslave){
					    if ($portlet->masterrecord == 1){
					     	echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/products/viewwAllProducts.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;view=viewAllProducts&amp;cmd=makecopy&amp;productid={$portlet->Id}\"><img src=\"{$CFG->wwwroot}/blocks/courseshop/pix/copy.gif\" border=\"0\" title=\"".get_string('createoverride', 'block_courseshop')."\"></a>";
					    } else {
					     	echo "<a href=\"{$CFG->wwwroot}/blocks/courseshop/products/viewwAllProducts.php?id={$id}&amp;pinned={$pinned}&amp;catalogid={$catalogid}&amp;view=viewAllProducts&amp;cmd=freecopy&amp;productid={$portlet->Id}\"><img src=\"{$CFG->wwwroot}/blocks/courseshop/pix/uncopy.gif\" border=\"0\" title=\"".get_string('deleteoverride', 'block_courseshop')."\"></a>";
					    }
					}
					?>
  				</td>
			</tr>
			<tr>
			   	<td colspan="2">
			   		&nbsp;
			   	</td>
			   	<td class="list" colspan="9">
					<?php
					if (count($portlet->set) == 0){
			    		echo get_string('noproductinbundle', 'block_courseshop');
					} else {
					?>
     				<table width="100%">
        				<tr>
            				<td class="columnHead font10">
                				<?php echo get_string('image', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php echo get_string('code', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php echo get_string('label', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php echo get_string('name', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php echo get_string('price', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php echo get_string('TTC', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php echo get_string('availability', 'block_courseshop') ?>
				            </td>
				            <td class="columnHead font10">
				                <?php echo get_string('controls', 'block_courseshop') ?>
				            </td>
				        </tr>
						<?php
						    foreach($portlet->set as $subportlet){
						        include $CFG->dirroot."/blocks/courseshop/lib/bundleAdminLine.portlet.php";
						    }
						?>
    				</table>
				<?php
				}
				?>
 			</td>
		</tr>
	</table>
    </td>
</tr>