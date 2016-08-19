<?php
require_once 'include/order.class.php';

$arr_uids=array();
$q=$db->query("SELECT uid FROM `{$tablepre}order_info` WHERE status='4' AND checktime<='$today_timestamp'");
while ($rtl=$db->fetch_array($q))
{
    $arr_uids[]=$rtl['uid'];
    unset($rtl);
}
$db->free_result();

foreach ($arr_uids as $val)
{
    $db->query("UPDATE `{$tablepre}order_info` SET status='5' WHERE uid='$val'");
    order::dispatch($val);
}
unset($arr_uids);

//for backwards order, dispatching money without altering order status
$expire=$today_timestamp-30*24*3600;
$arr_uids=array();
$q=$db->query("SELECT uid FROM `{$tablepre}order_info` WHERE status='6' AND addtime>'$expire' AND NOT (mark & 1)");
while ($rtl=$db->fetch_array($q))
{
    $arr_uids[]=$rtl['uid'];
    unset($rtl);
}
$db->free_result();

foreach ($arr_uids as $val)
{
    order::dispatch($val);
}
unset($arr_uids);

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>