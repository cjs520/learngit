<?php

/**
 * MVM_MALL 网上商店系统  后台短信发送
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: sendsms.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/
header("Content-type: text/html;charset=utf-8");
if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

if($action=='add1')
{
	if($_POST)
	{
		if ($send_type==1)
		{
			$receivers='';
			$goods_grant = (int)$goods_grant;
			$sql = $goods_grant!=0 ? "SELECT member_tel1,member_tel2,member_class FROM `{$tablepre}member_table` WHERE member_class='$goods_grant'":"SELECT member_tel1,member_tel2,member_class FROM `{$tablepre}member_table`";    //modify by dxd
			$q = $db->query($sql);
			while ($rtl=$db->fetch_array($q)) $rtl['member_tel2'] && $receivers.=$rtl['member_tel2'].',';
		}

		$str = sms_send($receivers,dhtmlchars($message));
		echo "<script language='javascript'>alert('$str');history.back();</script>";
		exit;
	}
	
	$arr = $cache->get_cache('grade');
	$arr[0]='All';
	$grant_menu   = drop_menu($arr,'goods_grant');
	require_once template('sendsms1');
	exit;
}
else if($action=='add')
{
	if($_POST)
	{
		if ($send_type==1)
		{
			$receivers='';
			$goods_grant = (int)$goods_grant;
			$sql = $goods_grant!=0 ? "SELECT member_tel1,member_tel2,member_class FROM `{$tablepre}member_table` WHERE member_class='$goods_grant'":"SELECT member_tel1,member_tel2,member_class FROM `{$tablepre}member_table`";    //modify by dxd
			$q = $db->query($sql);
			while ($rtl=$db->fetch_array($q)) $rtl['member_tel2'] && $receivers.=$rtl['member_tel2'].',';
		}

		$str = sms_send($receivers,dhtmlchars($message));
		echo "<script language='javascript'>alert('$str');history.back();</script>";
		exit;
	}
	
	$arr = $cache->get_cache('grade');
	$arr[0]='All';
	$grant_menu   = drop_menu($arr,'goods_grant');
	require_once template('sendsms');
	exit;
}
else show_msg('pass_worng');
