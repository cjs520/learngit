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
 * $Id: sup_apply.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
	require_once 'include/pager.class.php';
	$total_count = $db->counter("{$tablepre}shop_apply");
	
	$apply_list=array();
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$q = $db->query("SELECT m_uid,shop_name,name,sellshow,shop_cat,tel 
	                 FROM `{$tablepre}shop_apply` 
	                 ORDER BY reg_time DESC 
	                 LIMIT $from_record, $list_num");
	$apply_list=array();
	while($rtl = $db->fetch_array($q))
	{
	    $cat=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$rtl[shop_cat]' LIMIT 1");
	    $rtl['shop_cat']=$cat['category_name'];
	    $rtl['sellshow']=$rtl['sellshow']==1?'销售型':'展示型';
	    
	    $rtl['shop_url']=GetBaseUrl('index','','',$rtl['m_uid']);
		$apply_list[] = $rtl;
	}
	$page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
	require_once template('sup_apply');
	footer();
} 
else if ($action=='edit')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT * FROM `{$tablepre}shop_apply` WHERE m_uid='$uid'");
    if(!$rtl) exit('找不到相关记录');
    
    $rtl['reg_time'] = date('Y-m-d',$rtl['reg_time']);
	$rtl['sellshow']=$rtl['sellshow']==1?'销售型':'展示型';
	if($rtl['up_logo'] && file_exists($rtl['up_logo'])) $logo='<a href="'.$rtl['up_logo'].'" target="_blank"><img src="'.$rtl['up_logo'].'" style="height:35px;" /></a>';
	if($rtl['banner'] && file_exists($rtl['banner'])) $banner='<a href="'.$rtl['banner'].'" target="_blank"><img src="'.$rtl['banner'].'" style="height:35px;" /></a>';
	require_once template('sup_apply_add');
	exit;
    
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl = $db->get_one("SELECT shop_name,up_logo,banner FROM `{$tablepre}shop_apply` WHERE m_uid = '$uid'");
    if($rtl)
    {
        admin_log("删除托管申请：$rtl[shop_name]");
        file_unlink($rtl['up_logo']);
        file_unlink($rtl['banner']);
   	    $db->query("DELETE FROM `{$tablepre}shop_apply` WHERE m_uid='$uid'");
   	    $db->free_result();
    }
    exit;
}
else show_msg('pass_worng');