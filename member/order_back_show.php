<?php
$uid=(int)$uid;
$order_back=$db->get_one("SELECT uid,status,og_uid,info1,register_date 
                          FROM `{$tablepre}order_back` 
                          WHERE uid='$uid' AND m_uid='$m_check_uid' 
                          LIMIT 1");
if(!in_array($order_back['status'],array(1,2,3,4,-1)))  move_page("member.php?action=order_back_2&uid=$order_back[uid]");
if(!$order_back) show_msg('检索不到指定的退货记录');

$order_goods=$db->get_one("SELECT order_id,goods_name,g_uid,goods_table,module,status,buy_number,buy_point,buy_price,supplier_id,goods_attr 
                           FROM `{$tablepre}order_goods` WHERE uid='$order_back[og_uid]' LIMIT 1");
if(!$order_goods) show_msg('检索不到指定的订单商品');
$order_goods['goods_attr']=str_replace('|','<br />',$order_goods['goods_attr']);
$order_goods['total_price']=$order_goods['buy_price']*$order_goods['buy_number'];
$order_goods['total_price_txt']=currency($order_goods['total_price']);
$order_goods['buy_price']=currency($order_goods['buy_price']);

$order_info=$db->get_one("SELECT ordersn FROM `{$tablepre}order_info` WHERE uid='$order_goods[order_id]' AND username='$m_check_id' LIMIT 1");
if(!$order_info) show_msg('检索不到指定订单');

$order_back['info1']=unserialize($order_back['info1']);
$order_back['info2']=unserialize($order_back['info2']);
$order_back['info1']['money']=currency($order_back['info1']['money']);
$order_back['left_time']=$order_back['register_date']+7*24*3600-$m_now_time;

$product=$db->get_one("SELECT goods_name,goods_file1 FROM `$order_goods[goods_table]` WHERE uid='$order_goods[g_uid]' LIMIT 1");
if($product)
{
    $product['url']=GetBaseUrl($order_goods['module'],$order_goods['g_uid'],'action',$order_goods['supplier_id']);
    $product['goods_file1']=ProcImgPath($product['goods_file1']);
}

$shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$order_goods[supplier_id]' LIMIT 1");
if($shop)
{
    $shop['url']=GetBaseUrl('index','','',$order_goods['supplier_id']);
    $cfg=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$order_goods[supplier_id]' AND cf_name='mm_client_qq1' LIMIT 1");
    $shop['qq']=$cfg['cf_value'];
}

require 'header.php';
include template('member_order_back_show');