<?php
require_once 'include/order.class.php';
$order=new order();

$q=$db->query("SELECT task_name,param,expire FROM `{$tablepre}task_list` WHERE expire<='$m_now_time'");
while($rtl=$db->fetch_array($q))
{
    switch ($rtl['task_name'])
    {
        case 'preorder':
            $order_uid=(int)$rtl['param'];
            $order->dispatch($order_uid);
            break;
        default:
            break;
    }
}
$db->free_result();
$db->query("DELETE FROM `{$tablepre}task_list` WHERE expire<='$m_now_time'");
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>