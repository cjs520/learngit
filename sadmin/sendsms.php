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
 * $Id: sendsms.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

if($action=='add')
{
	if($_POST)
	{
		if(!$cmd) $cmd = 'send';    //操作命令;
		if ($send_type==1)
		{
			$receivers='';
			$goods_grant = (int)$goods_grant;
			$sql = $goods_grant!=0 ? 
			       "SELECT member_tel1,member_tel2,member_class FROM `{$tablepre}member_table` WHERE member_class='$goods_grant'":
			       "SELECT member_tel1,member_tel2,member_class FROM `{$tablepre}member_table`";
			$result = $db->query($sql);
			while ($rt=$db->fetch_array($result)) $rt['member_tel2'] && $receivers.=$rt['member_tel2'].',';
		}
		
		$message = trim($message);    //url编码后的短信内容
		$info=supplier_sms_send($receivers,$message,$page_member_id);
		sadmin_show_msg('发送完毕',base64_encode('sadmin.php?module=settings&action=list&type=sms_set'));
	}
	
	$arr = $m_class_array;
	$arr[0] = 'All';
	$grant_menu = drop_menu($arr,'goods_grant');
	include template('sadmin_sendsms');
	exit;	
}