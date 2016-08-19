<?php

/**
 * MVM_MALL 网上商店系统 首页文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: wosmj.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

if($action=='list')
{
	require_once 'include/pager.class.php';
	
    $search_sql = "WHERE `supplier_id`='$page_member_id' ";
	$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}page_table` $search_sql");
	$is_full=$rtl['cnt']>=$allow_page_items[$shop_file['shop_level']];

	$total_count = $rtl['cnt'];
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$sql = "SELECT * FROM `{$tablepre}page_table` $search_sql ORDER BY uid DESC LIMIT $from_record, $list_num";
	$q = $db->query($sql);
	while ( $rt = $db->fetch_array($q))
	{
		$rt['register_date'] = date('Y-m-d',$rt['register_date']);
		$rt['edit'] = "sadmin.php?module=$module&action=edit&uid=$rt[uid]&page=$page";
		$rt['link'] = GetBaseUrl('page',$rt['page_name'],'action',$rt['supplier_id']);
		$page_rt[] = $rt;
	}
	$page_list = $rowset->link("sadmin.php?module=$module&action=list&page=");
	include template('sadmin_page');
}
else if($action=='add')
{
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}page_table` WHERE supplier_id='$page_member_id'");
	if($rtl['cnt']>=$allow_page_items[$shop_file['shop_level']]) show_msg('您的页面已到达上限，不能再添加');
	
	if($_POST && (int)$step==1)
	{
		$rtl=$db->get_one("SELECT uid FROM `{$tablepre}page_table` WHERE page_name='$page_name' AND supplier_id='$page_member_id' LIMIT 1");
		if($rtl) show_msg('英文名称重复，请重请更换');
		
		$rows = array(
		    'page_name' => $page_name,
		    'page_subject' => $page_subject,
		    'page_body' => $page_body,
		    'page_key' => $page_key,
		    'page_desc' => $page_desc,
		    'register_date' => $m_now_time,
		    'supplier_id' => $page_member_id
		);
		$db->insert("{$tablepre}page_table",$rows);
		show_msg('添加成功',"sadmin.php?module=$module&action=list");
	} 
	
	include template('sadmin_page_add');
}
else if($action=='edit')
{
	if($_POST && (int)$step==1)
	{
		$rtl=$db->get_one("SELECT uid FROM `{$tablepre}page_table` WHERE page_name='$page_name' AND supplier_id='$page_member_id' AND uid<>'$uid' LIMIT 1");
		if($rtl) show_msg('英文名称重复，请重请更换');
		
		$rows = array(
		    'page_name' => $page_name,
		    'page_subject' => $page_subject,
		    'page_body' => $page_body,
		    'page_key' => $page_key,
		    'page_desc' => $page_desc,
		    'register_date' => $m_now_time
		);
		$db->update("{$tablepre}page_table",$rows,"uid='$uid' AND `supplier_id`='$page_member_id'");
		show_msg('编辑成功',"sadmin.php?module=$module&action=list&page=$page");
	}
	$page_rt = $db->get_one("SELECT * FROM `{$tablepre}page_table` WHERE uid = '$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
	@extract($page_rt,EXTR_OVERWRITE);
	$page_body=dhtmlchars($page_body);
	
	
	include template('sadmin_page_add');
}
else if($action=='del')
{
	$uid=(int)$uid;
	$uid>0 && $db->query("DELETE FROM `{$tablepre}page_table` WHERE uid='$uid' AND `supplier_id`='$page_member_id' ");
	exit;
}
else if($action=='all_delete')
{
	do
	{
	    if(!is_array($uid_check) || !$uid_check) break;
	    foreach ($uid_check as $key=>$val)
	    {	
	        $val=(int)$val;
	        if($val<=0) unset($uid_check[$key]);
	    }
	    if(!$uid_check) break;
	    $uid_check=array_unique($uid_check);
	    $str_uid=implode(',',$uid_check);
	    $db->query("DELETE FROM `{$tablepre}page_table` WHERE uid IN ($str_uid) AND `supplier_id`='$page_member_id'");
	}while(0);
	
	show_msg('删除成功',"sadmin.php?module=$module&action=list");
}
