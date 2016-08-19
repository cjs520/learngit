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
 * $Id: consume_rank.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/pager.class.php';

if($action=='list')
{
    $search_sql=" WHERE supplier_id='0' ";
    $order_sql="";
    $use_index="";
    $range=dhtmlchars($range);
    $range=in_array($range,array('month','week'))?$range:'all';
    $field='';
    $od=dhtmlchars($od)=='asc'?'asc':'desc';
    
    if($range=='all')
    {
        $field='consume AS consume,buy_num AS buy_num';
        $order_sql=" ORDER BY `consume` $od";
        $use_index=" FORCE INDEX (`supplier_id`)";
    }
    else if($range=='month')
    {
        $field='consume_30 AS consume,buy_num_30 AS buy_num';
        $order_sql=" ORDER BY `consume_30` $od";
        $use_index=" FORCE INDEX (`supplier_id_2`)";
    }
    else if($range=='week')
    {
        $field='consume_7 AS consume,buy_num_7 AS buy_num';
        $order_sql=" ORDER BY `consume_7` $od";
        $use_index="FORCE INDEX (`supplier_id_3`)";
    }
    
    if($m_id) $search_sql.=" AND m_id='$m_id'";

    $arr_statistics=array();
    $total_count = $db->counter("{$tablepre}member_consume_statistics",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $i=1;
    $q = $db->query("SELECT m_id,$field FROM `{$tablepre}member_consume_statistics` $use_index 
                     $search_sql
                     $order_sql 
                     LIMIT $from_record, $list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['shop_name']=$shop['shop_name'];
        $rtl['shop_url']=GetBaseUrl('index','','',$rtl['m_uid']);
        $rtl['rank']=($page-1)*$list_num+$i;
        $rtl['consume']=currency($rtl['consume']);
        $i++;
        $arr_statistics[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&od=$od&m_id=".urlencode($m_id)."&range=$range&page=");
    
    include template('consume_rank');
    footer();
}
else show_msg('参数传递错误');
