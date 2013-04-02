<?php

    $froms = get_records_select('flowcontrol', " element = 'bill' AND `to` = '{$portlet->status}' GROUP BY element,`from` ");
    $tos = get_records_select('flowcontrol', " element = 'bill' AND `from` = '{$portlet->status}' GROUP BY element,`to` ");

?>
<script type="text/javascript">
function flowcontrol_toggle(){
    var panel = document.getElementById('flowcontrol');
    if (panel.style.visibility == 'hidden'){
        panel.style.visibility = 'visible';
        document.images['flowcontrol_button'].src = "<?php echo $CFG->pixpath.'/t/switch_minus.gif' ?>";
    } else {
        panel.style.visibility = 'hidden';
        document.images['flowcontrol_button'].src = "<?php echo $CFG->pixpath.'/t/switch_plus.gif' ?>";
    }
}
</script>
<table class="flowcontrolHead" cellspacing="0" width="100%">
   <tr class="billListTitle">
      <td valign="top" style="padding : 2px" align="left">
         <a href="Javascript:flowcontrol_toggle()"><img name="flowcontrol_button" src="<?php echo $CFG->pixpath.'/t/switch_plus.gif' ?>" border="0"></a>
      </td>
      <td valign="top" style="padding : 2px" align="left">
         <?php print_string('billstates', 'block_courseshop') ?>
      </td>
      <td valign="top" style="padding : 2px" align="right">
         <?php print_string('actualstate', 'block_courseshop') ?>: <div class="billstate"><?php print_string($portlet->status, 'block_courseshop') ?></div>
      </td>
   </tr>
</table>
<table id="flowcontrol" width="100%" class="generaltable" cellspacing="0" style="visibility:hidden">
   <tr valign="middle" >
      <th width="50%" class="header c0" align="left">
        <?php print_string('backto', 'block_courseshop') ?>
      </th>
      <th width="50%" class="header c1" align="right">
        <?php print_string('goto', 'block_courseshop') ?>
      </th>
   </tr>
   <tr>
       <td style="padding : 5px" align="left">
<?php

if ($froms){
    foreach($froms as $aFrom){
    ?>
                <a href="<?php echo $portlet->url."&pinned={$pinned}&cmd=flowchange&status={$aFrom->from}" ?>"><?php print_string($aFrom->from, 'block_courseshop') ?></a>
    <?php
    }
} else { 
    print_string('flowControlNetStart', 'block_courseshop');
}
?>
       </td>
       <td style="padding : 5px" align="right">
<?php
if ($tos){
    foreach($tos as $aTo){
?>
            <a href="<?php echo $portlet->url."&pinned={$pinned}&cmd=flowchange&status={$aTo->to}" ?>"><?php print_string($aTo->to, 'block_courseshop') ?></a><br />
<?php
    }
} else {
     print_string('flowControlNetEnd', 'block_courseshop');
}
?>
       </td>
   </tr>
</table>