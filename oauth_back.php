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
 * $Id: oauth_back.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require_once 'include/mvm_oauth.class.php';
if($_GET['oauth_type']=='qq')
{
    $qq_oauth=new mvm_oauth(array('app_id'=>$mm_qq_app_id,'app_key'=>$mm_qq_app_key),OAUTH_QQ);
    $qq_oauth->callback();
}
else if($_GET['oauth_type']=='sina')
{
    $sina_oauth=new mvm_oauth(array('app_key'=>$mm_sina_app_key,'app_secret'=>$mm_sina_app_secret),OAUTH_SINA);
    if($_GET['hack_type'])
    {
        $sina_oauth->get_member();
        exit;
    }
    else $sina_oauth->callback();
}
else if($_GET['oauth_type']=='wx')
{
    $supid=(int)$supid;
    if($supid>0)
    {
        $cfg=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE cf_name='mm_wx_token' AND supplier_id='$supid' LIMIT 1");
        $mm_wx_token=$cfg['cf_value'];
        if($_GET["signature"] && $_GET["nonce"])
        {
            require_once 'include/oauth/wx_check.class.php';
            $wechatObj = new wechatCallbackapiTest($mm_wx_token);
            $wechatObj->valid();
            exit;
        }
    }
    else
    {
        if($_GET["signature"] && $_GET["nonce"] && (int)$mm_wx_event_type==0)
        {
            require_once 'include/oauth/wx_check.class.php';
            $wechatObj = new wechatCallbackapiTest($mm_wx_token);
            $wechatObj->valid();
            exit;
        }
        else if($_GET['state']=='xyz')    //登录返回
        {
            $wx_oauth=new mvm_oauth(array('app_id'=>$mm_wx_app_id,'app_secret'=>$mm_wx_app_secret),OAUTH_WX);
            if($_GET['hack_type'])
            {
                $wx_oauth->get_member();
                exit;
            }
            else if(!$_GET['code']) exit('ERR:code is null');
            else $wx_oauth->callback(); 
        }
        else
        {
            $wx_oauth=new mvm_oauth(array('app_id'=>$mm_wx_app_id,'app_secret'=>$mm_wx_app_secret),OAUTH_WX);
            $wx_oauth->parse_event($HTTP_RAW_POST_DATA);
        }
    }
    
    echo '';
    exit;
}
else if($_GET['oauth_type']=='youku')
{
    $youku_oauth=new mvm_oauth(array('client_id'=>$mm_youku_client_id,'client_secret'=>$mm_youku_client_secret),OAUTH_YOUKU);
    $youku_oauth->callback();
}