<?php
$uid=(int)$uid;
$reason=mb_substr(dhtmlchars($reason),0,80);
$day=(int)$day;

if($day<=0 || $day>(int)$mm_max_delay) exit('ERR:延迟时间不符合规定，请重新填写');

$order_info=$db->get_one("SELECT uid,ordersn,supplier_id FROM `{$tablepre}order_info` WHERE uid='$uid' AND username='$m_check_id' LIMIT 1");
if(!$order_info) exit('ERR:检索不到指定的订单');
$delay_info=$db->get_one("SELECT uid FROM `{$tablepre}order_delay` WHERE ordersn='$order_info[ordersn]' LIMIT 1");
if($delay_info) exit('ERR:延迟申请已提交，无法重复申请');

$row=array(
    'ordersn'=>$order_info['ordersn'],
    'day'=>$day,
    'reason'=>$reason,
    'm_id'=>$m_check_id,
    'supplier_id'=>$order_info['supplier_id'],
    'register_date'=>$m_now_time
);
$db->insert("`{$tablepre}order_delay`",$row);
$db->free_result();

exit('OK：申请提交成功，请等待店主审核');