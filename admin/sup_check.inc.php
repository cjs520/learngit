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
 * $Id: sup_check.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
$star_data=array(0=>'----',1=>'★',2=>'★★',3=>'★★★',4=>'★★★★',5=>'★★★★★');
if($action=='list')
{
	require_once 'include/pager.class.php';
	
	$search_sql=" WHERE TRUE ";
	$use_index=" FORCE INDEX (`register_date`) ";
	$isSupplier = (int)$isSupplier;
	if($isSupplier==1)
	{
		$search_sql.=" AND isSupplier='1'";
		$isSupplier_1_checked='checked';
	}
	else if($isSupplier==2)
	{
		$search_sql.=" AND isSupplier>'1'";
		$isSupplier_2_checked='checked';
	}
	else $isSupplier_0_checked='checked';
		
	if($ps_member)
	{
	    $search_sql.=" AND m_id='$ps_member'";
	    $use_index='';
	}
	
	$arr_shop=array();
	$total_count = $db->counter("{$tablepre}member_shop",$search_sql);
	$page  = $page ? (int)$page:1;
	$list_num = 20;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$q = $db->query("SELECT m_uid,m_id,shop_name,certified_type,isSupplier,up_id_card,up_licence,isSupplier 
	                 FROM `{$tablepre}member_shop` $use_index
	                 $search_sql 
	                 ORDER BY register_date DESC 
	                 LIMIT $from_record, $list_num");
	while($rtl = $db->fetch_array($q))
	{
	    if($rtl['up_id_card']) $rtl['id_card']='<a rel="up_id_card" href="'.ProcImgPath($rtl['up_id_card']).'" target="_blank">查看身份证</a>';
	    if($rtl['up_licence']) $rtl['licence']='<a rel="up_licence" href="'.ProcImgPath($rtl['up_licence']).'"  target="_blank">查看营业执照</a>';
	    
	    $rtl['tag']='';
	    if($rtl['isSupplier']==2) $rtl['tag']='（已认证）';
	    else if($rtl['isSupplier']==3) $rtl['tag']='（已审核）';
	    
	    $rtl['shop_url']=GetBaseUrl('index','','',$rtl['m_uid']);
		$arr_shop[]=$rtl;
	}
	$page_list = $rowset->link("admincp.php?module=$module&action=$action&ps_member=".urlencode($ps_member)."&isSupplier=$isSupplier&page=");
	$db->free_result();
	
	require_once template('sup_check');
	footer();
}
else if($action=='pass_supplier')
{
    $uid=(int)$uid;
    $shop=$db->get_one("SELECT m_uid,shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$uid' LIMIT 1");
	if(!$shop) exit('ERR:检索不到指定商铺');
    admin_log("审核商铺：".$shop['shop_name']);
	
	$db->query("UPDATE `{$tablepre}member_table` SET isSupplier='3' WHERE uid='$uid'");
    $db->query("UPDATE `{$tablepre}member_shop` SET 
               isSupplier='3',
               approval_date='$m_now_time' 
               WHERE m_uid='$uid' LIMIT 1");
    $db->free_result();
    
    //发送邮件
    $m=cur_member_info($shop['m_uid']);
    if((int)$mm_mail_shop_pass==1 && $m['member_email'])
    {
        smtp_mail($m['member_email'],
		    "您在{$mm_mall_title}的商铺已通过审核",
		    str_replace(array('{member_id}','{mall_title}','{shop_name}'),array($m['member_id'],$mm_mall_title,$shop['shop_name']),$mm_mail_shop_pass_cnt)
        );
    }
    exit('OK:通过验证');
}
else if($action=='certify_supplier')
{
	$uid=(int)$uid;
	$type=(int)$type;
	!in_array($type,array(1,2)) && $type=1;
	
	$shop=$db->get_one("SELECT m_uid,isSupplier,certified_type,shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$uid' LIMIT 1");
	if(!$shop) exit('ERR:检索不到指定商铺');
	admin_log("认证商铺：".$shop['shop_name']);
	
	$isSupplier=$shop['isSupplier'];
	if($isSupplier<3) $isSupplier=2;
	$type=($shop['certified_type'] | $type);
	
    $db->query("UPDATE `{$tablepre}member_table` SET isSupplier='$isSupplier' WHERE uid='$uid'");
	$db->query("UPDATE `{$tablepre}member_shop` SET 
	            certified_type = '$type',
	            isSupplier='$isSupplier' 
	            WHERE m_uid='$uid'");
	$db->free_result();
	
	exit('OK:通过认证');
}
else if($action=='deny_check')
{
	$uid=(int)$uid;
	
	$post_reason=dhtmlchars($deny_reason);
	if($post_reason=='0') $post_reason=dhtmlchars($reason);
	if($post_reason=='') exit('请输入拒绝理由');
	
	$member=$db->get_one("SELECT member_id FROM `{$tablepre}member_table` WHERE uid='$uid' LIMIT 1");
	if(!$member) exit('ERR:检索不到指定会员');
	admin_log("拒绝审核商铺：".$member['member_id']);
	
	inner_sms_send('admin',$member['member_id'],'商铺审核拒绝',$mm_mall_title.'：您的商铺没有通过审核。原因：'.$post_reason);
    exit('OK:短消息发送成功');
}
else if($action=='deny_certify')
{
	$uid=(int)$uid;
	
	$post_reason=dhtmlchars($deny_reason);
	if($post_reason=='0') $post_reason=dhtmlchars($reason);
	if($post_reason=='') exit('ERR:请输入拒绝理由');
	
	$member=$db->get_one("SELECT member_id FROM `{$tablepre}member_table` WHERE uid='$uid' LIMIT 1");
	if(!$member) exit('ERR:检索不到指定会员');
	admin_log("拒绝认证商铺：".$member['member_id']);
	
	inner_sms_send('admin',$member['member_id'],'商铺认证拒绝',$mm_mall_title.'：您的商铺没有通过认证。原因：'.$post_reason);
    exit('OK:短消息发送成功');
}
else show_msg('pass_worng');