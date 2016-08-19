<?php

/**
 * MVM_MALL 网上商店系统  品牌
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-27 $
 * $Id: preorder.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'include/pager.class.php';
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';
require 'header.php';

$arr_brand_cache=$cache->get_cache("brand");
unset($arr_brand_cache[0]);
$arr_brand=array();
foreach ($arr_brand_cache as $key=>$val)
{
    $rtl=$db->get_one("SELECT preorder_num FROM `{$tablepre}brand_statistics` WHERE brand_uid='$key' LIMIT 1");
    $arr_brand[]=array(
        'brand_uid'=>$key,
        'brand_name'=>$val,
        'cnt'=>(int)$rtl['preorder_num']
    );
}

$arr_cat=array();
foreach ($cat_parent as $val)
{
    $rtl=$db->get_one("SELECT preorder_num FROM `{$tablepre}cat_statistics` WHERE cat_uid='$val[uid]' LIMIT 1");
    $arr_cat[]=array(
        'cat_uid'=>$val['uid'],
        'cat_name'=>$val['category_name'],
        'cnt'=>(int)$rtl['preorder_num']
    );
}


$search_sql = "WHERE gt.type=9 AND ms.isSupplier=3 ";
if($brand_uid>0) $search_sql.=" AND goods_brand=$brand_uid ";
if($cat_uid>0)
{
    include 'data/malldata/category.config.php';
    include 'data/malldata/category_pid.config.php';
    require_once 'include/cat_func.func.php';

    $children_uids=get_chidldren_uids($cat_uid,$uid_2_pid,$cat);
    array_push($children_uids,$cat_uid);
    $str_children_uids=implode(',',$children_uids);
    $search_sql.=" AND gt.goods_category IN ($str_children_uids)";
}

//处理排序
if($rel=='change') $ac=$ac=='ASC'?'DESC':'ASC';
if($ac!='ASC' && $ac!='DESC') $ac='DESC';
$od=strtolower($od);
if(!in_array($od,array('register_date','goods_sale_price','goods_hit'))) $od='register_date';
$order_sql=" ORDER BY gt.{$od} $ac";

$arr_goods=array();
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_table` gt 
		           LEFT JOIN `{$tablepre}member_shop` ms 
                   ON gt.supplier_id=ms.m_uid 
		           $search_sql");
$total_count = (int)$rtl['cnt'];
$page=(int)$page<=0?1:(int)$page;
$list_num = 15;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q=$db->query("SELECT gt.uid,gt.goods_name,gt.goods_sale_price,gt.goods_file1,gt.goods_status,gt.goods_stock,gt.supplier_id,gt.goods_hit,gt.down_payment 
		       FROM `{$tablepre}goods_table` gt 
		       LEFT JOIN `{$tablepre}member_shop` ms 
               ON gt.supplier_id=ms.m_uid 
		       $search_sql 
		       $order_sql 
		       LIMIT $from_record,$list_num");
while($rtl = $db->fetch_array($q))
{
    $rtl['down_payment']=currency($rtl['down_payment']);
    $arr_goods[]=goods_array($rtl);
}
$db->free_result();

$page_list = $rowset->link("preorder.php?cat_uid=$cat_uid&brand_uid=$brand_uid&od=$od&page=");
$id = (int)$id;

$mm_mall_title = '预定';

include template('preorder');
footer();

