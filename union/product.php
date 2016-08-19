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
 * $Id: product.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require_once 'header.php';

$uid=(int)$action;
if($shop_file['sellshow']==1) $fields=",down_payment";
$product=$db->get_one("SELECT uid,goods_name,goods_sale_price,goods_code,goods_file1,goods_brand,goods_status,goods_stock,filter_attr,type{$fields} 
                       FROM `$goods_table` 
                       WHERE uid='$uid' AND supplier_id='$page_member_id' 
                       LIMIT 1");
if(!$product || !in_array($product['type'],array(0,1,2,3,8,9))) show_msg('检索不到指定的商品');

$detail_fields="goods_key,goods_market_price,goods_file2,attr_val,attr_store";
if($shop_file['sellshow']==1) $detail_fields.=',wholesale_price ';
else if($shop_file['sellshow']==2) $detail_fields.=',company,goods_url,tel,address,contact';
$detail=$db->get_one("SELECT $detail_fields FROM `$detail_table` 
                      WHERE g_uid='$uid' 
                      LIMIT 1");
$product=array_merge($product,$detail);
unset($detail);

$db->query("UPDATE `$goods_table` SET goods_hit=goods_hit+1 WHERE uid='$uid'");
$db->free_result();

//start to proc product info
if($product['type']==3)    //member_discount
{
    $arr_discount=$cache->get_cache('grade_discount',$page_member_id);
    $discount=get_goods_discount($arr_discount);
    $product['goods_sale_price']*=floatval($discount);
}

if($product['type']==9)    //preorder
{
    $product['down_payment']=currency($product['down_payment']);
}

$product['ori_price']=$product['goods_sale_price'];
$product['goods_sale_price']=currency($product['goods_sale_price']);
$product['goods_market_price']=currency($product['goods_market_price']);
$product['goods_point']=(int)$product['ori_price'];
$product['addoption'] = '';
$product['addoption'] .= ($product['goods_status'] & 4) ? '<span class="free_deliver"></span>':'';
$product['filter_attr']=SplitFilterAttr($product['filter_attr']);
$arr_attr=SplitAttr($product['attr_val']);

$brand=$db->get_one("SELECT brandname FROM `{$tablepre}brand_table` WHERE id='$product[goods_brand]' LIMIT 1");
$product['goods_brand']=$brand?$brand['brandname']:'--';

//product gallery
$arr_gallery=array();
$arr_gallery[]=array(ProcImgPath($product['goods_file1']),ProcImgPath($product['goods_file2']));
foreach ($arr_gallery as $key=>$val)
{
    //if(!file_exists($val[0])) $arr_gallery[$key][0]='images/noimages/noproduct.jpg';
    //if(!file_exists($val[1])) $arr_gallery[$key][1]='images/noimages/noproduct.jpg';
}
$q=$db->query("SELECT thumb,imgbig FROM `$gallery_table` WHERE goods_id='$product[uid]' LIMIT 5");
while ($rtl=$db->fetch_array($q))
{
	$rtl['thumb']=ProcImgPath($rtl['thumb']);
	$rtl['imgbig']=ProcImgPath($rtl['imgbig']);
    $arr_gallery[]=array($rtl['thumb'],$rtl['imgbig']);
}
$db->free_result();

//qrcode
$qrcode_img=create_qrcode(md5($product['goods_name']),GetBaseUrl('product',$product['uid'],'action','1',$page_member_id));

//product combine
$arr_combine=array();
if($product['type']==1)
{
    $q=$db->query("SELECT com_uid,price FROM `{$tablepre}goods_combine` WHERE g_uid='$product[uid]'");
    while ($rtl=$db->fetch_array($q))
    {
        $goods=$db->get_one("SELECT uid,goods_name,goods_file1,supplier_id,goods_sale_price,goods_stock 
                             FROM `{$tablepre}goods_table` 
                             WHERE uid='$rtl[com_uid]' AND supplier_id='$page_member_id' 
                             LIMIT 1");
        if(!$goods) continue;
        $goods_detail=$db->get_one("SELECT attr_store,attr_val FROM `{$tablepre}goods_detail` WHERE g_uid='$rtl[com_uid]' LIMIT 1");
        if(!$goods_detail) continue;
        $goods=array_merge($goods,$goods_detail);
         $goods['goods_file1']=ProcImgPath($goods['goods_file1']);
        //if(!file_exists($goods['goods_file1'])) $goods['goods_file1']='images/noimages/noproduct.jpg';
        $goods['price']=$rtl['price'];
        $goods['price_txt']=currency($rtl['price']);
        $goods['url']=GetBaseUrl('product',$goods['uid']);
        
        if($rtl['com_uid']==$product['uid']) array_unshift($arr_combine,$goods);
        else array_push($arr_combine,$goods);
    }
    $db->free_result();
}

//product wholesale
if($product['type']==2)
{
    $product['wholesale_price']=unserialize($product['wholesale_price']);
}

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
        $rtl['goods_file1']=ProcImgPath($rtl['goods_file1']);
        $rtl['url']=GetBaseUrl('product',$rtl['uid'],'action','1',$rtl['supplier_id']);
        $arr_history[]=$rtl;
    }
    $db->free_result();
    
}
else
{
    $q=$db->query("SELECT uid,goods_name,goods_file1 FROM `{$goods_table}` WHERE supplier_id='$page_member_id' AND type IN(0,1,3) ORDER BY register_date DESC LIMIT 6");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['goods_file1']=ProcImgPath($rtl['goods_file1']);
        $rtl['url']=GetBaseUrl('product',$rtl['uid']);
        $arr_history[]=$rtl;
    }
    $db->free_result();
}

//favorite
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}favorite` WHERE t='1' AND f_uid='$product[uid]' AND module='$script'");
$favorite_num=$rtl['cnt'];

//relation
$arr_relation=array();
if(in_array($product['type'],array(0,1,2,3)))
{
    $q=$db->query("SELECT rel_g_uid,rel_goods_table,rel_module FROM `{$tablepre}order_relation_statistics`
                   FORCE INDEX (`module`) 
                   WHERE module='$script' AND g_uid='$product[uid]' 
                   ORDER BY reg_date DESC 
                   LIMIT 7");
    while ($rtl=$db->fetch_array($q))
    {
        $g=$db->get_one("SELECT goods_file1,goods_name,goods_sale_price,supplier_id FROM `$rtl[rel_goods_table]` WHERE uid='$rtl[rel_g_uid]' LIMIT 1");
        if(!$g) continue;
        //if(!$g['goods_file1'] || !file_exists($g['goods_file1'])) $g['goods_file1']='images/noimages/noproduct.jpg';
        $arr_relation[]=array(
            'uid'=>$rtl['rel_g_uid'],
            'goods_file1'=>ProcImgPath($g['goods_file1']),
            'goods_sale_price'=>currency($g['goods_sale_price']),
            'goods_name'=>$g['goods_name'],
            'url'=>GetBaseUrl($rtl['rel_module'],$rtl['rel_g_uid'],'action','1',$g['supplier_id'])
        );
        unset($g);
    }
    $db->free_result();
}
if(!$arr_relation)
{
    $q=$db->query("SELECT uid,goods_file1,goods_name,goods_sale_price FROM `$goods_table` WHERE supplier_id='$page_member_id' ORDER BY register_date DESC LIMIT 7");
    while ($rtl=$db->fetch_array($q))
    {
    	$rtl['goods_file1']=ProcImgPath($rtl['goods_file1']);
        //if(!$rtl['goods_file1'] || !file_exists($rtl['goods_file1'])) $rtl['goods_file1']='images/noimages/noproduct.jpg';
        $rtl['url']=GetBaseUrl($script,$rtl['uid']);
        $arr_relation[]=$rtl;
    }
    $db->free_result();
}

//statistics
$statistics=$db->get_one("SELECT good,normal,bad,total_sale FROM `{$tablepre}goods_statistics` WHERE g_uid='$product[uid]' AND goods_table='$goods_table' LIMIT 1");
$statistics['good']=(int)$statistics['good'];
$statistics['normal']=(int)$statistics['normal'];
$statistics['bad']=(int)$statistics['bad'];
$statistics['total_sale']=(int)$statistics['total_sale'];
$statistics['comment_total']=$statistics['good']+$statistics['normal']+$statistics['bad'];

$tpl='product';
if($product['type']==1) $tpl='product_combine';
else if($product['type']==2) $tpl='product_bat';

$mm_mall_title=$product['goods_name'];
include_once template($tpl);
footer();