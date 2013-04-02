<tr class="<?php echo strtolower($portlet->status) ?>line">
   <td>
        <a href="<?php echo $CFG->wwwroot.'/blocks/courseshop/products/view.php?view=viewProduct&productid={$portlet->id}&id={$id}") ?>"><?php echo $portlet->code ?></a>
   </td>
   <td>
        <?php echo $portlet->name ?>
   </td>
   <td>
        <?php print_string($portlet->status) ?>
   </td>
   <td>
        <?php echo $portlet->stock ?>
   </td>
   <td>
        <?php echo sprintf("%.2f", round($portlet->price1, 2)) ?>
   </td>
</tr>