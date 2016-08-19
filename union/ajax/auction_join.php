<?php
$uid=(int)$uid;

$arr_rtl=array();
$q=$db->query("SELECT m_id,price,register_date FROM `{$tablepre}goods_auction_join` WHERE g_uid='$uid' ORDER BY register_date DESC LIMIT 8");
while ($rtl=$db->fetch_array($q))
{
    $rtl['register_date']=date('Y-m-d H:i:s',$rtl['register_date']);
    $rtl['price']=currency($rtl['price']);
    $arr_rtl[]=$rtl;
}
$db->free_result();

exit(json_encode($arr_rtl));