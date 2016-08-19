<?php

/**
 * MVM_MALL 网上商店系统  流量统计管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-10-01 $
 * $Id: sale_rank.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/pager.class.php';

if($action=='sale_list')
{
    $search_sql=" WHERE TRUE ";
    $order_sql="";
    $use_index="";
    $range=dhtmlchars($range);
    $range=in_array($range,array('month','week'))?$range:'all';
    $field='';
    
    if($range=='all')
    {
        $field='total_sale_money';
        $use_index=" FORCE INDEX (`total_sale_money`)";
    }
    else if($range=='month')
    {
        $field='total_sale_money_30';
        $use_index=" FORCE INDEX (`total_sale_money_30`)";
    }
    else if($range=='week')
    {
        $field='total_sale_money_7';
        $use_index="FORCE INDEX (`total_sale_money_7`)";
    }
    
    if($goods_name)
    {
        $search_sql.=" AND goods_name LIKE '%$goods_name%'";
    }
    $od=dhtmlchars($od)=='asc'?'asc':'desc';
    $order_sql=" ORDER BY `$field` $od";
    
    $arr_statistics=array();
    $total_count = $db->counter("{$tablepre}goods_statistics",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $i=1;
    $q = $db->query("SELECT g_uid,goods_table,goods_name,module,supplier_id,`$field` AS total_sale_money FROM `{$tablepre}goods_statistics` $use_index 
                     $search_sql
                     $order_sql 
                     LIMIT $from_record, $list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        if(!$shop) continue;
        $rtl['shop_name']=$shop['shop_name'];
        $rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);
        
        $rtl['goods_url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
        $rtl['total_sale_money']=currency($rtl['total_sale_money']);
        $rtl['rank']=($page-1)*$list_num+$i;
        $i++;
        $arr_statistics[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&od=$od&goods_name=".urlencode($goods_name)."&range=$range&page=");
    
    include template('goods_sale_rank');
    footer();
}
else if($action=='sale_num')
{
    $search_sql=" WHERE TRUE ";
    $order_sql="";
    $use_index="";
    $range=dhtmlchars($range);
    $range=in_array($range,array('month','week'))?$range:'all';
    $field='';
    
    if($range=='all')
    {
        $field='total_sale';
        $use_index=" FORCE INDEX (`total_sale`)";
    }
    else if($range=='month')
    {
        $field='total_sale_30';
        $use_index=" FORCE INDEX (`total_sale_30`)";
    }
    else if($range=='week')
    {
        $field='total_sale_7';
        $use_index="FORCE INDEX (`total_sale_7`)";
    }
    
    if($goods_name)
    {
        $search_sql.=" AND goods_name LIKE '%$goods_name%'";
    }
    $od=dhtmlchars($od)=='asc'?'asc':'desc';
    $order_sql=" ORDER BY `$field` $od";
    
    $arr_statistics=array();
    $total_count = $db->counter("{$tablepre}goods_statistics",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $i=1;
    $q = $db->query("SELECT g_uid,goods_table,goods_name,module,supplier_id,`$field` AS total_sale FROM `{$tablepre}goods_statistics` $use_index 
                     $search_sql
                     $order_sql 
                     LIMIT $from_record, $list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        if(!$shop) continue;
        $rtl['shop_name']=$shop['shop_name'];
        $rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);
        
        $rtl['goods_url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
        $rtl['rank']=($page-1)*$list_num+$i;
        $i++;
        $arr_statistics[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&od=$od&goods_name=".urlencode($goods_name)."&range=$range&page=");
    
    include template('goods_sale_num_rank');
    footer();
}
else if($action=='back_num')
{
    $search_sql=" WHERE TRUE ";
    $order_sql="";
    $use_index="";
    $range=dhtmlchars($range);
    $range=in_array($range,array('month','week'))?$range:'all';
    $field='';
    
    if($range=='all')
    {
        $field='back_num';
        $use_index=" FORCE INDEX (`back_num`)";
    }
    else if($range=='month')
    {
        $field='back_num_30';
        $use_index=" FORCE INDEX (`back_num_30`)";
    }
    else if($range=='week')
    {
        $field='back_num_7';
        $use_index="FORCE INDEX (`back_num_7`)";
    }
    
    if($goods_name)
    {
        $search_sql.=" AND goods_name LIKE '%$goods_name%'";
    }
    $od=dhtmlchars($od)=='asc'?'asc':'desc';
    $order_sql=" ORDER BY `$field` $od";
    
    $arr_statistics=array();
    $total_count = $db->counter("{$tablepre}goods_statistics",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $i=1;
    $q = $db->query("SELECT g_uid,goods_table,goods_name,module,supplier_id,`$field` AS back_num FROM `{$tablepre}goods_statistics` $use_index 
                     $search_sql
                     $order_sql 
                     LIMIT $from_record, $list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        if(!$shop) continue;
        $rtl['shop_name']=$shop['shop_name'];
        $rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);
        
        $rtl['goods_url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
        $rtl['rank']=($page-1)*$list_num+$i;
        $i++;
        $arr_statistics[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&od=$od&goods_name=".urlencode($goods_name)."&range=$range&page=");
    
    include template('goods_back_num_rank');
    footer();
}
else if($action=='goods_hit')
{
    $search_sql=" WHERE TRUE ";
    $order_sql="";
    
    $field='goods_hit';
    $use_index=" FORCE INDEX (`goods_hit`)";

    if($goods_name)
    {
        $search_sql.=" AND goods_name LIKE '%$goods_name%'";
    }
    $od=dhtmlchars($od)=='asc'?'asc':'desc';
    $order_sql=" ORDER BY `$field` $od";
    
    $arr_statistics=array();
    $total_count = $db->counter("{$tablepre}goods_statistics",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $i=1;
    $q = $db->query("SELECT g_uid,goods_table,goods_name,module,supplier_id,`$field` FROM `{$tablepre}goods_statistics` $use_index 
                     $search_sql
                     $order_sql 
                     LIMIT $from_record, $list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        if(!$shop) continue;
        $rtl['shop_name']=$shop['shop_name'];
        $rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);
        
        $rtl['goods_url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
        $rtl['rank']=($page-1)*$list_num+$i;
        $i++;
        $arr_statistics[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&od=$od&goods_name=".urlencode($goods_name)."&range=$range&page=");
    
    include template('goods_hit_rank');
    footer();
}
else show_msg('参数传递错误');