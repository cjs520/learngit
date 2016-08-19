<?php
$uid=(int)$uid;
if($uid<=0) exit('请指定正确的订单');
$order = $db->get_one("SELECT ordersn,status,uid,username,goods_amount,sh_price,goods_point,discount FROM `{$tablepre}order_info` 
    	               WHERE uid = '$uid' AND username='$m_check_id' 
    	               LIMIT 1");
if(!$order) exit('检索不到您的订单，请联系管理员');
if($order['status']!=1) exit('当前状态无法取消订单');

$db->query("UPDATE `{$tablepre}order_info` SET status='2' WHERE uid='$uid'");
$db->query("UPDATE `{$tablepre}order_goods` SET status='0' WHERE order_id='$uid'");
$db->free_result();
exit('ok');