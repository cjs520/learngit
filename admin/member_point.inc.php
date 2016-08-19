<?php

/**
 * MVM_MALL 网上商店系统  会员积分管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: member_point.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $search_sql="WHERE register_date>0";
    $use_index=" FORCE INDEX (`register_date`)";
    if($ps_member)
    {
        $ps_member = dhtmlchars($ps_member);
        $search_sql .= " AND point_id = '$ps_member' ";
        $use_index=" FORCE INDEX (`point_id_2`)";
    }
    $t=(int)$t;
    if($t==1) $search_sql.=" AND point_add>='0'";
    else if($t==2) $search_sql.=" AND point_add<'0'";
    else if($t==3) $search_sql.=" AND approval_date='-1'";
    
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}point_table",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 15;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,register_date,point_add,register_date,approval_date,type,point_sess,point_id,point_left 
                     FROM {$tablepre}point_table $use_index 
                     $search_sql 
                     ORDER BY register_date DESC 
                     LIMIT $from_record,$list_num");
    $member_point = array();
    while($rtl = $db->fetch_array($q))
    {
    	$rtl['reg_date'] = date('Y-m-d',$rtl['register_date']);
    	if(!$rtl['point_sess']) $rtl['point_sess']=$rtl['register_date'];
    	$rtl['point_add']<0?$rtl['minus']=$rtl['point_add']:$rtl['add']=$rtl['point_add'];
    	
    	if($rtl['approval_date']>0) $rtl['status']='<span class="orange">成功</span>';
    	else if($rtl['approval_date']==0) $rtl['status']='待审核';
    	else if($rtl['approval_date']==-1) $rtl['status']='已撤销';
    	else $rtl['status']='未知';
    	
    	$member_point[]= $rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=member_point&action=list&ps_member=".urlencode($ps_member)."&t=$t&page=", $exc);
    require_once template('member_point');
    footer();
}
else if($action=='del')
{
	$uid=(int)$uid;
	$rtl=$db->get_one("SELECT uid,point_sess FROM `{$tablepre}point_table` WHERE uid='$uid' LIMIT 1");
	if($rtl)
	{
	    admin_log("删除积分明细：$rtl[point_sess]");
	    $db->query("DELETE FROM `{$tablepre}point_table` WHERE uid='$uid'");
	    $db->free_result();
	}
	
	exit;
}
else if($action=='cancel')
{
	$uid=(int)$uid;
	$rtl=$db->get_one("SELECT point_id,point_sess,approval_date,uid,point_add FROM `{$tablepre}point_table` WHERE uid='$uid' LIMIT 1");
	if(!$rtl) exit('检索不到指定记录');
	if($rtl['approval_date']<=0) exit('非审核记录，无法撤销');
	admin_log("撤销积分明细：$rtl[point_sess]");
	
	$db->query("UPDATE `{$tablepre}member_table` SET member_point=member_point-'$rtl[point_add]' WHERE member_id='$rtl[point_id]'");
	$db->query("UPDATE `{$tablepre}point_table` SET approval_date='-1' WHERE uid='$uid'");
	exit('ok');
}
else if($action=='check')
{
	$uid=(int)$uid;
	$rtl=$db->get_one("SELECT point_id,point_sess,approval_date,uid,point_add FROM `{$tablepre}point_table` WHERE uid='$uid' LIMIT 1");
	if(!$rtl) exit('检索不到指定记录');
	if($rtl['approval_date']!=0) exit('已审核 或 已撤销记录，无法审核');
	admin_log("审核积分明细：$rtl[point_sess]");
	
	$db->query("UPDATE `{$tablepre}member_table` SET member_point=member_point+'$rtl[point_add]' WHERE member_id='$rtl[point_id]'");
	$db->query("UPDATE `{$tablepre}point_table` SET approval_date='$m_now_time' WHERE uid='$uid'");
	exit('ok');
}