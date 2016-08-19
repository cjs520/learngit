<?php

/**
 * MVM_MALL 网上商店系统  找回密码
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-12 $
 * $Id: lostpass.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
if($action=='lostpasswd')
{
	if($_POST && (int)$step==1)
	{
		!$login_id &&  show_msg('intput_member');
		!$loss_email && show_msg('邮件输入错误');
		$login_id = dhtmlchars($login_id);
		$loss_email = dhtmlchars($loss_email);
		$list = $db->get_one("SELECT member_id, member_email,member_name 
		                      FROM `{$tablepre}member_table` 
		                      WHERE member_id = '$login_id'");
		if(!$list || $list['member_email']!=$loss_email) show_msg('您填写的用户ID或邮箱错误，请仔细检查');
	
		$rnd =md5(strval($m_now_time).strval(mt_rand(1000,9999)));
		$row = array(
		    'user_id' => $list['member_id'],
		    'lost_type'=>'1',
		    'lost_str' => $rnd,
		    'lost_time' => $m_now_time,
		);
		$db->replace("{$tablepre}lostpass",$row);
		$db->free_result();

		$chang_url = "$mm_mall_url/lostpass.php?action=changepasswd&login_id=".urlencode($list['member_id'])."&str=$rnd";
		$mail_content  = "$list[member_name]: 密码找回<br />";
		$mail_content .= "用户ID :  $list[member_id]<br />";
		$mail_content .= "密码修改链接 >>>：<a href=\"$chang_url\" target=\"_blank\">点击这里，重置商城用户密码</a><br />";
		
		smtp_mail($list['member_email'],"{$mm_mall_title}密码找回",$mail_content);
		show_msg('更改链接已经发送到您的邮箱，请查收','./');
	}
	require 'header.php';
	require_once template('lostpasswd');
	footer();
	
}
else if ($action=='changepasswd')
{
	$login_id = dhtmlchars($login_id);
	$str = dhtmlchars($str);
	
	$loss_log = $db->get_one("SELECT * FROM `{$tablepre}lostpass` WHERE user_id='$login_id' AND lost_type='1' AND lost_str='$str' LIMIT 1");
	if(!$loss_log) show_msg('pass_worng');
	
	if($_POST && (int)$step==1)
	{
		$member=$db->get_one("SELECT member_email,member_id FROM `{$tablepre}member_table` WHERE member_id = '$login_id'  LIMIT 1");
		if(!$member) show_msg('检索不到指定用户');
		$new_pass = trim(dhtmlchars($new_pass));//新密码明文   
    	$base_pass=base64_encode($new_pass);
    	$insert_pass = md5($new_pass);
    	
		$db->query("UPDATE `{$tablepre}member_table` SET 
		            member_pass= '$insert_pass',
		            base_pass='$base_pass',
		            pay_pass='$insert_pass' 
		            WHERE member_id = '$login_id'");
		$db->query("DELETE FROM `{$tablepre}lostpass` WHERE user_id='$login_id' AND lost_type='1'");
		$db->free_result();
		
		show_msg('登录密码与支付密码均已重置，请及时更新您的支付密码',GetBaseUrl('logging','login'));
	}
	
	$mm_mall_title = $login_id.$lang['update_loss'];
	require 'header.php';
	require_once template('lostpasswd');
	footer();
}