<?php

/**
 * MVM_MALL 网上商店系统  会员注册
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-03 $
 * $Id: register.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
$m_check_id &&  move_page('./');
if((int)$mm_member_reg!=1) show_msg('商城当前关闭注册，请联系管理员');
require_once 'include/mvm_oauth.class.php';

if ($_POST && (int)$step==1)    //注册
{
    define('REGISTER',1);
    if((int)$agree!=1) show_msg('您未同意本站的注册协议，无法注册');
    
	//登录验证码判断
	if($mm_code_use==1)
	{
		require_once 'include/captcha.class.php';
		$Captcha = new Captcha();
		!$Captcha->CheckCode($code) && show_msg('code_wrong');
	}
	!$login_id && show_msg('请输入您要注册的会员ID');
	(mb_strlen($login_id,'UTF-8')<2  || mb_strlen($login_id,'UTF-8')>16) && show_msg('请输入ID 2-16 位英文、数字或汉字组合');
	!$pass1 && show_msg('请输入您的密码');
	$pass1 != $pass2 && show_msg('两次输入的密码不一致');
	$login_id = dhtmlchars($login_id);
	$ori_pass=$pass1;
	$pass1 = md5($pass1);
	if(!preg_match('/^(\w|[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3})+$/',$login_id)) show_msg('会员ID格式错误');
	
	$rt_user = $db->get_one("SELECT member_id FROM `{$tablepre}member_table` WHERE member_id = '$login_id' LIMIT 1");
	$rt_user['member_id'] && show_msg('您的ID已被占用，请更换一个');
	
	$member_image='';
	if((int)$use_blog_head==1 && $headimgurl) $member_image=$headimgurl;
	
	$rows = array(
	    'member_class' => 1,
	    'member_id' => $login_id,
	    'member_pass' => $pass1,
	    'base_pass' => base64_encode($ori_pass),
	    'member_image'=>$member_image,
	    'register_date' => $m_now_time
	);
	$insert_id = $db->insert("{$tablepre}member_table",dhtmlchars($rows));
	
	//注册送积分
	add_score($insert_id,(int)$mm_point_member,'注册赠送','注册赠送积分');
	add_money($insert_id,(int)$mm_mony_member,'注册赠送','注册赠送预付款');
	
	switch ($oauth)
	{
	    case 'qq':
	        $qq_oauth=new mvm_oauth(array('app_id'=>$mm_qq_app_id,'app_key'=>$mm_qq_app_key),OAUTH_QQ);
	        $oauth_data=$qq_oauth->get_oauth_data();
	        $db->query("REPLACE INTO `{$tablepre}member_oauth` (m_uid,oauth_uid,token,type)
                        VALUES ('$insert_id','$oauth_data[open_id]','$oauth_data[access_token]','".$qq_oauth->get_oauth_type()."')");
	        $db->free_result();
	        break;
	    case 'sina':
	        $sina_oauth=new mvm_oauth(array('app_key'=>$mm_sina_app_key,'app_secret'=>$mm_sina_app_secret),OAUTH_SINA);
	        $oauth_data=$sina_oauth->get_oauth_data();
	        $db->query("REPLACE INTO `{$tablepre}member_oauth` (m_uid,oauth_uid,token,type)
                        VALUES ('$insert_id','$oauth_data[open_id]','$oauth_data[access_token]','".$sina_oauth->get_oauth_type()."')");
	        $db->free_result();
	        break;
	    case 'wx':
	        $wx_oauth=new mvm_oauth(array('app_id'=>$mm_wx_app_id,'app_secret'=>$mm_wx_app_secret),OAUTH_WX);
	        $oauth_data=$wx_oauth->get_oauth_data();
	        $db->query("REPLACE INTO `{$tablepre}member_oauth` (m_uid,oauth_uid,token,type)
                        VALUES ('$insert_id','$oauth_data[open_id]','$oauth_data[access_token]','".$wx_oauth->get_oauth_type()."')");
	        $db->free_result();
	        break;
	}
	
	$login_pass = $pass1;
	require_once 'include/passport.inc.php';
	exit;
}
else if($_POST && (int)$step==2)    //微信绑定
{
    $login_id=dhtmlchars($login_id);
    if(!$login_id) show_msg('请填写需要绑定的用户名');
    $pass1=md5($pass1);
    
    if($mm_code_use==1)
	{
		require_once 'include/captcha.class.php';
		$Captcha = new Captcha();
		!$Captcha->CheckCode($code) && show_msg('code_wrong');
	}
    
    $m=$db->get_one("SELECT uid,member_pass FROM `{$tablepre}member_table` WHERE member_id='$login_id' LIMIT 1");
    if(!$m) show_msg('检索不到指定的会员');
    if($m['member_pass']!=$pass1) show_msg('会员名或密码错误');
    
    $member_image='';
	if((int)$use_blog_head==1 && $headimgurl)
	{
	    $member_image=$headimgurl;
	    $db->query("UPDATE `{$tablepre}member_table` SET member_image='$member_image' WHERE uid='$m[uid]' LIMIT 1");
	    $db->free_result();
	}
    
    switch ($oauth)
	{
	    case 'qq':
	        $qq_oauth=new mvm_oauth(array('app_id'=>$mm_qq_app_id,'app_key'=>$mm_qq_app_key),OAUTH_QQ);
	        $oauth_data=$qq_oauth->get_oauth_data();
	        $db->query("REPLACE INTO `{$tablepre}member_oauth` (m_uid,oauth_uid,token,type)
                        VALUES ('$m[uid]','$oauth_data[open_id]','$oauth_data[access_token]','".$qq_oauth->get_oauth_type()."')");
	        $db->free_result();
	        break;
	    case 'sina':
	        $sina_oauth=new mvm_oauth(array('app_key'=>$mm_sina_app_key,'app_secret'=>$mm_sina_app_secret),OAUTH_SINA);
	        $oauth_data=$sina_oauth->get_oauth_data();
	        $db->query("REPLACE INTO `{$tablepre}member_oauth` (m_uid,oauth_uid,token,type)
                        VALUES ('$m[uid]','$oauth_data[open_id]','$oauth_data[access_token]','".$sina_oauth->get_oauth_type()."')");
	        $db->free_result();
	        break;
	    case 'wx':
	        $wx_oauth=new mvm_oauth(array('app_id'=>$mm_wx_app_id,'app_secret'=>$mm_wx_app_secret),OAUTH_WX);
	        $oauth_data=$wx_oauth->get_oauth_data();
	        $db->query("REPLACE INTO `{$tablepre}member_oauth` (m_uid,oauth_uid,token,type)
                        VALUES ('$m[uid]','$oauth_data[open_id]','$oauth_data[access_token]','".$wx_oauth->get_oauth_type()."')");
	        $db->free_result();
	        break;
	}
    
	$login_pass=$pass1;
    require_once 'include/passport.inc.php';
	exit;
}

if($action=='bind' && !$oauth) show_msg('您不是OAUTH登录用户，无法绑定商城账号');
switch ($oauth)
{
    case 'qq':
        $qq_oauth=new mvm_oauth(array('app_id'=>$mm_qq_app_id,'app_key'=>$mm_qq_app_key),OAUTH_QQ);
        $auth_info=$qq_oauth->get_user_blog_info();
        $oauth_nick_name=$auth_info['nickname'];
        break;
    case 'sina':
        $sina_oauth=new mvm_oauth(array('app_key'=>$mm_sina_app_key,'app_secret'=>$mm_sina_app_secret),OAUTH_SINA);
        $auth_info=$sina_oauth->get_user_blog_info();
        $oauth_nick_name=$auth_info['nickname'];
        break;
    case 'wx':
        $wx_oauth=new mvm_oauth(array('app_id'=>$mm_wx_app_id,'app_secret'=>$mm_wx_app_secret),OAUTH_WX);
        $auth_info=$wx_oauth->get_user_blog_info();
        $oauth_nick_name=$auth_info['nickname'];
        break;
}

$oauth_already_register=false;    //oauth登录的用户是否被注册
if($auth_info['nick_name'])
{
    $m=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$oauth_nick_name' LIMIT 1");
    if($m)
    {
        $oauth_already_register=true;
        $auth_info['nickname']=mb_substr($auth_info['nickname'],0,25,'UTF-8');
    }
}


require 'header.php';
include $action=='bind'?template('register_oauth_bind'):template('register');
footer();