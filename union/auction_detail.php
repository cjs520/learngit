<?php

/**
 * MVM_MALL 网上商店系统  商品显示
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-25 $
 * $Id: auction_detail.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require_once 'header.php';

$goods_table="{$tablepre}goods_auction";
$detail_table="{$tablepre}goods_auction_detail";
$uid=(int)$action;
$product=$db->get_one("SELECT uid,goods_name,goods_status,start_price,end_price,start_date,end_date,is_complete,goods_file1,filter_attr,assure,bid_add 
                       FROM `$goods_table` 
                       WHERE uid='$uid' AND supplier_id='$page_member_id' AND approval=1
                       LIMIT 1");
if(!$product) show_msg('检索不到指定商品');

$detail=$db->get_one("SELECT goods_file2 FROM `$detail_table` WHERE g_uid='$product[uid]' LIMIT 1");
$product=array_merge($detail,$product);

$product['addoption'] = '';
$product['addoption'] .= ($product['goods_status'] & 4) ? '<span class="free_deliver"></span>':'';
$product['filter_attr']=SplitFilterAttr($product['filter_attr']);
$arr_attr=SplitAttr($product['attr_val']);

$product['status']=1;    //进行中
if($m_now_time<$product['start_date']) $product['status']=0;    //未开始
else if($product['is_complete'] || $m_now_time>=$product['end_date']) $product['status']=2;    //已结束

$product['start_price']=currency($product['start_price']);
$product['end_price']=currency($product['end_price']);
$product['assure']=currency($product['assure']);
$bid_add=$product['bid_add'];
$product['bid_add']=currency($product['bid_add']);
$mvm_member['member_money']=currency($mvm_member['member_money']);

if($product['status']==1)
{
    $left_time=$product['end_date']-$m_now_time;
}
else if($product['status']==0)
{
    $left_time=$product['start_date']-$m_now_time;
}

do
{
    $assure=false;
    if(!$m_check_uid) break;
    $assure=$db->get_one("SELECT money FROM `{$tablepre}goods_auction_assure` WHERE m_uid='$m_check_uid' AND g_uid='$product[uid]' LIMIT 1");;
}while (0);

//product gallery
$arr_gallery=array();
$arr_gallery[]=array($product['goods_file1'],$product['goods_file2']);
foreach ($arr_gallery as $key=>$val)
{
    if(!file_exists($val[0])) $arr_gallery[$key][0]='images/noimages/noproduct.jpg';
    if(!file_exists($val[1])) $arr_gallery[$key][1]='images/noimages/noproduct.jpg';
}
$q=$db->query("SELECT thumb,imgbig FROM `{$tablepre}auction_gallery` WHERE goods_id='$product[uid]' LIMIT 5");
while ($rtl=$db->fetch_array($q))
{
    if(!file_exists($rtl['thumb'])) $rtl['thumb']='images/noimages/noproduct.jpg';
    if(!file_exists($rtl['imgbig'])) $rtl['imgbig']='images/noimages/noproduct.jpg';
    $arr_gallery[]=array($rtl['thumb'],$rtl['imgbig']);
}
$db->free_result();

//qrcode
$qrcode_img=create_qrcode(md5($product['goods_name']),GetBaseUrl('auction_detail',$product['uid'],'action','1',$page_member_id));

//my history view
$history_title=$m_check_uid?'我看过的':'你可能喜欢';
$arr_history=array();
if($m_check_uid)
{
    $arr_g_uid=false;
    $history=$db->get_one("SELECT history FROM `{$tablepre}view_history` WHERE m_uid='$m_check_uid' AND module='$script' LIMIT 1");
    if($history) $arr_g_uid=unserialize($history['history']);
    if(!is_array($arr_g_uid) || !$arr_g_uid) $arr_g_uid=array();
    array_push($arr_g_uid,$product['uid']);
    $arr_g_uid=array_unique($arr_g_uid);
    while (sizeof($arr_g_uid)>6) array_pop($arr_g_uid);
    $arr_g_uid=array_map('intval',$arr_g_uid);
    
    $str_g_uid=implode(',',$arr_g_uid);
    $history_g_uid=serialize($arr_g_uid);
    $db->query("REPLACE INTO `{$tablepre}view_history` (m_uid,module,history) 
                VALUES ('$m_check_uid','$script','$history_g_uid')");
    
    $q=$db->query("SELECT uid,goods_name,goods_file1,supplier_id FROM `{$goods_table}` WHERE uid IN ($str_g_uid) LIMIT 6");
    while ($rtl=$db->fetch_array($q))
    {
        if(!file_exists($rtl['goods_file1'])) $rtl['goods_file1']='images/noimages/noproduct.jpg';
        $rtl['url']=GetBaseUrl('auction_detail',$rtl['uid'],'action','1',0,$rtl['supplier_id']);
        $arr_history[]=$rtl;
    }
    $db->free_result();
    
}
else
{
    $q=$db->query("SELECT uid,goods_name,goods_file1 FROM `{$goods_table}` 
                   WHERE supplier_id='$page_member_id' AND approval=1 
                   ORDER BY start_date DESC LIMIT 6");
    while ($rtl=$db->fetch_array($q))
    {
        if(!file_exists($rtl['goods_file1'])) $rtl['goods_file1']='images/noimages/noproduct.jpg';
        $rtl['url']=GetBaseUrl('auction_detail',$rtl['uid']);
        $arr_history[]=$rtl;
    }
    $db->free_result();
}
//relation
$arr_relation=array();
$q=$db->query("SELECT uid,goods_file1,goods_name FROM `$goods_table` 
               WHERE supplier_id='$page_member_id' AND approval=1
               ORDER BY start_date DESC LIMIT 7");
while ($rtl=$db->fetch_array($q))
{
    if(!$rtl['goods_file1'] || !file_exists($rtl['goods_file1'])) $rtl['goods_file1']='images/noimages/noproduct.jpg';
    $rtl['url']=GetBaseUrl($script,$rtl['uid']);
    $arr_relation[]=$rtl;
}
$db->free_result();

$mm_mall_title=$product['goods_name'];
include_once template('product_auction');
footer();