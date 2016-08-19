<?php

/**
 * MVM_MALL 网上商店系统 首页文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: miaosha.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require_once 'include/pager.class.php';
require 'header.php';

$mm_mall_title='秒杀';

$arr_brand_cache=$cache->get_cache("brand");
unset($arr_brand_cache[0]);
$arr_brand=array();
foreach ($arr_brand_cache as $key=>$val)
{
    $rtl=$db->get_one("SELECT onsale_num FROM `{$tablepre}brand_statistics` WHERE brand_uid='$key' LIMIT 1");
    $arr_brand[]=array(
        'brand_uid'=>$key,
        'brand_name'=>$val,
        'cnt'=>(int)$rtl['onsale_num']
    );
}

$arr_cat=array();
foreach ($cat_parent as $val)
{
    $rtl=$db->get_one("SELECT onsale_num FROM `{$tablepre}cat_statistics` WHERE cat_uid='$val[uid]' LIMIT 1");
    $arr_cat[]=array(
        'cat_uid'=>$val['uid'],
        'cat_name'=>$val['category_name'],
        'cnt'=>(int)$rtl['onsale_num']
    );
}

//处理搜索条件
$search_sql=" WHERE ms.isSupplier=3 ";
if($brand_uid>0) $search_sql.=" AND go.goods_brand=$brand_uid ";
if($cat_uid>0)
{
    include 'data/malldata/category.config.php';
    include 'data/malldata/category_pid.config.php';
    require_once 'include/cat_func.func.php';

    $children_uids=get_chidldren_uids($cat_uid,$uid_2_pid,$cat);
    array_push($children_uids,$cat_uid);
    $str_children_uids=implode(',',$children_uids);
    $search_sql.=" AND go.goods_category IN ($str_children_uids)";
}

$status=(int)$status;
if(!in_array($status,array(0,1,2))) $status=0;
if($status==0) $search_sql.=" AND go.start_date<=$m_now_time AND go.end_date>=$m_now_time";
else if($status==1) $search_sql.=" AND go.start_date>$m_now_time";
else if($status==2) $search_sql.=" AND go.end_date<=$m_now_time";

//处理排序
$ac=strtoupper($ac);
$od=strtolower($od);
if($ac!='ASC' && $ac!='DESC') $ac='ASC';
if($change=='change') $ac=$ac=='ASC'?'DESC':'ASC';    //顺序反转
if(!in_array($od,array('start_date','end_date','goods_sale_price','goods_hit'))) $od='start_date';
$order_sql=" ORDER BY go.{$od} $ac";
if($od=='start_date') $use_index="FORCE INDEX (`start_date`)";

$arr_onsale=array();
$page=(int)$page<=0?1:(int)$page;
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_onsale` go 
                   LEFT JOIN `{$tablepre}member_shop` ms 
                   ON go.supplier_id=ms.m_uid 
                   $search_sql");
$total_count = (int)$rtl['cnt'];
$list_num = 20;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q=$db->query("SELECT go.uid,go.goods_name,go.goods_sale_price,go.goods_file1,go.goods_status,go.goods_stock,go.supplier_id,go.goods_hit,go.start_date,go.end_date,
                      god.goods_market_price 
               FROM `{$tablepre}goods_onsale` go $use_index 
               LEFT JOIN `{$tablepre}member_shop` ms 
               ON go.supplier_id=ms.m_uid 
               LEFT JOIN `{$tablepre}goods_onsale_detail` god 
               ON god.g_uid=go.uid 
               $search_sql 
               $order_sql 
               LIMIT $from_record,$list_num");
while ($rtl=$db->fetch_array($q))
{
    $rtl['goods_market_price']=$rtl['goods_market_price'];
    
    $rtl['sale_price']=$rtl['goods_sale_price'];
    $rtl['left_time']=$rtl['end_date']-$m_now_time;
    $rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);
    
    if($rtl['goods_stock']<=0) $rtl['sold_out']='sold_out';
    else if($rtl['start_date']>$m_now_time) $rtl['sold_out']='sold_begin';
    else if($rtl['end_date']<$m_now_time) $rtl['sold_out']='sold_over';
    if($rtl['sold_out']) $rtl['btn_cls']='but_gray';
    
    $rtl=goods_array($rtl);
    $rtl['url']=GetBaseUrl('salegd_detail',$rtl['uid'],'action',$rtl['supplier_id']);
    $arr_onsale[]=$rtl;
}
$db->free_result();
$page_list = $rowset->link("miaosha.php?&action=$action&brand_uid=$brand_uid&cat_uid=$cat_uid&od=$od&ac=$ac&status=$status&page=");

include template('miaosha');
footer();
