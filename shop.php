<?php

/**
 * MVM_MALL 网上商店系统  一般页面
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-05-12 $
 * $Id: shop.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'include/pager.class.php';
require 'header.php';

$shop_url=GetBaseUrl('shop');
//热门地区
$hot_area_file='data/malldata/hot_area.php';
if(file_exists($hot_area_file)) include $hot_area_file;
else $arr_hot_area=array();

$navigation='<a href="./">首页</a> &gt; <a href="'.$shop_url.'">商铺</a> ';

$shop_list=array();
$search_sql=" isSupplier=3 AND approval_date>10 ";
$sellshow=(int)$sellshow;
if($sellshow) $search_sql.=" AND sellshow='$sellshow'";
$shop_name = trim(dhtmlchars($shop_name));
if($shop_name=='请输入商铺名称') $shop_name='';
if($shop_name) $search_sql.=" AND shop_name LIKE '%$shop_name%'";

$province_s = dhtmlchars($province_s);
$city_s = dhtmlchars($city_s);
$county_s = dhtmlchars($county_s);
if($province_s) $search_sql .= " AND province='$province_s'";
if($city_s) $search_sql .= " AND city='$city_s'";
if($county_s) $search_sql .= " AND county='$county_s'";

$xj_class='xj';
$spdj_class='none';
$jmsj_class='none';
$o=dhtmlchars($o);
if(!in_array($o,array('act','reg'))) $o='reg';
switch ($o)
{
	case 'act':
	    $order_by=' ORDER BY shop_level DESC,register_date DESC';
	    $use_index="FORCE INDEX(`shop_level`)";
	    $spdj_class='down1';
	    break;
	case 'reg':
	    $order_by=' ORDER BY register_date DESC';
	    $jmsj_class='down1';
	    break;
	default:
	    $order_by=' ORDER BY register_date DESC';
	    break;
}

$total_count = $db->counter("{$tablepre}member_shop",$search_sql);
$page = $page ? (int)$page:1;
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q=$db->query("SELECT m_uid,m_id,up_logo,run_product,shop_address,shop_name,shop_intro,province,city,county,certified_type,sellshow 
               FROM `{$tablepre}member_shop` $use_index 
               WHERE $search_sql 
               $order_by 
               LIMIT $from_record,$list_num");
while($rtl=$db->fetch_array($q))
{
	$goods_table=$rtl['sellshow']==1?"{$tablepre}goods_table":"{$tablepre}goods_show";
    $rtl['shop_url']=GetBaseUrl('index','','',$rtl['m_uid']);    //商家url
    $rtl['sms_url']="account.php?action=mysms&to=$rtl[member_id]";
    if(!$rtl['up_logo'] || !file_exists(ProcImgPath($rtl['up_logo']))) $rtl['up_logo']='images/noimages/nologo.jpg';
    else $rtl['up_logo']=ProcImgPath($rtl['up_logo']);
    $rtl['promote_url']=GetBaseUrl('shopshow',$rtl['m_uid'],'sid');
    if(strlen(trim($shop_name))>0) $rtl['shop_name']=str_replace($shop_name,"<b style='color:red;'>$shop_name</b>",$rtl['shop_name']);
    
    $rtll=$db->get_one("SELECT COUNT(*) AS cnt FROM `$goods_table` WHERE supplier_id='$rtl[m_uid]'");
    $rtl['goods_cnt']=(int)$rtll['cnt'];
    
    $goods=array();
    $goods_q=$db->query("SELECT uid,goods_name,goods_file1,goods_sale_price FROM `$goods_table` 
                         WHERE supplier_id='$rtl[m_uid]' 
                         ORDER BY register_date DESC 
                         LIMIT 5");
    while($goods_rtl=$db->fetch_array($goods_q))
    {
    	$goods_rtl['goods_file1']=ProcImgPath($goods_rtl['goods_file1']);
    	$goods_rtl['goods_sale_price']=$rtl['sellshow']==2?'展示商品':currency($goods_rtl['goods_sale_price']);
    	$goods_rtl['url']=GetBaseUrl('product',$goods_rtl['uid'],'action',$rtl['m_uid']);
    	
    	$goods[]=$goods_rtl;
    }
    $rtl['goods']=$goods;
    $db->free_result(1);
    
    $qq=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$rtl[m_uid]' AND cf_name='mm_client_qq1' LIMIT 1");
    $rtl['shop_qq1']=$qq['cf_value'];
    $ww=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$rtl[m_uid]' AND cf_name='mm_client_ww' LIMIT 1");
    if($ww['cf_value']) $rtl['shop_ww']="<a target='_blank' href='http://amos.im.alisoft.com/msg.aw?v=2&uid=$ww[cf_value]&site=cntaobao&s=2&charset=utf-8' ><img border='0' src='http://amos.im.alisoft.com/online.aw?v=2&uid=$ww[cf_value]&site=cntaobao&s=2&charset=utf-8' alt='点击这里给我发消息' align='absmiddle' /></a>";
    
	$shop_list[]=$rtl;
}
$db->free_result();
$page_list = $rowset->link("shop.php?shop_cat=$shop_cat&province_s=".urlencode($province_s)."&city_s=".urlencode($city_s)."&county_s=".urlencode($county_s)."&sellshow=$sellshow&shop_name=".urlencode($shop_name)."&o=$o&page=");

$mm_mall_title='商铺';

require_once template('shop');
footer();
