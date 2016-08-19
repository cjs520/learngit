<?php

/**
 * MVM_MALL 网上商店系统 缺货商品管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-03 $
 * $Id: consult.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}ask_order");
    $ask_list = array();
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,name,tel,mobile,address,reg_time,supplier_id FROM `{$tablepre}ask_order` 
                     ORDER BY uid DESC
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        
        $rtl['shop_name']=$shop['shop_name'];
        $rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);
        $rtl['reg_time'] = date('Y-m-d H:i:s',$rtl['reg_time']);
        $ask_list[] = $rtl;
    }
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
    $db->free_result();
    require_once template('consult');
    footer();
}
else if($action=='edit')
{
	$uid=(int)$uid;
	$rtl=$db->get_one("SELECT * FROM `{$tablepre}ask_order` WHERE uid='$uid' LIMIT 1");
	extract($rtl,EXTR_OVERWRITE);
	
	$shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
	$shop_name=$shop['shop_name'];
	
	$reg_time=date('Y-m-d h:i:s',$reg_time);
	require_once template('consult_add');
	exit;
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT name FROM `{$tablepre}ask_order` WHERE uid='$uid' LIMIT 1");
    if($rtl)
    {
        admin_log("删除展示商品询单：$rtl[name]");
        $db->query("DELETE FROM `{$tablepre}ask_order` WHERE uid='$uid'");
    }
    
    exit;
}
else show_msg('pass_worng');