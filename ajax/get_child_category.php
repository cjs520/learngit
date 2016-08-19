<?php
$uid=(int)$uid;
$arr_category=array();
$q=$db->query("SELECT uid,category_name FROM `{$tablepre}category` WHERE supplier_id='0' AND category_id='$uid' ORDER BY category_rank");
while ($rtl=$db->fetch_array($q))
{
    $arr_category[]=$rtl;
}
$db->free_result();

exit(json_encode($arr_category));