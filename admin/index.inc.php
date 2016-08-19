<?php

/**
 * MVM_MALL 网上商店系统  后台顶部文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: index.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

$rnd = srand(0,1000);

if ($frame=='top')
{
	$q = $db->query("SELECT uid,menu_id,menu_name FROM `{$tablepre}admin_menu` 
	                 WHERE menu_id =0 AND main_show='1'
	                 ORDER BY menu_order");
	while ($rs=$db->fetch_array($q))
	{
		$top_arr .="'".$rs['uid']."'".',';
		$rs_cat[]=$rs; 
	}
	$db->free_result();
	include template('top');	
}
elseif ($frame=='menu')
{
	$one_cat = $db->get_all("SELECT uid,menu_id,menu_name FROM `{$tablepre}admin_menu` WHERE menu_id = '0' AND main_show='1' ORDER BY menu_order");
    include template('menu');	
}
elseif ($frame=='main')
{
    //搜索统计
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_search`");
    $rtl_show=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_show_search`");
    $search_num=$rtl['cnt']+$rtl_show['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}article_search`");
    $article_num=$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}shop_search`");
    $shop_num=$rtl['cnt'];
    
    $rtl=$db->get_one("SELECT register_date FROM `{$tablepre}log_task` WHERE module='clear_search_hint' ORDER BY register_date DESC LIMIT 1");
    if(!$rtl) $search_time='还未整理';
    else $search_time=date('Y-m-d',$rtl['register_date']);
    
    //会员统计
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_table`");
    $member_total=(int)$rtl['cnt'];
    $expire=$m_now_time-24*3600;
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_table` WHERE register_date>='$expire'");
    $member_today=(int)$rtl['cnt'];
    
    //商铺统计
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_shop`");
    $shop_total=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_shop` WHERE shop_level='0'");
    $shop_level_0=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_shop` WHERE shop_level='1'");
    $shop_level_1=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_shop` WHERE shop_level='2'");
    $shop_level_2=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_shop` WHERE shop_level='3'");
    $shop_level_3=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_shop` WHERE shop_level='4'");
    $shop_level_4=(int)$rtl['cnt'];
    $expire=$m_now_time-24*3600;
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_shop` WHERE register_date>'$expire'");
    $shop_today=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_table` WHERE isSupplier In (1,2)");
    $shop_to_certify=(int)$rtl['cnt'];
    
    //广告统计
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}ad_table`");
    $ad_total=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}ad_table` WHERE m_uid>0");
    $ad_shown=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}ad_apply` WHERE approval_date<>'0'");
    $ad_apply_certified=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}ad_apply` WHERE approval_date='0'");
    $ad_apply_uncertified=(int)$rtl['cnt'];
    $expire=$m_now_time+3*24*3600;
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}ad_table` WHERE m_uid>0 AND expire<='$expire'");
    $ad_expire=(int)$rtl['cnt'];
    
    //促销销计
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_group`");
    $group_total=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_group` WHERE approval='0'");
    $group_uncertified=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_auction`");
    $auction_total=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_auction` WHERE approval='0'");
    $auction_uncertified=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_onsale`");
    $onsale_total=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}coupon_cat`");
    $coupon_cat_total=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}coupon`");
    $coupon_total=(int)$rtl['cnt'];
    
    //商品销计
    $rtl_sell=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_table`");
    $rtl_show=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_show`");
    $goods_sell_total=(int)$rtl_sell['cnt'];
    $goods_show_total=(int)$rtl_show['cnt'];
    $goods_total=$goods_sell_total+$goods_show_total;
    $expire=$m_now_time-24*3600;
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}downgoods_table`");
    $downgoods_total=(int)$rtl['cnt'];
    
    //订单统计
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info`");
    $order_total=(int)$rtl['cnt'];
    $expire=$m_now_time-24*3600;
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE addtime>='$expire'");
    $order_today=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE status='1'");
    $order_status_1=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE status='3'");
    $order_status_3=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE status='4'");
    $order_status_4=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE status='5'");
    $order_status_5=(int)$rtl['cnt'];
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_back`");
    $order_back_total=(int)$rtl['cnt'];
    
    
	include template('main');
    footer();
}
else include template('index');