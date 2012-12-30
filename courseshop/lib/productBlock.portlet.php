<?php
	$portlet->TTCprice = courseshop_calculate_taxed($portlet->price1, $portlet->taxcode);
?>
<table class="article" width="100%">
   <tr valign="top">
      <td width="180" rowspan="2" class="productpix" valign="middle" align="center">
         <img src="<?php echo $portlet->thumb ?>" border="0"><br>
<?php
if ($portlet->image != ''){
?>
         <!-- a href="Javascript:openImage('<?php echo $portlet->image ?>', '<?php echo $CFG->wwwroot ?>/')"><?php print_string('showbigger', 'block_courseshop') ?></a -->
<?php
}
?>
        </td>
        <td width="*" class="producttitle">
            <?php echo $portlet->name ?>
        </td>
    </tr>
    <tr valign="top">
        <td class="productcontent">
         
         <?php echo $portlet->description ?>
         
         <p><strong><?php print_string('ref', 'block_courseshop') ?> : <?php echo $portlet->code ?> - </strong> 
         	<?php print_string('puttc', 'block_courseshop') ?> = <b>
         <?php echo sprintf("%.2f", round($portlet->TTCprice, 2)) ?> <?php echo $portlet->currency ?></b><br />
         <input type="button" name="" value="<?php print_string('buy', 'block_courseshop') ?>" onclick="addOneUnit('<?php echo $portlet->shortname ?>', <?php echo $portlet->TTCprice ?>, '<?php echo $portlet->maxdeliveryquant ?>')">
         <span id="bag_<?php echo $portlet->shortname ?>"></span>
      </td>
   </tr>
</table>
