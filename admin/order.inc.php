<?php

/**
 * MVM_MALL 网上商店系统  订单管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-15 $
 * $Id: order.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

if ($action=='list')
{
    require_once 'include/pager.class.php';
    $search_sql = 'WHERE TRUE ';
    
    $consginee && $search_sql .= " AND consignee = '".dhtmlchars($consginee)."'";
    $start_time && $search_sql.=" AND addtime>='".strtotime($start_time)."'";
    $end_time && $search_sql.=" AND addtime<='".strtotime($end_time)."'";
    $ordersn && $search_sql .= " AND ordersn = '".dhtmlchars($ordersn)."'";
    $status && $search_sql.=" AND status='".intval($status)."'";
    $username && $search_sql.=" AND username='$username'";
    
    $arr_order=array();
    $total_count = $db->counter("{$tablepre}order_info", $search_sql);
    $page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,supplier_id,ordersn,addtime,pay_name,status,goods_amount,goods_rest_amount,sh_price,goods_point,discount,username,mark 
                     FROM `{$tablepre}order_info` 
                     $search_sql 
                     ORDER BY addtime DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['addtime']=date('Y-m-d H:i',$rtl['addtime']);
        $rtl['order_amount']=currency($rtl['goods_amount']+$rtl['sh_price']-$rtl['discount']);
        $rtl['status_code']=$rtl['status'];
        $rtl['status']=$m_order_array[$rtl['status']];
        
        if($rtl['goods_rest_amount']>0 && !($rtl['mark'] & 8))
        {
            if($rtl['status_code']==1) $rtl['status']='定金待付款';
        }
        else if($rtl['goods_rest_amount']>0 && ($rtl['mark'] & 8))
        {
            $rtl['status']='余额待付款';
        }
        
        $rtl['goods_rest_amount']=currency($rtl['goods_rest_amount']);
        $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        $shop['url']=GetBaseUrl('index','','',$rtl['supplier_id']);
        $rtl['shop']=$shop;
        
    	$arr_order[] = $rtl;
    }
    $page_list = $rowset->link("admincp.php?module=order&action=list&start_date=$start_date&ordersn=$ordersn&consignee=".urlencode($consginee)."&username=".urlencode($username)."&status=$status&page=");
    $sel_status=drop_menu($m_order_array,'status',$status);
    
    require_once template('order');
    footer();
    
}
else if ($action=='edit')
{
    require_once 'include/order.class.php';
	$uid = (int)$uid;
    $order_info = $db->get_one("SELECT * FROM `{$tablepre}order_info` WHERE uid = '$uid' LIMIT 1");
    if(!$order_info) sadmin_show_msg('检索不到指定订单',$p_url);
    
    if((int)$setp==1 && $_POST)
    {
        if($order_row['status']<$order_info['status']) sadmin_show_msg('订单状态不能向后设置',$p_url);
        if($order_row['status']==6 && $order_info['status']!=6) sadmin_show_msg('不能手动设置到'.$m_order_array[6].'状态',$p_url);
        
        do    //扣除库存
        {
            if($order_row['status']!=4) break;
            if($order_info['status']==4) break;
            
            $payment=$db->get_one("SELECT class_name FROM `{$tablepre}payment_table` WHERE id='$order_info[pay_id]' LIMIT 1");
            $shop=cur_member_info($order_info['supplier_id']);
            if($payment['class_name']=='COD')    //货到付款，扣除商家金额，否则无法发货
            {
                $total_price=$order_info['goods_amount']+$order_info['sh_price']-$order_info['discount'];
                if($shop['member_money']<$total_price) sadmin_show_msg('商家预存款余额不足，无法发货',$p_url);
                if($shop['member_point']<$order_info['goods_point']) sadmin_show_msg('商家积分余额不足，无法发货',$p_url);
                
                add_money($$order_info['supplier_id'],-$total_price,'购物',"订单$order_info[ordersn]发货",$order_info['ordersn']);
                add_score($$order_info['supplier_id'],-$order_info['goods_point'],'发货',"订单$order_info[ordersn]发货",$order_info['ordersn']);
            }
            $order_row['checktime']=$m_now_time+intval($mm_order_confirm)*24*3600;
            
            $q=$db->query("SELECT uid FROM `{$tablepre}order_goods` WHERE order_id='$order_info[uid]'");
            while ($rtl=$db->fetch_array($q)) order::change_stock($rtl['uid']);
            $db->free_result();
        }while (0);
        
        do    //加回库存
        {
            if($order_row['status']!=6) break;
            if($order_info['status']==6) break;
            $q=$db->query("SELECT uid FROM `{$tablepre}order_goods` WHERE order_id='$order_info[uid]'");
            while ($rtl=$db->fetch_array($q)) order::change_stock($rtl['uid'],false);
            $db->free_result();
        }while (0);
        
        $db->update("`{$tablepre}order_info`",$order_row," uid='$order_info[uid]'");
        
        if(in_array($order_row['status'],array(3,4,5)))
        {
            $db->query("UPDATE `{$tablepre}order_goods` SET status=1 WHERE order_id='$order_info[uid]'");
            $db->free_result();
        }
        
        do    //分账
        {
            if($order_row['status']!=5) break;
            if($order_info['status']==5) break;
            order::dispatch($order_info['uid']);
        }while (0);
        
        admin_log("编辑订单：$order_info[ordersn]");
        move_page(base64_decode($p_url));
    }
    
    $total_goods_num=0;
    $arr_goods=array();
    list($arr_goods,$total_goods_num)=order::get_order_goods($order_info['uid']);
    
    $sh_price=$order_info['sh_price'];
    $discount=$order_info['discount'];
    $order_info['goods_rest_amount']=currency($order_info['goods_rest_amount']);
    $order_info['order_amount']=currency($order_info['goods_amount']+$order_info['sh_price']-$order_info['discount']);
    $order_info['goods_amount']=currency($order_info['goods_amount']);
    $order_info['sh_price']=currency($order_info['sh_price']);
    $order_info['discount']=currency($order_info['discount']);
    
    if($per=='view')
    {
        $ship=$db->get_one("SELECT name FROM `{$tablepre}ship_table` WHERE uid='$order_info[sh_uid]' LIMIT 1");
        $order_info['sh_name']=$ship['name'];
    }
    else
    {
        $arr_ship=array();
        $q=$db->query("SELECT uid,name FROM `{$tablepre}ship_table` WHERE supplier_id='$order_info[supplier_id]'");
        while ($rtl=$db->fetch_array($q)) $arr_ship[$rtl['uid']]=$rtl['name'];
        $db->free_result();
        $sel_ship=drop_menu($arr_ship,'order_row[sh_uid]',$order_info['sh_uid']);
        $order_menu = drop_menu($m_order_array,'order_row[status]',$order_info['status']);
    }
    
    include template($per=='view' ?'order_view':'order_add');
    exit;
    
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $order_info=$db->get_one("SELECT ordersn FROM `{$tablepre}order_info` WHERE uid='$uid' LIMIT 1");
    if($order_info)
    {
        admin_log("删除订单：$order_info[ordersn]");
        $db->query("DELETE FROM `{$tablepre}order_info` WHERE uid='$uid'");
        $db->query("DELETE FROM `{$tablepre}order_goods` WHERE order_id ='$uid'");
        $db->free_result();
    }
    
    exit('OK');
}
else show_msg('pass_worng');

