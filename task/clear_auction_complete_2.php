<?php

$arr_rtn_money=array();
$q=$db->query("SELECT uid,assure,goods_name,start_price,end_price,start_date,end_date,is_complete,bid_add,type 
               FROM `{$tablepre}goods_auction` 
               WHERE is_complete='1'");
while ($rtl=$db->fetch_array($q))
{
    $q_tmp=$db->query("SELECT m_uid,money FROM `{$tablepre}goods_auction_assure` WHERE g_uid='$rtl[uid]' AND success='0'");
    while ($rtl_tmp=$db->fetch_array($q_tmp))
    {
        $arr_rtn_money[]=array($rtl_tmp['m_uid'],$rtl_tmp['money']);
    }
    $db->free_result(1);
    
    $db->query("UPDATE `{$tablepre}goods_auction` SET is_complete=2 WHERE uid='$rtl[uid]'");
    $db->free_result(1);
    unset($rtl);
}
$db->free_result();

foreach ($arr_rtn_money as $val)
{
    add_money($val[0],$val[1],'保证金','保证金退还');
}
unset($arr_rtn_money);

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);

?>