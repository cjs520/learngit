<?php

/**
 * MVM_MALL 网上商店系统  登 陆
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: logging.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'include/cart.class.php';
$cart=new cart();

require_once 'include/mvm_oauth.class.php';
if($mm_qq_app_id && $mm_qq_app_key)
{
    $qq_oauth=new mvm_oauth(array('app_id'=>$mm_qq_app_id,'app_key'=>$mm_qq_app_key),OAUTH_QQ);
    $qq_login_url=$qq_oauth->get_login_url();
}
if($mm_sina_app_key && $mm_sina_app_secret)
{
    $sina_oauth=new mvm_oauth(array('app_key'=>$mm_sina_app_key,'app_secret'=>$mm_sina_app_secret),OAUTH_SINA);
    $sina_login_url=$sina_oauth->get_login_url();
}
if($mm_wx_app_id && $mm_wx_app_secret && $is_mobile && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger'))
{
    $wx_oauth=new mvm_oauth(array('app_id'=>$mm_wx_app_id,'app_secret'=>$mm_wx_app_secret),OAUTH_WX);
    $wx_login_url=$wx_oauth->get_login_url();
}

if($action=='logout')
{
	session_destroy();
	$m_check_id=$m_check_uid=$mvm_member=false;
	
	$logout_key=array(
	    'cart',
	    'activate'
	);
	
	$b_in_keyword=false;
	foreach ($logout_key as $val)
	{
		if(strstr($referer_url,$val))
		{
			$b_in_keyword=true;
			break;
		}
	}
	
	$cart->update_discount();
	if($is_mobile) move_page('index.php');
	else show_msg('成功退出',$_URL[0]);
}
else if($action == 'login')
{
	$info=array();
    $subrel=dhtmlchars($subrel);
    if($subrel) unset($m_check_id);
    
	if($m_check_id && !$subrel) show_msg('login_already',$mm_refer_url);
	
	if($_POST && (int)$setp==1)
	{
		if(!$login_id) show_msg('intput_member');
		if(!$login_pass) show_msg('password_require');
	
		//登录验证码判断
		if($mm_code_use==1)
	    {
		    require_once 'include/captcha.class.php';
		    $Captcha = new  Captcha();
		    !$Captcha->CheckCode($code) && show_msg('code_wrong');
	    }
	    
		$ori_pass=dhtmlchars($login_pass);
		$login_pass = md5($ori_pass);
		$login_id = dhtmlchars($login_id);
		require_once 'include/passport.inc.php';
		exit;
	}
	
	require 'header.php';
	require_once template('login');
	footer();
	
}
else show_msg('pass_worng');