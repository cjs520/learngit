<?php

/**
 * MVM_MALL 网上商店系统  会员预付款管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: xb_money.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/


if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $search_sql="WHERE TRUE";
    $ps_member && $search_sql.=" AND money_id='$ps_member'";
    $b_time && $search_sql.=" AND register_date>='".strtotime($b_time)."'";
    $e_time && $search_sql.=" AND register_date<='".strtotime($e_time)."'";
    
    $member_xb = array();
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}xb_money",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 15;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,money_reason,type,money_add,money_sess,money_left,money_id,register_date 
                     FROM `{$tablepre}xb_money` 
                     $search_sql 
                     ORDER BY register_date DESC 
                     LIMIT $from_record,$list_num");
    while($rtl = $db->fetch_array($q))
    {
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
        $rtl['money_left']=currency($rtl['money_left']);
        $rtl['money_add']>=0?$rtl['add']=currency($rtl['money_add']):$rtl['minus']=currency($rtl['money_add']);
        $member_xb[] = $rtl;
    }
    $page_list = $rowset->link("admincp.php?module=member_money&ps_member".urlencode($ps_member)."=&b_time=$b_time&e_time=$e_time&action=list&page=");
    require_once template('xb_money');
    footer();
}
else if($action=='del')
{
	$uid=(int)$uid;
	$rtl=$db->get_one("SELECT money_sess FROM `{$tablepre}xb_money` WHERE uid='$uid' LIMIT 1");
	if($rtl)
	{
	    $db->query("DELETE FROM `{$tablepre}xb_money` WHERE uid='$uid'");
	    $db->free_result();
	    admin_log("删除消保充值记录：$rtl[money_sess]");
	}
	
	exit;
}