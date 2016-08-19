<?php

/**
 * MVM_MALL 网上商店系统  分类显示
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: category.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'header.php';
require_once 'include/pager.class.php';
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';

$page = $page ? (int)$page : 1;
$action = (int)$action;
$cat_list = $db->get_one("SELECT uid,category_name,category_key,category_desc,att_list,category_id 
                          FROM `{$tablepre}category` 
                          WHERE uid = $action AND supplier_id=0 
                          LIMIT 1");
!$cat_list && show_msg('检索不到指定的商品分类');
if($cat_list['category_id']<=0) move_page("sub.php?action=$action");

$mm_mall_title = $cat_list['category_name'];    //标题
$cat_list['category_key'] && $mm_keywords = $cat_list['category_key'];    //关键字
$cat_list['category_desc'] && $mm_description = $cat_list['category_desc'];    //描述

$children_uids=get_chidldren_uids($action,$uid_2_pid,$cat);
array_push($children_uids,(int)$action);
$str_children_uids=implode(',',$children_uids);
$cat_in = $str_children_uids;
$search_sql  = "WHERE goods_category IN($cat_in)";

$cat_level=sizeof(get_parents($action,$uid_2_pid))-1;
$cat_level>=3?$cat_list['att_list']=unserialize($cat_list['att_list']):$cat_list['att_list']=false;

$sellshow=(int)$sellshow;
if($sellshow!=1 && $sellshow!=2) $sellshow=1;
$goods_table=$sellshow==1?"{$tablepre}goods_table":"{$tablepre}goods_show";

$ps_search=trim(mb_substr($ps_search,0,10,'UTF-8'));
$min_money=floatval($min_money);
$max_money=floatval($max_money);
$min_money>0?$search_sql.=" AND goods_sale_price>='$min_money' ":$min_money='';
$max_money>0?$search_sql.=" AND goods_sale_price<='$max_money' ":$max_money='';
if($ps_search)
{
    $search_sql.=" AND goods_name LIKE '%$ps_search%' ";
    $ps_search_txt=$ps_search;
    $ps_search=urlencode($ps_search);
}

//品牌处理
$brand_uid=(int)$brand_uid;
if($brand_uid>0) $search_sql.=" AND goods_brand='$brand_uid'";

//属性处理
do
{
    if($cat_level<3) break;
    if(!$attr) break;
    $arr_attr=explode('|',$attr);
    foreach ($arr_attr as $val)
    {
        if(!$val) continue;
        $val=base64_encode($val);
        $search_sql.=" AND filter_attr LIKE '%$val%'";
    }
    $attr=urlencode($attr);
}while (0);

//排序处理
if($rel=='change') $ac=$ac=='ASC'?'DESC':'ASC';
if($ac!='ASC' && $ac!='DESC') $ac='DESC';
$od=strtolower($od);
if(!in_array($od,array('register_date','goods_sale_price','goods_hit'))) $od='register_date';
$order_sql=" ORDER BY $od $ac";

$rtl = $db->get_one("SELECT COUNT(*) AS cnt FROM `$goods_table` $search_sql");
$total_count = $rtl['cnt'];
$list_num = 20;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();

$q = $db->query("SELECT uid,goods_name,goods_sale_price,goods_file1,goods_status,goods_stock,supplier_id,goods_hit 
                 FROM `$goods_table` 
                 $search_sql 
                 $order_sql 
                 LIMIT $from_record, $list_num");

while($list = $db->fetch_array($q))
{
    $tmp_arr = goods_array($list);
    if($ps_search_txt) $tmp_arr['goods_name']=str_replace($ps_search_txt,"<b style='color:red;'>$ps_search_txt</b>",$tmp_arr['goods_name']);

    $goods[]=$tmp_arr;
}
$db->free_result();

$search_query="sellshow=$sellshow".
              '&ps_search='.urlencode($ps_search).
              '&min_money='.urldecode($min_money).'&max_money='.urlencode($max_money);

$page_list = $rowset->link("category.php?action=$action&od=$od&ac=$ac&$search_query&attr=$attr&page=");


$navigation = $cat_list['category_name'];    //导航
$navigation = "<a href='$_URL[0]'>首页</a> ";
$parents=get_parents($action,$uid_2_pid);
$node=null;
foreach ($parents as $val)
{
    if(!$node) $node=$cat[$val];
    else $node=$node['child'][$val];
    if(!$node['data']['category_name']) continue;
    $navigation.=" > <a href='".GetBaseUrl('category',$node['data']['uid'])."'>{$node[data][category_name]}</a>";
}

//品牌列表
$brand_list=array();
$rtl=$db->get_one("SELECT brand_uids FROM `{$tablepre}cat_statistics` WHERE cat_uid='$action' LIMIT 1");
if($rtl['brand_uids'])
{
    $str_brand_uids=$rtl['brand_uids'];
    $q=$db->query("SELECT id,brandname FROM `{$tablepre}brand_table` WHERE id IN($str_brand_uids)");
    while($rtl=$db->fetch_array($q))
    {
        $brand_list[]=$rtl;
    }
    $db->free_result();
}

//下级分类
$lower_cat=array();
$q=$db->query("SELECT uid,category_name FROM `{$tablepre}category` WHERE supplier_id=0 AND category_id=$action ORDER BY category_rank");
while ($rtl=$db->fetch_array($q))
{
    $lower_cat[]=$rtl;
}
$db->free_result();

require_once template('category_grid');
footer();
