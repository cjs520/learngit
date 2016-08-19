<?php
require_once 'include/pager.class.php';
require_once 'include/order.class.php';
$status=(int)$status;
$filter_sql="WHERE username='$m_check_id'";
$use_index='username';

if($ordernum) $filter_sql.=" AND ordersn='$ordernum'";
if($status>0 && $status<=7)
{
    $use_index='username_2';
    $filter_sql.=" AND status='$status'";
}
else if($status==8)
{
    $filter_sql.=" AND goods_rest_amount>0 AND !(mark & 8)";
}
else if($status==9)
{
    $filter_sql.=" AND goods_rest_amount>0 AND (mark & 8)";
}


$order_list = array();
$total_count = $db->counter("`{$tablepre}order_info`",$filter_sql);
$page = $page ? (int)$page:1;
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q = $db->query("SELECT uid,ordersn,addtime,goods_amount,goods_rest_amount,mark,goods_point,sh_price,discount,status,pay_name,pay_id,supplier_id,delivery_code,sh_uid,code 
                 FROM `{$tablepre}order_info` 
                 FORCE INDEX (`$use_index`) 
                 $filter_sql 
                 ORDER BY addtime DESC 
                 LIMIT $from_record, $list_num");
while ($rtl = $db->fetch_array($q))
{
    $rtl['order_amount']=currency($rtl['goods_amount']+$rtl['sh_price']-$rtl['discount']);
    $rtl['goods_rest_amount_txt']=currency($rtl['goods_rest_amount']);
    !$rtl['code'] && $rtl['code']='无';
    
    $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
    $shop['url']=GetBaseUrl('index','','',$rtl['supplier_id']);
    $rtl['shop']=$shop;
    
    $rtl['status_code']=$rtl['status'];
    $rtl['status']=$m_order_array[$rtl['status']];
    $rtl['addtime'] = date('Y-m-d H:i:s',$rtl['addtime']);
    if($rtl['goods_rest_amount']>0 && !($rtl['mark'] & 8))
    {
        if($rtl['status_code']==1) $rtl['status']='定金待付款';
        $rtl['add_status_info']='(等待商家备货)';
    }
    else if($rtl['goods_rest_amount']>0 && ($rtl['mark'] & 8))
    {
        $rtl['status']='余额待付款';
        $rtl['add_status_info']='(商家已备货，请支付订单余额)';
    }
    
    $order_list[] = $rtl;
}
foreach ($order_list as $key=>$val)
{
    list($order_list[$key]['order_goods'])=order::get_order_goods($val['uid']);
}

$page_list = $rowset->link("member.php?action=$action&ordernum=$ordernum&status=$status&page=");

require 'header.php';
include template('member_order');