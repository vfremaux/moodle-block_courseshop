<?php
if (empty($portlet->pageSize)) $portlet->pageSize = 20;
if ($portlet->pageSize < $portlet->total) {
   $pages = ceil($portlet->total / $portlet->pageSize);
   if ($offset = optional_param('offset', 0, PARAM_INT) > 0) {
     $pageoffset = $offset - $portlet->pageSize;
?>
         <a href="<?php echo $portlet->url."&pinned={$pinned}&offset={$pageoffset}" ?>">&lt;</a> - 
<?php
   }
?>
   <span class="paging">
<?php
   for($i = 1 ; $i <= $pages ; $i++ ){
      if ($i == ($offset / $portlet->pageSize) + 1) {
         echo " $i - ";
      } else {
        $pageoffset = $portlet->pageSize * ($i - 1);
?>
         <a class="paging" href="<?php echo $portlet->url."&pinned={$pinned}&offset={$pageoffset}" ?>"><?php echo $i ?></a> - 
<?php
      }
   }
?>
   </span>
<?php
   if ( $offset + $portlet->pageSize < $portlet->total) {
     $pageoffset = $offset + $portlet->pageSize;
?>
         <a href="<?php echo $portlet->url."&pinned={$pinned}&offset={$pageoffset}" ?>">&gt;</a>
<?php
   }
}
?>
