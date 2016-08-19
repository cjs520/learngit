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
 * $Id: coupon.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';

$mm_mall_title='优惠券';

$arr_free_coupon=array();
$q=$db->query("SELECT uid,supplier_id,end_date,discount,coupon_img,price_lbound 
               FROM `{$tablepre}coupon_cat` 
               WHERE od>10000 AND end_date>$m_now_time AND handout_type=0
               ORDER BY od DESC 
               LIMIT 10");
while ($rtl=$db->fetch_array($q))
{
    $rtl['coupon_img']=ProcImgPath($rtl['coupon_img']);
    $rtl['url']=GetBaseUrl('coupon','list','action',$rtl['supplier_id']);
    $rtl['end_date']=date('Y年m月d日',$rtl['end_date']);
    $rtl['discount']=round($rtl['discount']);
    
    $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
    if($shop) $rtl=array_merge($rtl,$shop);
    
    $arr_free_coupon[]=$rtl;
}
$db->free_result();


$arr_exchange_coupon=array();
$q=$db->query("SELECT uid,supplier_id,end_date,discount,coupon_img,price_lbound,sale_price 
               FROM `{$tablepre}coupon_cat` 
               WHERE od>10000 AND end_date>$m_now_time AND handout_type=1
               ORDER BY od DESC 
               LIMIT 10");
while ($rtl=$db->fetch_array($q))
{
    $rtl['coupon_img']=ProcImgPath($rtl['coupon_img']);
    $rtl['url']=GetBaseUrl('coupon','list','action',$rtl['supplier_id']);
    $rtl['end_date']=date('Y年m月d日',$rtl['end_date']);
    $rtl['discount']=round($rtl['discount']);
    $rtl['sale_price']=round($rtl['sale_price']);
    
    $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
    if($shop) $rtl=array_merge($rtl,$shop);
    
    $arr_exchange_coupon[]=$rtl;
}
$db->free_result();

$arr_order_coupon=array();
$q=$db->query("SELECT uid,supplier_id,end_date,discount,coupon_img,price_lbound,sale_price 
               FROM `{$tablepre}coupon_cat` 
               WHERE od>10000 AND end_date>$m_now_time AND handout_type=2
               ORDER BY od DESC 
               LIMIT 10");
while ($rtl=$db->fetch_array($q))
{
    $rtl['coupon_img']=ProcImgPath($rtl['coupon_img']);
    $rtl['url']=GetBaseUrl('coupon','list','action',$rtl['supplier_id']);
    $rtl['end_date']=date('Y年m月d日',$rtl['end_date']);
    $rtl['discount']=round($rtl['discount']);
    $rtl['sale_price']=round($rtl['sale_price']);
    
    $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
    if($shop) $rtl=array_merge($rtl,$shop);
    
    $arr_order_coupon[]=$rtl;
}
$db->free_result();


require 'header.php';
include template('coupon');
footer();