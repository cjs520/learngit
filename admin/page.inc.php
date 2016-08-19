<?php

/**
 * MVM_MALL 网上商店系统  页面管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-06 $
 * $Id: page.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
	require_once 'include/pager.class.php';
	
	$arr_page=array();
    $search_sql  = "WHERE supplier_id=0 ";
	$total_count = $db->counter("{$tablepre}page_table",$search_sql);
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$sql = "SELECT uid,page_name,page_subject,register_date 
	        FROM `{$tablepre}page_table` 
	        $search_sql 
	        ORDER BY page_name 
	        LIMIT $from_record, $list_num";
	$q = $db->query($sql);
	while ($rtl = $db->fetch_array($q))
	{
	    $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
		$rtl['edit'] = "admincp.php?module=$module&action=edit&uid=$rtl[uid]&page=$page";
		$arr_page[] = $rtl;
	}
	$db->free_result();
	$page_list = $rowset->link("admincp.php?module=$module&action=list&page=");
	require_once template('page');
	footer();
}
elseif ($action=='add')
{
	if($_POST && (int)$step==1)
	{
		$rtl=$db->get_one("SELECT uid FROM `{$tablepre}page_table` WHERE page_name='$page_name' AND supplier_id='0' LIMIT 1");
		if($rtl) show_msg('英文名称重复，请重请更换');
		
		$rows = array(
		    'page_name' => $page_name,
		    'page_subject' => $page_subject,
		    'page_body' => $page_body,
		    'page_key' => $page_key,
		    'page_desc' => $page_desc,
		    'register_date' => $m_now_time
		);
		$db->insert("{$tablepre}page_table",$rows);
		$db->free_result();
		admin_log("添加页面：$page_subject");
		show_msg('success',"admincp.php?module=$module&action=list");
	}
	
	require_once template('page_add');
	footer();
}
elseif ($action=='edit')
{
    $uid=(int)$uid;
	if($_POST && (int)$step==1)
	{
		$rtl=$db->get_one("SELECT uid FROM `{$tablepre}page_table` WHERE page_name='$page_name' AND supplier_id='0' AND uid<>'$uid' LIMIT 1");
		if($rtl) show_msg('英文名称重复，请重请更换');
		
		$rows = array(
		    'page_name' => $page_name,
		    'page_subject' => $page_subject,
		    'page_body' => $page_body,
		    'page_key' => $page_key,
		    'page_desc' => $page_desc,
		    'register_date' => $m_now_time
		);
		$db->update("{$tablepre}page_table",$rows,"uid='$uid'");
		$db->free_result();
		admin_log("编辑页面：".$page_subject);
		show_msg('success',"admincp.php?module=$module&action=list&page=$page");
	}
	$page_rt = $db->get_one("SELECT * FROM `{$tablepre}page_table` WHERE uid = '$uid' LIMIT 1");
	@extract($page_rt,EXTR_OVERWRITE);
	$grade_select = drop_menu($m_class_array,'page_grant',$page_grant);
	$page_body=dhtmlchars($page_body);
	
	require_once template('page_add');
	footer();
} 
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,page_subject FROM `{$tablepre}page_table` WHERE uid='$uid' AND supplier_id='0' LIMIT 1");
    if($rtl)
    {
        admin_log("删除页面：$rtl[page_subject]");
        $db->query("DELETE FROM `{$tablepre}page_table` WHERE uid='$uid'");
        $db->free_result();
    }
	
	exit('OK:删除成功');
}
else if($action=='ajax')
{
	$uid=(int)$uid;
	$uid==0 && exit;
	$db->query("UPDATE `{$tablepre}page_table` SET $field='$v' WHERE uid='$uid'");
}
else show_msg('pass_worng');
?>
