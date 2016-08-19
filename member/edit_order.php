<?php
require_once 'include/order.class.php';
$uid = (int)$uid;
$order_info = $db->get_one("SELECT * FROM `{$tablepre}order_info` WHERE uid = '$uid' and username='$m_check_id' LIMIT 1");
if(!$order_info) show_msg('检索不到指定订单');

$ship=$db->get_one("SELECT name FROM `{$tablepre}ship_table` WHERE uid='$order_info[sh_uid]' LIMIT 1");
$order_info['sh_name']=$ship['name'];

$order_info['checktime']=$order_info['status']==4?date('Y-m-d H:i',$order_info['checktime']):'无';
if(!$order_info['delivery_code']) $order_info['delivery_code']='无';
$order_info['order_amount']=currency($order_info['goods_amount']+$order_info['sh_price']-$order_info['discount']);
$order_info['goods_amount']=currency($order_info['goods_amount']);
$order_info['sh_price']=currency($order_info['sh_price']);
$order_info['discount']=currency($order_info['discount']);
$order_info['status']=$m_order_array[$order_info['status']];
list($arr_goods,$goods_total_num)=order::get_order_goods($order_info['uid']);


require_once 'header.php';
include template('member_order_edit');
exit;