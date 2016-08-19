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
 * $Id: sort.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'include/pager.class.php';
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';

require 'header.php';

$shop_url=GetBaseUrl('sort');
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

$shop_cat=(int)$shop_cat;
if($shop_cat>0)
{
    $children_uids=get_chidldren_uids($shop_cat,$uid_2_pid,$cat);    
    array_push($children_uids,$shop_cat);
    $str_children_uids=implode(',',$children_uids);
    $search_sql.=" AND supplier_cat IN($str_children_uids)";
}
if($province_s) $search_sql.=" AND province='$province_s'";
if($city_s) $search_sql.=" AND city='$city_s'";
if($county_s) $search_sql.=" AND county='$county_s'";

$lng=floatval($_COOKIE['lng']);
$lat=floatval($_COOKIE['lat']);
$near_checked='';
if($is_mobile && $lng>0 && $lat>0)
{
    $lng*=100000;
    $lat*=100000;
    $near_checked='checked';
    $search_sql.=" AND ((lng BETWEEN ".strval($lng-10000)." AND ".strval($lng+10000).") AND (lat BETWEEN ".strval($lat-1000)." AND ".strval($lat+1000)."))";
}

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
$q=$db->query("SELECT m_uid,m_id,up_logo,run_product,shop_address,shop_name,shop_intro,province,city,county,certified_type,sellshow,lng,lat 
               FROM `{$tablepre}member_shop` $use_index 
               WHERE $search_sql 
               $order_by 
               LIMIT $from_record,$list_num");
while($rtl=$db->fetch_array($q))
{
	$goods_table=$rtl['sellshow']==1?"{$tablepre}goods_table":"{$tablepre}goods_show";
    $rtl['shop_url']=GetBaseUrl('index','','',$rtl['m_uid']);    //商家url
    $rtl['sms_url']="account.php?action=mysms&to=$rtl[member_id]";
    $rtl['up_logo']=ProcImgPath($rtl['up_logo'],'logo');
    $rtl['promote_url']=GetBaseUrl('shopshow',$rtl['m_uid'],'sid');
    if(strlen(trim($shop_name))>0) $rtl['shop_name']=str_replace($shop_name,"<b style='color:red;'>$shop_name</b>",$rtl['shop_name']);
    
    $rtll=$db->get_one("SELECT COUNT(*) AS cnt FROM `$goods_table` WHERE supplier_id='$rtl[m_uid]'");
    $rtl['goods_cnt']=(int)$rtll['cnt'];
    
    if($near_checked=='checked')
    {
        $rtl['distance']=(int)CalDistance($lng,$lat,$rtl['lng'],$rtl['lat']);
        if($rtl['distance']>1000) $rtl['distance']=strval(round($rtl['distance']/1000,2)).'公里';
        else $rtl['distance'].='米';
        
    }
    
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
$page_list = $rowset->link("sort.php?shop_cat=$shop_cat&province_s=".urlencode($province_s)."&city_s=".urlencode($city_s)."&county_s=".urlencode($county_s)."&sellshow=$sellshow&shop_name=".urlencode($shop_name)."&o=$o&page=");

$mm_mall_title='商铺';

require_once template('sort');
footer();


function CalDistance($lng1,$lat1,$lng2,$lat2){
	//将角度转为狐度
	$radLat1=deg2rad($lat1/100000);//deg2rad()函数将角度转换为弧度
	$radLat2=deg2rad($lat2/100000);
	$radLng1=deg2rad($lng1/100000);
	$radLng2=deg2rad($lng2/100000);
	$a=$radLat1-$radLat2;
	$b=$radLng1-$radLng2;
	$s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
	return $s;
}