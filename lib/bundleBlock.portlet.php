<table class="article">
   <tr>
        <td width="200" class="pad10">
            <img src="<?php echo $portlet->thumb ?>" vspace="10" border="0"><br>
<?php
if ($portlet->image != ''){
?>
            <a href="Javascript:openPopup('photo.php?img=<?php echo $portlet->image ?>')"><?php echo get_string('viewlarger', 'block_courseshop') ?></a>
<?php
}
?>
        </td>
        <td align="left" width="350" class="pad10">
         <p><em><?php echo $portlet->name ?></em><br>
         
         <?php echo $portlet->description ?>
         
        </td>
   </tr>
<?php
$TTCprice = 0;
foreach($portlet->set as $aBundleElement){
	$aBundleElement->TTCprice = courseshop_calculate_taxed($aBundleElement->price1, $aBundleElement->taxcode);
    $TTCprice += $aBundleElement->TTCprice;
?>
   <tr>
      <td width="200" class="pad10">
          &nbsp;
      </td>
      <td width="350" class="pad10">
        <table>
            <tr>
                <td valign="middle">
<?php
    if ($aBundleElement->thumb != ''){
?>
         <img src="<?php echo $aBundleElement->thumb ?>" vspace="3" border="0" width="30" align="left">
<?php
    }
?>
                </td>
                <td>
        <p><strong><?php echo get_string('Ref', 'block_courseshop') ?> : <?php echo $aBundleElement->code ?> </strong><?php
    if ($aBundleElement->showsNameInSet == 'Y'){
?>
         - <?php echo $aBundleElement->name ?>
<?php
    }
?>
                </td>
           </tr>
<?php
    if ($aBundleElement->showsDescriptionInSet == 'Y'){
?>
         <tr>
            <td>&nbsp;</td>
            <td>
              <?php echo $aBundleElement->description ?>
            </td>
         </tr>
<?php
    }
?>
        </table>
      </td>
   </tr>
<?php
}
?>
    <tr>
        <td width="200" class="pad10">
           &nbsp;
        </td>
        <td width="350">
            <strong><?php print_string('ref', 'block_courseshop') ?> : <?php echo $portlet->code ?> - </strong>
            <?php print_string('puttc', 'block_courseshop') ?> = <b><?php echo $TTCprice.' '. $portlet->currency ?> </b><br>
	        <input type="button" name="" value="<?php print_string('buy', 'block_courseshop') ?>" onclick="addOneUnit('<?php echo $portlet->shortname ?>', <?php echo $portlet->TTCprice ?>, '<?php echo $portlet->maxdeliveryquant ?>')">
	        <span id="bag_<?php echo $portlet->shortname ?>"></span>
        </td>
    </tr>
</table>
