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
 * $Id: back_order.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
$arr_status=array(-1=>'拒绝',1=>'等待卖家审核',2=>'等待买家填写',3=>'等待卖家审核',4=>'完成');

if ($action=='list')
{
   require_once 'include/pager.class.php';
    $search_sql = 'WHERE TRUE ';
    $order_sql=" ORDER BY uid DESC";
    if($start_time) $search_sql.=" AND register_date>='".strtotime($start_time)."'";
    if($end_time) $search_sql.=" AND register_date<='".strtotime($end_time)."'";
    if($ordersn) $search_sql.=" AND ordersn='$ordersn'";
    if($m_id) $search_sql.=" AND m_id='$m_id'";
    if(strstr($search_sql,'AND')) $order_sql=" ORDER BY register_date DESC";
    
    $arr_order_back=array();
    $total_count = $db->counter("{$tablepre}order_back", $search_sql);
    $page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,ordersn,og_uid,g_uid,module,goods_table,status,supplier_id,reject,info1,m_id,register_date,supplier_id 
                     FROM `{$tablepre}order_back` 
                     $search_sql 
                     $order_sql
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $g=$db->get_one("SELECT goods_name,goods_file1 
                         FROM `$rtl[goods_table]` 
                         WHERE uid='$rtl[g_uid]' 
                         LIMIT 1");
       $g['goods_file1']=ProcImgPath($g['goods_file1']);
        $g['goods_url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
        
        $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        $shop['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);
        
        $rtl=array_merge($g,$rtl);
        $rtl=array_merge($shop,$rtl);
        
        $order_info=$db->get_one("SELECT discount FROM `{$tablepre}order_info` WHERE ordersn='$rtl[ordersn]' LIMIT 1");
        $rtl['discount']=currency($order_info['discount']);
        
        $rtl['info1']=unserialize($rtl['info1']);
        $rtl['status_txt']=$arr_status[$rtl['status']];
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
    	$arr_order_back[] = $rtl;
    }
    $page_list = $rowset->link("admincp.php?module=order&start_time=$start_time&end_time=$end_time&ordersn=$ordersn&m_id=".urlencode($m_id)."&page=");
    
    require_once template('order_back');
    footer();

}
else if($action=='edit')
{
    
    $uid=(int)$uid;
    $order_back=$db->get_one("SELECT * FROM `{$tablepre}order_back` WHERE uid='$uid' LIMIT 1");
    
    if($_POST && (int)$step==1)
    {
        if(!in_array($order_back['status'],array(1,3))) sadmin_show_msg('退货申请状态无法修改',$p_url);
        $approval=(int)$approval==1?1:-1;
        $reject=trim(strip_tags($reject));
        $back_address=trim(strip_tags($back_address));
        $consignee=trim(strip_tags($consignee));
        if($approval==-1 && !$reject) sadmin_show_msg('请填写您的拒绝理由',$p_url);
        if($approval==1 && !$back_address) sadmin_show_msg('请填写退货地址',$p_url);
        if($approval==1 && !$consignee) sadmin_show_msg('请填写退货收件人',$p_url);
        
        $status=$approval==-1?-1:$order_back['status']+1;

        if($approval==-1)    //退积分
        {
            $order_goods=$db->get_one("SELECT buy_price,buy_number FROM `{$tablepre}order_goods` WHERE uid='$order_back[og_uid]' LIMIT 1");
            add_score($order_back['m_uid'],intval($order_goods['buy_price']*$order_goods['buy_number']),'拒绝退货',"拒绝$order_back[ordersn]申请",$order_back['ordersn']);
        }
        
        if($approval==1 && $status==4)    //最终确认退货，并索回商铺的分账和积分
        {
            $arr_rtl=claim_order_back($order_back);
            if(!$arr_rtl[0]) sadmin_show_msg($arr_rtl[1],$p_url);
        }
        
        $row=array('status'=>$status);
        if($approval==-1) $row['reject']=$reject;
        if($order_back['status']==1 && $approval==1)
        {
            $row['back_address']=$back_address;
            $row['consignee']=$consignee;
        }
        $db->update("`{$tablepre}order_back`",$row," uid='$uid' ");
        $db->free_result();
        
        admin_log("修改退货订单：$order_back[ordersn]");
        
        move_page(base64_decode($p_url));
    }
    
    if(!$order_back) exit('检索不到指定的退货申请');
    $order_back['status_txt']=$arr_status[$order_back['status']];
    $order_back['info1']=unserialize($order_back['info1']);
    $order_back['info2']=unserialize($order_back['info2']);
    $order_back['info1']['money']=currency($order_back['info1']['money']);
    //if(!$order_back['info1']['img'] || !file_exists($order_back['info1']['img'])) $order_back['info1']['img']='images/noimages/noproduct.jpg';
   // if(!$order_back['info2']['img'] || !file_exists($order_back['info2']['img'])) $order_back['info2']['img']='images/noimages/noproduct.jpg'
    $order_back['info2']['img']=IMG_URL.$order_back['info1']['img'];
    
    $order_goods=$db->get_one("SELECT goods_attr,buy_point,buy_price,buy_number FROM `{$tablepre}order_goods` WHERE uid='$order_back[og_uid]' LIMIT 1");
    $order_goods['buy_price']=currency($order_goods['buy_price']);
    
    $product=$db->get_one("SELECT goods_name,goods_file1 FROM `$order_back[goods_table]` WHERE uid='$order_back[g_uid]' LIMIT 1");
    $product['goods_file1']=ProcImgPath($product['goods_file1']);
    
    include template('order_back_add');
    exit;
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT info1,info2,ordersn FROM `{$tablepre}order_back` WHERE uid='$uid' LIMIT 1");
    do
    {
        if(!$rtl) exit;
        $rtl['info1']=unserialize($rtl['info1']);
        $rtl['info2']=unserialize($rtl['info2']);
        if($rtl['info1']) file_unlink($rtl['info1']['img']);
        if($rtl['info2']) file_unlink($rtl['info2']['img']);
        
        admin_log("删除退货订单：$order_back[ordersn]");
        $db->query("DELETE FROM `{$tablepre}order_back` WHERE uid='$uid'");
        $db->free_result();
    }while (0);
    
    exit('OK');
}
else show_msg('pass_worng');


function claim_order_back($order_back)
{
    global $db,$tablepre,$mm_buy_point;
    $mm_buy_point=(int)$mm_buy_point;
    $b_rtl=false;
    $err='';
    
    do
    {
        $order_goods=$db->get_one("SELECT uid,order_id,buy_price,buy_point,buy_number FROM `{$tablepre}order_goods` WHERE uid='$order_back[og_uid]' LIMIT 1");
        if(!$order_goods)
        {
            $err='检索不到指定的订购商品';
            break;
        }
        $total_money=$order_goods['buy_price']*$order_goods['buy_number'];
        $total_point=$order_goods['buy_point']+$order_goods['buy_number'];
        
        $shop=$db->get_one("SELECT xb_money FROM `{$tablepre}member_shop` WHERE m_uid='$order_back[supplier_id]' LIMIT 1");
        $shop_m=cur_member_info($order_back['supplier_id']);
        if($shop_m['member_point']<$total_point)
        {
            $total_money+=($total_point-$shop_m['member_point'])/$mm_buy_point;
            $total_point=$shop_m['member_point'];
        }
        if($shop['xb_money']<$total_money)
        {
            $err='消保金额不足以支付账目索回';
            break;
        }

        add_xb($order_back['supplier_id'],-$total_money,'退货',"订单$order_back[ordersn]退货",$order_back['ordersn']);
        add_score($order_back['supplier_id'],-$total_point,'退货',"订单$order_back[ordersn]退货",$order_back['ordersn']);
        
        add_money($order_back['m_uid'],$order_goods['buy_price']*$order_goods['buy_number'],'退货',"订单$order_back[ordersn]退货",$order_back['ordersn']);
        add_score($order_back['m_uid'],$order_goods['buy_point']+$order_goods['buy_number'],'退货',"订单$order_back[ordersn]退货",$order_back['ordersn']);
        
        $db->query("UPDATE `{$tablepre}order_goods` SET status='2' WHERE uid='$order_goods[uid]'");
        $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_goods` WHERE order_id='$order_goods[order_id]' AND status<>'2'");
        if($rtl['cnt']<=0) $db->query("UPDATE `{$tablepre}order_info` SET status='6' WHERE uid='$order_goods[order_id]'");
        $db->free_result();
        
        $b_rtl=true;
    }while (0);
    
    return array($b_rtl,$err);
}