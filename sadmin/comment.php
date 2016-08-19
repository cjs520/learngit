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
 * $Id: comment.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

if($action=='list')
{
	require_once MVMMALL_ROOT.'include/pager.class.php';
	$arr_comment=array();
    $total_count = $db->counter("{$tablepre}gcomment_table"," `supplier_id`='$page_member_id'");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,comment_body,m_id,register_date,approval_date,reply,reply_time,module,guid,type 
                     FROM `{$tablepre}gcomment_table` 
                     WHERE supplier_id='$page_member_id' 
                     ORDER BY register_date DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $goods_table=goods_table($rtl['type']);
        $g=$db->get_one("SELECT goods_name,goods_file1 FROM `$goods_table` WHERE uid='$rtl[guid]' LIMIT 1");
        
        $rtl['register_date'] = date('Y-m-d',$rtl['register_date']);
        $rtl['app'] = $rtl['approval_date']>0 ? '审核':'未审核';
        $rtl['goods_name']=$g['goods_name'];
        $rtl['glink'] = GetBaseUrl($rtl['module'],$rtl['guid'],'action',$page_member_id);
        $rtl['goods_file1'] = ProcImgPath($g['goods_file1']);
        $rtl['status']=$rtl['approval_date']<10?'未审核':'审核';
        
        $arr_comment[] = $rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
	include template('sadmin_comment');
}
else if($action=='edit')
{
    $uid=(int)$uid;
	if($uid<=0) exit('error');
    $rtl = $db->get_one("SELECT approval_date,uid FROM `{$tablepre}gcomment_table` WHERE uid='$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
    $rtl['approval_date'] >10 ? $app = 0 : $app= $m_now_time;
    $db->query("UPDATE `{$tablepre}gcomment_table` SET approval_date = '$app' WHERE uid='$uid' AND `supplier_id`='$page_member_id'");
    $db->free_result();
    exit($rtl['approval_date']>10 ? '未审核' : '审核');
}
else if($action=='del')
{
	$uid=(int)$uid;
    $db->query("DELETE FROM `{$tablepre}gcomment_table` WHERE uid='$uid' AND `supplier_id`='$page_member_id'");
    exit;
}
else if($action=='reply')
{
    $uid=(int)$_POST['uid'];
    $reply=dhtmlchars(trim(strip_tags($reply)));
    
    if($uid<=0) exit('ERROR');
    if(!$reply) exit('请填写留言内容');
    $db->query("UPDATE `{$tablepre}gcomment_table` SET 
                reply='$reply',
                reply_time='$m_now_time' 
                WHERE uid='$uid' AND supplier_id='$page_member_id'");
    $db->free_result();
    exit('ok');
}