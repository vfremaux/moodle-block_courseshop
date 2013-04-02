<?php

    $pathinfo = pathinfo($portlet->attachement);
    $type = strtoupper($pathinfo['extension']);
    $filename = $pathinfo['basename'];
    $fileIcon = $CFG->pixpath.'/f/$type.gif';
    if (!file_exists($fileIcon)){
        $fileIcon = $CFG->pixpath.'/f/unkonwn.gif';
    }
    
?>
<tr>
   <td>
      <img src="<?php echo $fileIcon ?>">
   </td>
   <td width="120">
      <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/file.php?type={$portlet->attachementType}&file={$filename}") ?>"><?php echo $filename ?></a>
   </td>
   <td width="80">
      <?php echo filesize($portlet->attachement) ?> octets
   </td>
   <td>
      <!-- a href=""><img src="<?php echo getUrl('images/icons/view.gif') ?>"></a -->
      <a href="<?php echo $CFG->wwwroot."/blocks/courseshop/bills/view.php?id={$id}&pinned=$pinned&cmd=unattach&type={$portlet->attachementType}&file={$filename}" ?>"><img src="<?php echo $CFG->pixpath.'/t/delete.gif'?>" border="0" alt="<?php print_string('delete') ?>"></a>
   </td>
</tr>
