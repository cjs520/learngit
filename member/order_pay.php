<?php
require_once 'include/pager.class.php';
require_once 'include/order.class.php';

$sn=dhtmlchars($sn);
$rest=dhtmlchars($rest);
if(strtolower(substr($sn,0,2))=='au') move_page("member.php?action=auction_pay&sn=$sn");

$order_info=$db->get_one("SELECT uid,ordersn,addtime,address,consignee,zipcode,mobile,goods_amount,discount,sh_price,sh_uid,supplier_id,status,goods_rest_amount 
                          FROM `{$tablepre}order_info` 
                          WHERE ordersn='$sn' AND username='$m_check_id' 
                          LIMIT 1");
if(!$order_info) show_msg('检索不到指定订单');
if($rest=='rest' && $order_info['goods_rest_amount']<=0) show_msg('检索不到需要付款的订单'); 
else if($rest!='rest' && $order_info['status']!=1) show_msg('指定订单无法支付'); 
$order_info['addtime']=date('Y-m-d H:i:s',$order_info['addtime']);
$order_info['order_amount']=currency($order_info['goods_amount']+$order_info['sh_price']-$order_info['discount']);
$order_info['goods_rest_amount']=currency($order_info['goods_rest_amount']);
$order_info['sh_price']=currency($order_info['sh_price']);
$order_info['discount']=$order_info['discount']>0?currency($order_info['discount']):'无';

$ship=$db->get_one("SELECT name FROM `{$tablepre}ship_table` WHERE uid='$order_info[sh_uid]' LIMIT 1");
$shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$order_info[supplier_id]' LIMIT 1");
$shop['url']=GetBaseUrl('index','','',$order_info['supplier_id']);

$cfg=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE cf_name='mm_client_qq1' AND supplier_id='$order_info[supplier_id]' LIMIT 1");
$shop['qq']=$cfg['cf_value'];

list($arr_order_goods,$total_goods_num)=order::get_order_goods($order_info['uid']);

require 'header.php';
include template($rest=='rest'?'member_order_pay_rest':'member_order_pay');