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
 * $Id: point.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require 'header.php';
require_once 'include/pager.class.php';

$mm_mall_title='积分汇';

$arr_brand_cache=$cache->get_cache("brand");
unset($arr_brand_cache[0]);
$arr_brand=array();
foreach ($arr_brand_cache as $key=>$val)
{
    $rtl=$db->get_one("SELECT change_num FROM `{$tablepre}brand_statistics` WHERE brand_uid='$key' LIMIT 1");
    $arr_brand[]=array(
        'brand_uid'=>$key,
        'brand_name'=>$val,
        'cnt'=>(int)$rtl['change_num']
    );
}

$arr_cat=array();
foreach ($cat_parent as $val)
{
    $rtl=$db->get_one("SELECT change_num FROM `{$tablepre}cat_statistics` WHERE cat_uid='$val[uid]' LIMIT 1");
    $arr_cat[]=array(
        'cat_uid'=>$val['uid'],
        'cat_name'=>$val['category_name'],
        'cnt'=>(int)$rtl['change_num']
    );
}

$search_sql=" WHERE gc.approval=1 AND ms.isSupplier=3 ";
$brand_uid=(int)$brand_uid;
$cat_uid=(int)$cat_uid;

//处理搜索条件
if($brand_uid>0) $search_sql.=" AND goods_brand=$brand_uid ";
if($cat_uid>0)
{
    include 'data/malldata/category.config.php';
    include 'data/malldata/category_pid.config.php';
    require_once 'include/cat_func.func.php';

    $children_uids=get_chidldren_uids($cat_uid,$uid_2_pid,$cat);
    array_push($children_uids,$cat_uid);
    $str_children_uids=implode(',',$children_uids);
    $search_sql.=" AND gc.goods_category IN ($str_children_uids)";
}

//处理排序
$ac=strtoupper($ac);
$od=strtolower($od);
if($ac!='ASC' && $ac!='DESC') $ac='DESC';
if($change=='change') $ac=$ac=='ASC'?'DESC':'ASC';    //顺序反转
if(!in_array($od,array('goods_sale_point','goods_sale_price','goods_hit','register_date'))) $od='register_date';
$order_sql=" ORDER BY gc.{$od} $ac";

$arr_point=array();
$page=(int)$page<=0?1:(int)$page;
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_change` gc 
                   LEFT JOIN `{$tablepre}member_shop` ms 
                   ON gc.supplier_id=ms.m_uid 
                   $search_sql");
$total_count = (int)$rtl['cnt'];
$list_num = 20;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q=$db->query("SELECT gc.uid,gc.goods_name,gc.goods_sale_price,gc.goods_sale_point,gc.goods_file1,gc.goods_status,gc.goods_stock,gc.supplier_id,gc.goods_hit 
               FROM `{$tablepre}goods_change` gc 
               LEFT JOIN `{$tablepre}member_shop` ms 
               ON gc.supplier_id=ms.m_uid 
               $search_sql 
               $order_sql 
               LIMIT $from_record,$list_num");
while ($rtl=$db->fetch_array($q))
{
    $rtl['sale_price']=$rtl['goods_sale_price'];
    
    if($rtl['goods_stock']<=0) $rtl['sold_out']='sold_out';
    if($rtl['sold_out']) $rtl['btn_cls']='but_gray';
    
    $rtl['goods_sale_point']=(int)$rtl['goods_sale_point'];
    $rtl=goods_array($rtl);
    $rtl['url']=GetBaseUrl('changegd_detail',$rtl['uid'],'action',$rtl['supplier_id']);
    $arr_point[]=$rtl;
}
$db->free_result();
$page_list = $rowset->link("point.php?&action=$action&brand_uid=$brand_uid&cat_uid=$cat_uid&od=$od&ac=$ac&page=");

include template('point');
footer();

