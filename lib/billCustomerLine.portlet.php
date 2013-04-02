<?php
if ($portlet->type == 'BILLING'){
?>
<tr class="<?php echo courseshop_switch_style('odd','even'); ?>">
   <td valign="top" class="billlineabstract">
      <?php echo  $portlet->abstract ?>
   </td>
   <td valign="top" class="billlinecode">
      <?php echo  $portlet->itemcode ?>
   </td>
   <td valign="top" class="billlineprice" align="right">
      <?php echo  sprintf("%.2f", round($portlet->unitcost, 2)) ?>&nbsp&nbsp;
   </td>
   <td valign="top" class="billlinequantity" align="right">
      <?php echo  $portlet->quantity ?>&nbsp;&nbsp;
   </td>
   <td align="right" valign="top"  class="billtaxamount" align="right">
      <?php echo  sprintf("%.2f", round($portlet->quantity * $portlet->taxamount, 2)) ?>&nbsp&nbsp;
   </td>
   <td align="right" valign="top"  class="billtaxcode" align="right">
      <?php echo  sprintf("%.1f", round($portlet->ratio, 2)) ?>&nbsp&nbsp;
   </td>
   <td align="right" valign="top"  class="billlineprice" align="right">
      <?php echo  sprintf("%.2f", round($portlet->quantity * $portlet->taxedprice, 2)) ?>&nbsp&nbsp;
   </td>
</tr>
<?php
}
else{
?>
<tr class="<?php echo  switchStyle('odd','even'); ?>">
    <td colspan="7" class="billlineabstract">
        <?php echo  $portlet->abstract ?>&nbsp;
    </td>
</tr>
<?php
}
?>