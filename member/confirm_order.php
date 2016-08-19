<?php
require_once 'include/order.class.php';
$uid=(int)$uid;
$order_info = $db->get_one("SELECT uid,ordersn,status,goods_rest_amount 
    	                    FROM `{$tablepre}order_info` 
    	                    WHERE uid = '$uid' AND username='$m_check_id' 
    	                    LIMIT 1");
if(!$order_info) show_msg('找不到您的订单，请及时联系管理员');
if(!in_array($order_info['status'],array(3,4))) show_msg('当前状态不能确认收货');
if($order_info['goods_rest_amount']>0) show_msg('尚有余额未结清，无法确认收货');

$db->query("UPDATE `{$tablepre}order_info` SET status='5' WHERE uid='$order_info[uid]'");
order::dispatch($order_info['uid']);

inner_sms_send($seller['member_id'],$m_check_id,"{$order_info[ordersn]}订单确认成功","您在{$seller[shop_name]}的订单{$order_info[ordersn]}确认收货成功，感谢您的参与",0);
if($mvm_member['member_email'])
{
    smtp_mail($mvm_member['member_email'],$order_info['ordersn'].'订单完成，请及时评价',
              "您在{$mm_mall_title}订购的订单$order_info[ordersn]已完成，请及时评价<br />
               <a href='$mm_mall_url/member.php?action=scomment&roll=1' target='_blank'>点此进行评价</a>");
}


show_msg('确认收货成功','member.php?action=scomment&roll=1');