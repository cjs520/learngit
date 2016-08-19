<?php

/**
 * MVM_MALL 网上商店系统  商品管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: goods_storage.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if (!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

//商品管理列表
if($action=='list')
{
	$arr_goods=array();
    require_once MVMMALL_ROOT.'include/pager.class.php';
    
    $search_sql=" WHERE TRUE";
    $order_sql=" ORDER BY uid DESC";
    if($ps_subject)
    {
    	$ps_subject_txt=$ps_subject;
    	$search_sql.=" AND goods_name LIKE '%$ps_subject%'";
    	$ps_subject=urlencode($ps_subject);
    	$order_sql='';
    }
    $status=(int)$status;
    if($status==1)
    {
        $search_sql.=" AND approval_date='0'";
        $order_sql='';
    }
    else if($status==2)
    {
        $search_sql.=" AND approval_date>0";
        $order_sql='';
    }
    
    $cat_ids=array();
    $total_count = $db->counter("{$tablepre}goods_storage",$search_sql);
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT * FROM `{$tablepre}goods_storage` 
                     $search_sql 
                     $order_sql 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['status']=$rtl['approval_date']==0?'<span class="orange">未审核</span>':'已审核';
        $g=$db->get_one("SELECT goods_category,goods_brand,goods_sale_price,goods_code,supplier_id 
                         FROM `{$tablepre}goods_table` 
                         WHERE uid='$rtl[goods_uid]' LIMIT 1");
        if(!$g) continue;
        $rtl=array_merge($rtl,$g);
        $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$g[supplier_id]' LIMIT 1");
        $rtl['shop_name']=$shop['shop_name'];
        $rtl['shop_url']=GetBaseUrl('index','','',$g['supplier_id']);
        
        $rtl['goods_url'] = GetBaseUrl('product',$rtl['goods_uid'],'action',$rtl['supplier_id']);
        $cat_ids[]=$rtl['goods_category'];
        
        $arr_goods[] = $rtl;
    }
    $db->free_result();
    
    if($cat_ids)
    {
        $str_cat_ids=implode(',',$cat_ids);
        $q=$db->query("SELECT uid,category_name FROM `{$tablepre}category` WHERE uid IN ($str_cat_ids)");
        while($rtl=$db->fetch_array($q)) $cats[$rtl['uid']]=$rtl['category_name'];
    }
    
    foreach ($arr_goods as $key=>$val)
    {
        $arr_goods[$key]['goods_category']=$cats[$val['goods_category']];
    }
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&ps_subject=$ps_subject&status=$status&page=");
    
    $arr_status=array(0=>'-- 全部 --',1=>'未审核',2=>'已审核');
    $sel_status=drop_menu($arr_status,'status',$status);
    
    require_once template('goods_storage');
    footer();
}
else if($action=='edit')
{
	$uid=(int)$uid;
	$goods=$db->get_one("SELECT uid,goods_name FROM `{$tablepre}goods_storage` WHERE uid='$uid' LIMIT 1");
	if(!$goods) exit('ERROR:检索不到指定商品');
	admin_log("审核入库商品：$goods[goods_name]");
	$db->query("UPDATE `{$tablepre}goods_storage` SET approval_date='$m_now_time' WHERE uid='$uid'");
	$db->free_result();
	
	exit('OK:审核成功');
}
else if($action=='bat_edit')
{
    if($_POST && (int)$step==1)
    {
        if(!is_array($uids)) show_msg('请正确提交数据');
        foreach ($uids as $key=>$val)
        {
            $val=(int)$val;
            if($val>0) $uids[$key]=$val;
            else unset($uids[$key]);
        }
        if(!$uids) show_msg('请选择需要审核的库存商品');
        
        $str_uids=implode(',',$uids);
        $db->query("UPDATE `{$tablepre}goods_storage` SET approval_date='$m_now_time' WHERE uid IN ($str_uids)");
        $db->free_result();
        admin_log("批量审核入库商品");
    }
    
    show_msg('审核成功',base64_decode($_POST['p_url']));
}
else if($action=='del')
{
	$uid=(int)$uid;
	$goods=$db->get_one("SELECT uid,goods_name FROM `{$tablepre}goods_storage` WHERE uid='$uid' LIMIT 1");
	if($goods)
	{
	    admin_log("删除入库商品：$goods[goods_name]");
	    $db->query("DELETE FROM `{$tablepre}goods_storage` WHERE uid='$uid'");
	    $db->free_result();
	}
	
	exit('OK:删除成功');
}
else show_msg('pass_worng');