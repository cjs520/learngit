<?php

/**
 * MVM_MALL 网上商店系统 商品评论管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: comment.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $arr_comment=array();
    require_once MVMMALL_ROOT.'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}gcomment_table");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,comment_body,m_id,register_date,approval_date,reply,module,guid,supplier_id 
                     FROM `{$tablepre}gcomment_table` 
                     ORDER BY `uid` DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['register_date'] = date('Y-m-d H:i',$rtl['register_date']);
        $rtl['app'] = $rtl['approval_date']>0 ? '审核':'未审核';
        !$rtl['reply'] && $rtl['reply']='暂未回复';
        $rtl['glink'] = GetBaseUrl($rtl['module'],$rtl['guid'],'action',$rtl['supplier_id']);
        $arr_comment[] = $rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
    require_once template('comment');
    footer();
}
else if ($action == 'edit')    //通过审核
{
    $uid=(int)$uid;
	if($uid<=0) exit('error');
    $rtl = $db->get_one("SELECT approval_date,uid,comment_body FROM `{$tablepre}gcomment_table` WHERE uid='$uid' LIMIT 1");
    $rtl['approval_date'] >10 ? $app = 0 : $app= $m_now_time;
    $db->query("UPDATE `{$tablepre}gcomment_table` SET approval_date = '$app' WHERE uid='$uid'");
    $db->free_result();
    admin_log($rtl['approval_date']>10?"审核商品评论：$rtl[comment_body]":"拒绝商品评论：$rtl[comment_body]");
    exit($rtl['approval_date']>10 ? '未审核' : '审核');
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl = $db->get_one("SELECT uid,comment_body FROM `{$tablepre}gcomment_table` WHERE uid='$uid' LIMIT 1");
    if($rtl)
    {
        admin_log("删除商品评论：$rtl[comment_body]");
        $db->query("DELETE FROM `{$tablepre}gcomment_table` WHERE uid='$uid'");
    }
    
    exit;
}
else show_msg('pass_worng');