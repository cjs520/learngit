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
 * $Id: wx_menu.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/wx_public.class.php';
if($action=='list')
{
    if(!$ucfg['mm_wx_app_id'] || !$ucfg['mm_wx_app_secret']) show_msg('请先将微信公众平台资料填写完整');
    if(!function_exists('curl_init')) show_msg('请先加载CURL模块');
    
    $wx_public=new wx_public($ucfg['mm_wx_app_id'],$ucfg['mm_wx_app_secret']);
    $menu=$wx_public->query_menu();
    if($menu->errcode) $menu='';
    
    require_once template('sadmin_wx_menu');
    footer();
}
else if ($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $json_menu=trim(stripslashes($json_menu));
        if(!$json_menu) exit('ERR:请填写菜单数据');
        $wx_public=new wx_public($ucfg['mm_wx_app_id'],$ucfg['mm_wx_app_secret']);
        $rtl=$wx_public->add_menu($json_menu);
        
        $rtl=json_decode($rtl);
        if($rtl->errcode!=0) exit("ERR:{$rtl->errmsg}");
        exit("OK:填写成功，二十四小时后在客户端生效");
    }
}
else if($action=='del')
{
    $wx_public=new wx_public($ucfg['mm_wx_app_id'],$ucfg['mm_wx_app_secret']);
    echo $wx_public->del_menu();
    exit;
}
else if($action=='refresh')
{
    $wx_public=new wx_public($ucfg['mm_wx_app_id'],$ucfg['mm_wx_app_secret']);
    $wx_public->get_access_token(true);
    echo '刷新成功';
    exit;
}