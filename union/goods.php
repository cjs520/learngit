<?php

/**
 * MVM_MALL 网上商店系统 商品展示位置
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-04-12 $
 * $Id: goods.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'header.php';
require_once 'include/pager.class.php';

$action=dhtmlchars($action);
if(!$action) $action='new';

$search_sql=" WHERE supplier_id='$page_member_id'";

$page_title='';
switch ($action)
{
    case 'sales':
        $mm_mall_title=$page_title='折扣专区';
        $search_sql.=" AND type=3";
        $tpl='goods_sale';
        $arr_discount=$cache->get_cache('grade_discount',$page_member_id);
        $discount=get_goods_discount($arr_discount);
        break;
    case 'bat':
        $mm_mall_title=$page_title='批发专区';
        $search_sql.=" AND type=2";
        $tpl='goods_bat';
        break;
    default:
        $mm_mall_title=$page_title='新品抢购';
        if($shop_file['sellshow']==1) $search_sql.=" AND type IN (0,1)";
        else if($shop_file['sellshow']==2) $search_sql.=" AND type=8";
        $tpl='goods';
        break;
}

$arr_goods=array();
$total_count = $db->counter("$goods_table",$search_sql);
$page = $page ? (int)$page : 1;
$list_num = 20;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q=$db->query("SELECT uid,goods_name,goods_file1,supplier_id,goods_status,goods_sale_price,goods_hit,goods_stock 
               FROM `$goods_table` 
               $search_sql 
               ORDER BY register_date DESC 
               LIMIT $from_record,$list_num");
while ($rtl=$db->fetch_array($q))
{
    $rtl['goods_name']=str_replace($ps_search,"<font color='red'>$ps_search</font>",$rtl['goods_name']);
    if($action=='sales')
    {
        $rtl['discount_price']=currency($rtl['goods_sale_price']*$discount);
    }
    else if($action=='bat')
    {
        $rtl_tmp=$db->get_one("SELECT wholesale_price FROM `{$tablepre}goods_detail` WHERE g_uid='$rtl[uid]' LIMIT 1");
        $rtl['wholesale_price']=unserialize($rtl_tmp['wholesale_price']);
    }
    
    $rtl=goods_array($rtl);
    $arr_goods[]=$rtl;
}
$db->free_result();

$page_list = $rowset->link("goods.php?action=$action&page=");

require_once template($tpl);
footer();
