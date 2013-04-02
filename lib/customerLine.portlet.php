<tr class="customer" valign="top" >
   <td class="cell c0">
   </td>
   <td class="cell c1">
      <a class="activeLink" href="<?php echo $CFG->wwwroot."/blocks/courseshop/customers/view.php?id=$id&pinned={$pinned}&view=viewCustomer&customer=" . $portlet->id ?>"><?php echo  $portlet->id ?></a>
   </td>
   <td class="cell c2">
      <?php echo $portlet->lastname ?>
   </td>
   <td class="cell c3">
       <?php echo $portlet->firstname ?>
   </td>
   <td class="cell c4">
       <?php echo $portlet->billCount ?>
   </td>
   <td align="right" class="cell c5">
       <b><?php echo  sprintf("%.2f", round($portlet->totalAccount, 2)) ?> <?php echo $CFG->block_courseshop_defaultcurrency ?></b>
   </td>
   <td align="right" class="cell lastcol">
         <a class="activeLink" href="<?php echo $CFG->wwwroot."/blocks/courseshop/customers/edit_customer.php?id={$id}&pinned={$pinned}&customerid={$portlet->id}"; ?>"><img src="<?php echo $CFG->pixpath.'/t/edit.gif' ?>" border="0"></a>
<?php
if ($portlet->billCount == 0){
?>
      <a class="activeLink" href="<?php echo $portlet->url."&pinned={$pinned}&customerid={$portlet->id}&cmd=deletecustomer"; ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif' ?>" border="0"></a>
<?php
}
?>

   </td>
</tr>
