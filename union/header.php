<?php

/**
 * MVM_MALL 网上商店系统 头文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: header.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if (!defined('MVMMALL')) exit;
$mm_keywords=$shop_file['run_product'];
require_once 'qrcode/func.php';

$shop_qr_code=create_qrcode('shop_'.$page_member_id,GetBaseUrl('index'));
$cat_parent = $cache->get_cache('right_tree',$page_member_id);
$shop_category=$cache->get_cache('shop_category',$page_member_id);

$goods_table=$shop_file['sellshow']==1?"{$tablepre}goods_table":"{$tablepre}goods_show";
$detail_table=$shop_file['sellshow']==1?"{$tablepre}goods_detail":"{$tablepre}goods_show_detail";
$gallery_table=$shop_file['sellshow']==1?"{$tablepre}gallery":"{$tablepre}show_gallery";

if(!$shop_file['video_code']) $shop_file['video_code']='<embed src="http://player.youku.com/player.php/sid/XMjYwNjU3MzUy/v.swf" quality="high" width="480" height="400" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed>';
$shop_file['approval_date']=date('Y-m-d',$shop_file['approval_date']);
if($shop_file['sellshow']==1)
{
    $shop_file['xb_money']=currency($shop_file['xb_money']);
    $member_statistics=$db->get_one("SELECT comment_data FROM `{$tablepre}member_statistics` WHERE m_uid='$page_member_id' LIMIT 1");
    $member_statistics['comment_data']=unserialize($member_statistics['comment_data']);
    $shop_file['good_comment']=get_rate_class((int)$member_statistics['comment_data']['seller']['good']['total'],'seller_class_');
    $shop_file['good_rate']=floatval($member_statistics['comment_data']['seller']['good']['total'])/
                            (floatval($member_statistics['comment_data']['seller']['good']['total'])+
                            floatval($member_statistics['comment_data']['seller']['normal']['total'])+floatval($member_statistics['comment_data']['seller']['bad']['total']));
    $shop_file['good_rate']=strval(round($shop_file['good_rate']*100,2)).'%';
    
    $shop_statistics=$db->get_one("SELECT `match`,seller_service,seller_ship,ship_service FROM `{$tablepre}shop_statistics` WHERE m_uid='$page_member_id' LIMIT 1");
    $shop_statistics['match']=(int)$shop_statistics['match'];
    $shop_statistics['seller_service']=(int)$shop_statistics['seller_service'];
    $shop_statistics['seller_ship']=(int)$shop_statistics['seller_ship'];
    $shop_statistics['ship_service']=(int)$shop_statistics['ship_service'];
}
if($mvm_member['isSupplier']>=1) $my_shop_url=GetBaseUrl('index','','','1',$m_check_uid);

$shop_banner=$db->get_one("SELECT banner_file1 FROM `{$tablepre}banner_table` WHERE supplier_id='$page_member_id' AND banner_point='shop_banner' LIMIT 1");


if(!$shop_banner) $shop_banner['banner_file1']='images/noimages/dbk_banner.jpg';
else $shop_banner['banner_file1']=ProcImgPath($shop_banner['banner_file1'],'banner');

if(!$mm_wx_logo) $mm_wx_logo='images/noimages/nowx.jpg';
else $mm_wx_logo=ProcImgPath($mm_wx_logo);

/**系统url**/
$mm_url=array();
if($mm_domain_name) $mm_url['login'] = "$_URL[0]/logging.php?action=login&subrel=$mm_domain_name";
else $mm_url['login'] = GetBaseUrl('logging','login','action','1',$page_member_id,true);//登录
$mm_url['logout'] = GetBaseUrl('logging','logout','action','1',$page_member_id,true);//退出
$mm_url['user_reg'] = GetBaseUrl('register','','','1',$page_member_id,true);//注册
$mm_url['certification'] = GetBaseUrl('page','certification','action','1',0,true);//商铺认证
$mm_url['cart'] = GetBaseUrl('cart','list','action','1',0,true);//购物车
$mm_url['changegd'] = GetBaseUrl('changegd','list');
$mm_url['coupon'] = GetBaseUrl('coupon','list');
$mm_url['investment'] = GetBaseUrl('investment','','','',0,true);//我要开店
    
/**导航**/
foreach($cache->get_cache('nav',$page_member_id) as $val)
{
	switch ($val['pos'])
	{
		case 'head':
		    $nav_head[] = $val;
		    break;
		case 'foot':
		    $nav_foot[] = $val;
		    break;
		case 'help':
		    $nav_help[] = $val;
		    break;
		default:
		    $nav_middle[]=$val;
		    break;
	}
}
$nav_head[]=array (
    'nid' => '0',
    'title' => '商家展示',
    'link' => GetBaseUrl('shopshow',$page_member_id,'sid','1',$page_member_id,true),
    'target' => 'target="_blank"',
    'supplier_id' => $page_member_id,
);//添加黄页导航选项卡

$cache->var_stack['isMain']=true;
$nav2_help=$cache->get_cache('nav2');

///////////////////
if(!$shop_file['up_logo']) $shop_file['up_logo']='images/noimages/nologo.jpg';
else $shop_file['up_logo']=ProcImgPath($shop_file['up_logo'],'logo');

$script_param=implode('|',$_GET);

require_once 'include/cart.class.php';
$cart = new cart();
$cart_info=$cart->get_simple_info();