<?php
// $_CFG['debug'] = "DB";

$cmd = get('cmd', 'REQUEST');
if ($cmd != ''){
    include includePage($_CXT->level1 . '/' . $_CXT->level2. '/search.controller.php');
}

$query = "
   SELECT 
        COUNT(*)
   FROM
        {$CFG->prefix}courseshop_catalogitem
";
$result = mysql_execute($query);
$theProducts = mysql_fetch_row($result);
$productCount = $theProducts[0];

?>
<script type="text/javascript">
function searchBy(criteria){
    document.search.by.value = criteria;
    document.search.submit();
}
</script>
<h1><?php print_string('Produits : Recherche') ?></h1>

<?php
if ($cmd == 'search'){
    echo loadText('searchProductFailed');
}
?>