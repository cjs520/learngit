<?php

/**
 * MVM_MALL 网上商店系统  会员管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-11 $
 * $Id: shop_update.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
	require_once 'include/pager.class.php';
	$total_count = $db->counter("{$tablepre}update_table");
	
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$q = $db->query("SELECT uid,member_id,supplier_id,member_id,ordersn,shop_level,amount,reg_date,approval_date 
	                 FROM `{$tablepre}update_table` 
	                 ORDER BY uid DESC 
	                 LIMIT $from_record, $list_num");
	$apply_list=array();
	while($rtl = $db->fetch_array($q))
	{
	    $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
	    if(!$shop) continue;
	    
	    $rtl['shop_name']=$shop['shop_name'];
	    $rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);
	    $rtl['amount']=currency($rtl['amount']);
	    $rtl['shop_level']=$lang['shop_level'][$rtl['shop_level']];
	    $rtl['reg_date']=date('Y-m-d H:i',$rtl['reg_date']);
		$apply_list[] = $rtl;
	}
	$db->free_result();
	$page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
	require_once template('shop_update');
	footer();
} 
else if($action=='check')
{
	$uid=(int)$uid;
	$shop_apply=$db->get_one("SELECT supplier_id,shop_level,approval_date,ordersn FROM `{$tablepre}update_table` WHERE uid='$uid' LIMIT 1");
	if(!$shop_apply) exit('ERR:检索不到指定的升级记录');
	if($shop_apply['approval_date']>10) exit('ERR:指定的记录已审核，无法重新审核');
	
	$shop=$db->get_one("SELECT shop_level FROM `{$tablepre}member_shop` WHERE m_uid='$shop_apply[supplier_id]' LIMIT 1");
	if(!$shop) exit('ERR:检索不到指定的商铺');
	if($shop['shop_level']>=$shop_apply['shop_level']) exit('ERR:商铺等级比申请等级高，申请纪录无法审核');
	$db->query("UPDATE `{$tablepre}update_table` SET approval_date='$m_now_time' WHERE uid='$uid'");
	$db->query("UPDATE `{$tablepre}member_shop` SET shop_level='$shop_apply[shop_level]' WHERE m_uid='$shop_apply[supplier_id]'");
	$db->free_result();
	
	admin_log("手动审核商铺升级请求：$shop_apply[ordersn]");
	exit('OK:手动审核成功');
}
else show_msg('pass_worng');
