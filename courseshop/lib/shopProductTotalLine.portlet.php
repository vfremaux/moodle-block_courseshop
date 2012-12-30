<?php
$portlet->TTCprice = courseshop_calculate_taxed($portlet->price1, $portlet->taxcode);
?>
<tr>
  <td class="ordercaptioncell" colspan="3">
     <?php echo $portlet->name ?>
  </td>
</tr>
<tr>
  <td class="ordercell">
     <a href="Javascript:document.forms['caddie'].<?php echo $portlet->shortname ?>.value = 0;calculateLocal(document.forms['caddie'].<?php echo $portlet->shortname ?>, <?php echo $portlet->TTCprice ?>);totalize();"><img src="<?php echo $CFG->pixpath.'/t/delete.gif'?>" /></a>
     <input type="text" name="<?php echo $portlet->shortname ?>" value="<?php echo $portlet->preset ?>" size="6" onChange="calculateLocal(this, <?php echo $portlet->TTCprice ?>);totalize();" class="form_attenuated">
  </td>
  <td class="ordercell">
     <p>x <?php echo sprintf("%.2f", round($portlet->TTCprice, 2)) ?> <?php echo $portlet->currency ?> :  
  </td>
  <td class="ordercell">
     <input type="text" name="<?php echo $portlet->shortname ?>_total" value="0" size="10" disabled class="totals" >
    <script type="text/javascript">
        calculateLocal(document.forms['caddie'].<?php echo $portlet->shortname ?>, <?php echo $portlet->TTCprice ?>);
    </script>
  </td>
</tr>
