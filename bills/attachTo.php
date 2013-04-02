<?php
$item = get('item','REQUEST');
$type = get('type', 'REQUEST');

switch($type) {
   case 'bill' : ;
   case 'billitem' : { $referer = getUrl('spage.php?p={$_CXT->level1}/bills/viewBill'); break ; }
}
?>
<form name="upload" method="post" enctype="multipart/form-data" action="<?php echo  getUrl('spage.php') ?>">
<input type="hidden" name="type" value="<?php echo  $type ?>">
<input type="hidden" name="p" value="<?php echo  $_CXT->level1 ?>/bills/uploadGate">
<input type="hidden" name="item" value="<?php echo  $item ?>">
<table width="100%">
   <tr height="150">
      <td valign="bottom">
      <img src="<?php echo  getUrl('images/intranet/envelope.jpg') ?>" height="120"><br>
      <?php echo  loadText('uploadText') ?>
      </td>
   </tr>
   <tr>
      <td>
         <input type="file" name="attachement">
      </td>
   </tr>
   <tr>
      <td align="right">
         <a href="Javascript:document.upload.submit();"><?php echo  getString('save') ?></a> - 
         <a href="<?php echo  $referer ?>"><?php echo  getString('cancel') ?></a>
      </td>
   </tr>
</table>
</form>