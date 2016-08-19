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
 * $Id: sign.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require_once 'header.php';

if(!$is_mobile) exit;
require_once 'include/mvm_oauth.class.php';
if($is_wx)
{
    $wx_oauth=new mvm_oauth(array('app_id'=>$mm_wx_app_id,'app_secret'=>$mm_wx_app_secret),OAUTH_WX);
    $mm_url['login']=$wx_oauth->get_login_url();
}

//判断签到
$is_sign=false;
if($m_check_uid && $is_mobile)
{
    $rtl=$db->get_one("SELECT m_uid FROM `{$tablepre}member_sign` WHERE m_uid='$m_check_uid' AND register_date='$today_timestamp' LIMIT 1");
    if($rtl) $is_sign=true;
}

include template('sign');
footer();

