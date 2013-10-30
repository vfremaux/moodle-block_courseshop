<?php
	$portlet->TTCprice = courseshop_calculate_taxed($portlet->price1, $portlet->taxcode);
?>
<table class="courseshop-article" width="100%">
   	<tr valign="top">
      	<td width="180" rowspan="2" class="courseshop-productpix" valign="middle" align="center">
         	<img src="<?php echo $portlet->image ?>" border="0"><br>
        </td>
        <td width="*" class="courseshop-producttitle">
            <?php echo $portlet->name ?>
        </td>
    </tr>
    <tr valign="top">
    	<td class="courseshop-productcontent">
         
        	<?php echo $portlet->description ?>
         
        	<p><strong><?php print_string('ref', 'block_courseshop') ?> : <?php echo $portlet->code ?> - </strong> 
         	<?php print_string('puttc', 'block_courseshop') ?> = <b>
         	<?php echo sprintf("%.2f", round($portlet->TTCprice, 2)) ?> <?php echo $portlet->currency ?></b><br />
        	<input type="button" name="" value="<?php print_string('buy', 'block_courseshop') ?>" onclick="addOneUnit('<?php echo $portlet->shortname ?>', '<?php echo $portlet->code ?>', <?php echo $portlet->TTCprice ?>, '<?php echo $portlet->maxdeliveryquant ?>')">
        	<span id="bag_<?php echo $portlet->shortname ?>"></span>
      	</td>
   	</tr>
</table>
