<?php

/**
 * MVM_MALL 网上商店系统  商品搜索
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-03-01 $
 * $Id: search.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';


require_once'include/pager.class.php';
$search_key  = '';
$sellshow = (int)$sellshow;
if($sellshow==1)
{
    $class_sell = 'class="now"';
    $goods_table="{$tablepre}goods_table";
    $sellshow_1_checked='checked';
}
else
{
    $class_show = 'class="now"';
    $goods_table="{$tablepre}goods_show";
    $sellshow_2_checked='checked';
}

$ps_search = dhtmlchars($ps_search);
if($ps_search == '') move_page('goodcat.php');
$search_key = " AND (goods_name LIKE '%$ps_search%')";
$mm_mall_title="搜索：$ps_search";


//分类搜索
$category_id = (int)$_GET['category_id'];
if($_POST)
{
    foreach ($goods_cat as $val)
    {
        $val=(int)$val;
        if($val>0) $category_id=$val;
    }
}
if($category_id>0)
{
    $children_uids=get_chidldren_uids($category_id,$uid_2_pid,$cat);
    array_push($children_uids,(int)$category_id);
    $cat_in=implode(',',$children_uids);

    $cat_search = "  AND goods_category IN($cat_in) ";
}
$brand_search = $ps_brand ? ' AND goods_brand=' .intval($ps_brand): '';    //品牌搜索
//价格搜索
$max_money=floatval($max_money);
if($max_money>0) $max_search=" AND goods_sale_price<=$max_money";
$min_money=floatval($min_money);
if($min_money>0) $min_search=" AND goods_sale_price>=$min_money";

$province_s = dhtmlchars($province_s);
$city_s = dhtmlchars($city_s);
$county_s = dhtmlchars($county_s);
if($province_s) $search_key .= " AND mt.province='$province_s'";
if($city_s) $search_key .= " AND mt.city='$city_s'";
if($county_s) $search_key .= " AND mt.county='$county_s'";

//生成搜索的sql
$search_sql = "WHERE TRUE " . $cat_search . $search_key . $brand_search . $min_search . $max_search ;
$rtl = $db->get_one("SELECT COUNT(*) AS cnt FROM `$goods_table` gt $search_sql");
$total_count = (int)$rtl['cnt'];
$page = $page ? (int)$page:1;
$list_num = 20;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();

//排序处理
if($rel=='change') $ps_ac = $ps_ac == 'ASC'?'DESC':'ASC';
!$ps_ac && $ps_ac='ASC';
!$order && $order='shoplevel';
switch ($order)
{
    case 'price':
        $order_by = " ORDER BY goods_sale_price $ps_ac ";
        break;
    case 'stock':
        $order_by = " ORDER BY goods_stock $ps_ac ";
        break;
    case 'date':
        $order_by = " ORDER BY register_date $ps_ac ";
        break;
    case 'shoplevel':
        $order_by = " ORDER BY goods_hit $ps_ac ";
        break;
}

$arr_brand=$cache->get_cache('brand');
$arr_goods=array();
$q = $db->query("SELECT uid,goods_name,goods_sale_price,goods_file1,goods_status,supplier_id,goods_hit,goods_category,goods_brand,type 
                 FROM `$goods_table`
	             $search_sql 
	             $order_by 
	             LIMIT $from_record, $list_num");
while($list = $db->fetch_array($q))
{
    $tmp = goods_array($list);
    $tmp['goods_name'] = str_replace($ps_search,"<b class='red'>$ps_search</b>",$tmp['goods_name']);
    $tmp['goods_brand']=$arr_brand[$tmp['goods_brand']];
    
    $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$list[supplier_id]' LIMIT 1");
    $tmp['shop_name']=$shop['shop_name'];
    $tmp['shop_url']=GetBaseUrl('index','','',$tmp['supplier_id']);
    
    $cat=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$tmp[goods_category]' LIMIT 1");
    $tmp['cat_url']=GetBaseUrl('category',$tmp['goods_category']);
    $tmp['goods_category']=$cat['category_name'];
    
    
    
    $arr_goods[] = $tmp;
}
$db->free_result();

$ps_search_txt=$ps_search;
$ps_search = urlencode($ps_search);
$page_list = $rowset->link("search.php?action=$action&category_id=$category_id&ps_search=$ps_search&ps_brand=$ps_brand&min_money=$min_money&max_money=$max_money&sellshow=$sellshow&order=$order&ps_ac=$ps_ac&province_s=".urlencode($province_s)."&city_s=".urlencode($city_s)."&county_s=".urlencode($county_s)."&page=");

$price_url="search.php?action=$action&category_id=$category_id&ps_search=$ps_search&ps_brand=$ps_brand&min_money=$min_money&max_money=$max_money&sellshow=$sellshow&order=price&ps_ac=$ps_ac&rel=change&page=$page";
$stock_url="search.php?action=$action&category_id=$category_id&ps_search=$ps_search&ps_brand=$ps_brand&min_money=$min_money&max_money=$max_money&sellshow=$sellshow&order=stock&ps_ac=$ps_ac&rel=change&page=$page";
$date_url="search.php?action=$action&category_id=$category_id&ps_search=$ps_search&ps_brand=$ps_brand&min_money=$min_money&max_money=$max_money&sellshow=$sellshow&order=date&ps_ac=$ps_ac&rel=change&page=$page";
$shoplevel_url="search.php?action=$action&category_id=$category_id&ps_search=$ps_search&ps_brand=$ps_brand&min_money=$min_money&max_money=$max_money&sellshow=$sellshow&order=shoplevel&ps_ac=$ps_ac&rel=change&page=$page";

$sel_brand=drop_menu($arr_brand,'ps_brand',$ps_brand);

require_once template('search');
footer();