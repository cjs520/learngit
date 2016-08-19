<?php

/**
 * MVM_MALL 网上商店系统  会员等级管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-12 $
 * $Id: grade.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
	$q = $db->query("SELECT * FROM `{$tablepre}grade_table` ORDER BY is_admin ASC,degree ASC");
	while ($rt = $db->fetch_array($q))
	{
	    $rt['is_admin'] = $rt['is_admin']==0?'会员等级':'管理员等级';
		$rt['edit'] = "admincp.php?module=$module&action=edit&uid=$rt[group_id]";
		$rt['del'] = "admincp.php?module=$module&action=del&uid=$rt[group_id]";
		$grade_rt[] = $rt;
	}
	$db->free_result();
	
	require_once template('grade');
	footer();
}
else if ($action=='add')
{
    require_once 'include/rank_list.inc.php';
    
	if($_POST && (int)$step==1)
	{
		if(!is_array($rank)) $rank=array();
		$rank = implode(',',$rank);
		
		$rows = array(
		    'group_name' => $group_name,
		    'max_points' => (int)$max_points,
		    'min_points' => (int)$min_points,
		    'rank_list' => $rank,
		    'degree' => $degree,
		    'is_admin' => (int)$is_admin
		);
		$db->insert("{$tablepre}grade_table",dhtmlchars($rows));
		$db->free_result();
		admin_log("添加会员等级：$group_name");
		$cache->delete('grade',0);
		move_page(base64_decode($p_url));
	}
	$is_admin_n='checked';
	
	require_once template('grade_add');
	exit;
}
else if ($action=='edit')
{
    $uid=(int)$uid;
    $rt_rank = $db->get_one("SELECT * FROM `{$tablepre}grade_table` WHERE group_id='$uid' LIMIT 1");
    if(!$rt_rank) show_msg('检索不到指定会员等级');
    include 'include/rank_list.inc.php';
    
	if($_POST && (int)$step==1)
	{
		if(!is_array($rank)) $rank=array();
		$rank = implode(',',$rank);
		
		$rows = array(
		    'group_name' => $group_name,
		    'max_points' => (int)$max_points,
		    'min_points' => (int)$min_points,
		    'rank_list' => $rank,
		    'degree' => $degree,
		    'is_admin' => (int)$is_admin
		);
		if($rt_rank['is_admin']==1)
		{
		    unset($rows['max_points']);
		    unset($rows['min_points']);
		}
		$db->update("{$tablepre}grade_table",dhtmlchars($rows),"group_id='$uid'");
		$db->free_result();
		admin_log("编辑会员等级：$group_name");
		$cache->delete('grade',0);
		
		move_page(base64_decode($p_url));
	}
	
	$rt_rank['is_admin']==0?$is_admin_n='checked':$is_admin_y='checked';
	$rank = explode(',',$rt_rank['rank_list']);
	@extract($rt_rank,EXTR_SKIP);
	require_once template('grade_add');
	exit;
	
}
else if ($action=='del')
{
    $uid=(int)$uid;
	//无法删除管理员
	$rtl=$db->get_one("SELECT * FROM `{$tablepre}grade_table` WHERE group_id='$uid'");
	if(!$rtl) show_msg('检索不到指定的会员等级','admincp.php?module=grade&action=list');
	if($rtl['group_id']==3) show_msg('cant_del_manager','admincp.php?module=grade&action=list');

	$db->query("DELETE FROM `{$tablepre}grade_table` WHERE group_id = '$uid'");
	$db->free_result();
	$cache->delete('grade',0);
	admin_log("删除会员等级：$rtl[group_name]");
	show_msg('success','admincp.php?module=grade&action=list');
} 
else if($action=='ajax')
{
	$uid=(int)$uid;
	$uid==0 && exit;
	$db->query("UPDATE `{$tablepre}grade_table` SET $field='$v' WHERE group_id='$uid'");
}
else show_msg('pass_worng');
