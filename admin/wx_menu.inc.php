<?php

/**
 * MVM_MALL 网上商店系统  友情连接管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: wx_menu.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/wx_public.class.php';
if($action=='list')
{
    if(!$mm_wx_app_id || !$mm_wx_app_secret) show_msg('请先将微信公众平台资料填写完整');
    if(!function_exists('curl_init')) show_msg('请先加载CURL模块');
    
    $wx_public=new wx_public($mm_wx_app_id,$mm_wx_app_secret);
    $menu=$wx_public->query_menu();
    if($menu->errcode) $menu='';
    
    require_once template('wx_menu');
    footer();
}
else if ($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $json_menu=trim(stripslashes($json_menu));
        //file_put_contents('1.txt',$json_menu);
        if(!$json_menu) exit('ERR:请填写菜单数据');
        $wx_public=new wx_public($mm_wx_app_id,$mm_wx_app_secret);
        $rtl=$wx_public->add_menu($json_menu);
        admin_log("编辑微信菜单");
        $rtl=json_decode($rtl);
        if($rtl->errcode!=0) exit("ERR:{$rtl->errmsg}");
        exit("OK:填写成功，二十四小时后在客户端生效");
    }
}
else if($action=='del')
{
    $wx_public=new wx_public($mm_wx_app_id,$mm_wx_app_secret);
    admin_log("删除微信菜单");
    echo $wx_public->del_menu();
    exit;
}
else if($action=='refresh')
{
    $wx_public=new wx_public($mm_wx_app_id,$mm_wx_app_secret);
    $wx_public->get_access_token(true);
    admin_log("刷新微信凭证");
    echo '刷新成功';
    exit;
}
else show_msg('pass_worng');

