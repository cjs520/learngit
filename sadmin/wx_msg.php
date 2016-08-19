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
 * $Id: wx_msg.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if($action=='list')
{
	$arr_msg=array();
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}wx_msg"," `supplier_id`='$page_member_id'");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,m_uid,content,reply,reply_m_id,register_date,reply_date FROM `{$tablepre}wx_msg` 
                     WHERE `supplier_id`='$page_member_id' 
                     ORDER BY `register_date` DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['m_id']='游客';
        if($rtl['m_uid']>0)
        {
            $m=$db->get_one("SELECT member_id FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
            if($m) $rtl['m_id']=$m['member_id'];
        }
        $rtl['reply_expire']=($m_now_time-$rtl['register_date']>24*3600);
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
        $rtl['reply_date']=$rtl['reply_date']<=10?'未回复':date('Y-m-d H:i',$rtl['reply_date']);
        $arr_msg[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&page=");
    
	include template('sadmin_wx_msg');
}
else if($action=='edit')
{
	require_once 'include/wx_public.class.php';
    
    $uid=(int)$uid;
    $reply=trim(dhtmlchars($reply));
    if(!$reply) exit('ERR:请输入回复信息');
    $msg=$db->get_one("SELECT uid,from_open_id,register_date FROM `{$tablepre}wx_msg` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
    if(!$msg) exit('ERR:检索不到指定的咨询信息');
    if($m_now_time-$msg['register_date']>100*60) exit('ERR:咨询留言已过期，无法回复');
    
    $row=array(
        'reply'=>$reply,
        'reply_m_id'=>$m_check_id,
        'reply_date'=>$m_now_time
    );
    $db->update("`{$tablepre}wx_msg`",$row," uid='$msg[uid]'");
    
    $wx_public=new wx_public($ucfg['mm_wx_app_id'],$ucfg['mm_wx_app_secret']);
    $o_rtl=$wx_public->reply_msg($msg['from_open_id'],$reply);
    
    if((int)$o_rtl->errcode==0) $rtl_msg='OK:回复成功';
    else $rtl_msg='ERR:回复失败，原因:'.$o_rtl->errmsg;
    
    exit($rtl_msg);
    
    include template('sadmin_links_add');
    exit;
}
else if($action=='del')
{
	$uid=(int)$uid;
    $db->query("DELETE FROM `{$tablepre}wx_msg` WHERE uid='$uid' AND supplier_id='$page_member_id'");
    exit('OK:删除成功');
}