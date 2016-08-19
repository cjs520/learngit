<?php
$arr_expire_uid=array();
$q=$db->query("SELECT m_uid FROM `{$tablepre}member_shop` WHERE shop_level>0 AND shop_expire<='$today_timestamp'");
while ($rtl=$db->fetch_array($q))
{
    $arr_expire_uid[]=$rtl['m_uid'];
    unset($rtl);
}
$db->free_result();
if($arr_expire_uid)
{
    $str_expire_uid=implode(',',$arr_expire_uid);
    $db->query("UPDATE `{$tablepre}member_shop` SET shop_level=0 WHERE m_uid IN ($str_expire_uid)");
    $db->free_result();
}

$expire=$today_timestamp+3*24*3600;
$q=$db->query("SELECT m_id FROM `{$tablepre}member_shop` WHERE shop_level>0 AND shop_expire<='$expire'");
while ($rtl=$db->fetch_array($q))
{
    inner_sms_send('admin',$rtl['m_id'],'商铺续费提示','您的商铺即将过期，请即时充值',0);
    unset($rtl);
}
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);
?>