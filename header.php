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
$cat_parent = $cache->get_cache('right_tree');

/**系统url**/
$mm_url=array();
$mm_url['index'] = GetBaseUrl('index');//首页
$mm_url['my_account'] = $mvm_member['isSupplier']>=2?'sadmin.php?module=index':'member.php?action=index';
$mm_url['investment'] = $mvm_member['isSupplier']>0?GetBaseUrl('index','','',$m_check_uid):"member.php?action=shop_apply&sellshow=1&shop_level=0";
$mm_url['login'] = GetBaseUrl('logging','login');//登录
$mm_url['logout'] = GetBaseUrl('logging','logout');//退出
$mm_url['user_reg'] = GetBaseUrl('register');//注册
$mm_url['cart'] = GetBaseUrl('cart','list');//购物车
$mm_url['goodcat'] = GetBaseUrl('goodcat','');//所有分类
$mm_url['help'] = GetBaseUrl('help');//帮助中心

/**导航**/
foreach($cache->get_cache('nav') as $val)
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
		case 'news':
		    $nav_news[] = $val;
		    break;
		default:
		    $nav_middle[] = $val;
		    break;
	}
}
/**分级导般**/
foreach($cache->get_cache('nav2') as $val)
{
	switch ($val['pos'])
	{
		case 'head':
		    $nav2_head[] = $val;
		    break;
		case 'foot':
		    $nav2_foot[] = $val;
		    break;
		case 'help':
		    $nav2_help[] = $val;
		    break;
		case 'news':
		    $nav2_news[] = $val;
		    break;
		default:
		    $nav2_middle[] = $val;
		    break;
	}
}

require_once 'include/cart.class.php';
$cart = new cart();
$cart_info=$cart->get_simple_info();