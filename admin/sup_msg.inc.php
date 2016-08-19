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
 * $Id: sup_msg.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')    
{
	require_once 'include/pager.class.php';
	
	$msg_list=array();
	$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_goods_comment`");
	$total_count=$rtl['cnt'];
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$q=$db->query("SELECT uid,from_id,to_id,level,comment,goods_table,g_uid,module 
	               FROM `{$tablepre}order_goods_comment` 
	               ORDER BY reg_date DESC 
	               LIMIT $from_record,$list_num");
	while($rtl=$db->fetch_array($q))
	{
	    $g=$db->get_one("SELECT goods_name,supplier_id FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
	    if($g)
	    {
	        $rtl['goods_name']=$g['goods_name'];
	        $rtl['goods_url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$g['supplier_id']);
	    }
	    
	    $rtl['level']=$mm_comment_level[$rtl['level']];
		$msg_list[]=$rtl;
	}
	$db->free_result();
	$page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
	require_once template('sup_msg');
	footer();
} 
else if($action=='list_seller')    //商家评价
{
	require_once 'include/pager.class.php';
	
	$msg_list=array();
	$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_user_comment` WHERE roll='1'");
	$total_count=$rtl['cnt'];
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$q=$db->query("SELECT uid,from_id,to_id,level,comment 
	               FROM `{$tablepre}order_user_comment` 
	               FORCE INDEX (`reg_date`) 
	               WHERE roll='1' 
	               ORDER BY reg_date DESC 
	               LIMIT $from_record,$list_num");
	while($rtl=$db->fetch_array($q))
	{
	    $rtl['level']=$mm_comment_level[$rtl['level']];
		$msg_list[]=$rtl;
	}
	$page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
	require_once template('sup_msg_seller');
	footer();
} 
else if ($action=='del')
{
    $uid=(int)$uid;
    if($uid<=0) exit;
    if($act!='list' && $act!='list_seller') $act='list';
   
    if($act=='list') $db->query("DELETE FROM `{$tablepre}order_goods_comment` WHERE uid='$uid'");
    else $db->query("DELETE FROM `{$tablepre}order_user_comment` WHERE uid='$uid'");
    $db->free_result();
    admin_log("删除订单评价");
    exit;
}
else if($action=='all_delete')
{
    do
    {
        if($act!='list' && $act!='list_seller') $act='list';
        if(!$uid_check || !is_array($uid_check)) break;
        
        foreach ($uid_check as $key=>$val)
        {
            $val=(int)$val;
            if($val<=0) unset($uid_check[$key]);
            else $uid_check[$key]=$val;
        }
        if(!$uid_check) break;
        $str_uid=implode(',',$uid_check);
        if($act=='list') $db->query("DELETE FROM `{$tablepre}order_goods_comment` WHERE uid IN ($str_uid)");
        else $db->query("DELETE FROM `{$tablepre}order_user_comment` WHERE uid IN ($str_uid)");
        $db->free_result();
        admin_log("批量删除订单评价");
    }while (0);
    
    show_msg('删除成功',"admincp.php?module=$module&action=$act");
}
else show_msg('pass_worng');
