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
 * $Id: video_youku.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if(!$mm_youku_client_id || !$mm_youku_client_secret) show_msg('视频广告功能未开启');
require_once 'include/mvm_oauth.class.php';
$youku_oauth=new mvm_oauth(array('client_id'=>$mm_youku_client_id,'client_secret'=>$mm_youku_client_secret),OAUTH_YOUKU);
$oauth_data=$youku_oauth->get_oauth_data();

if($action=='list')
{
    
	$youku_status=$oauth_data['access_token']?'<span class="login_state fl" title="已授权登录，您可以上传视频了"></span>':'<span class="nologin_state fl" title="还未授权登录，请先进行授权登录"></span>';
    
	include template('sadmin_video_youku');
}
else if($action=='add')
{
    if(!$oauth_data['access_token']) show_msg('请先进行授权登录',"sadmin.php?module=$module&action=list");
    
    include template('sadmin_video_youku_add');
}
else if($action=='youku_interface')
{
    if(!$oauth_data['access_token']) show_msg('请先进行授权登录',"sadmin.php?module=$module&action=list");
    
    include template('sadmin_video_youku_add2');
    footer(false);
}
else if($action=='push')
{
    if($_POST && (int)$step==1)
    {
        $youku_id=dhtmlchars($youku_id);
        $title=dhtmlchars($title);
        $img=dhtmlchars($img);
        $public_type=dhtmlchars($public_type);
        $state=dhtmlchars($state);
        $url=dhtmlchars($_POST['url']);
        $arr_info=array();
        
        do
        {
            if(!$youku_id)
            {
                $arr_info['err']='优酷ID为空，推送失败';
                break;
            }
            if($public_type!='all')
            {
                $arr_info['err']='未公开视频，无法推送';
                break;
            }
            if($state!='normal')
            {
                $arr_info['err']='非正常视频，无法推送';
                break;
            }
            if(!$url)
            {
                $arr_info['err']='优酷链接为空，推送失败';
                break;
            }
            
            $row=array(
                'm_uid'=>$shop_file['m_uid'],
                'm_id'=>$shop_file['m_id'],
                'youku_id'=>$youku_id,
                'youku_link'=>$url,
                'pic'=>$img,
                'title'=>$title,
                'register_date'=>0
            );
            $db->query("DELETE FROM `{$tablepre}video_ad` WHERE m_uid='$m_check_uid' AND register_date=0");
            $db->free_result();
            $insert_id=$db->insert("`{$tablepre}video_ad`",$row);
            $arr_info['uid']=$insert_id;
        }while (0);
        
        exit(json_encode($arr_info));
    }
}
else if($action=='video_list')
{
    $page=(int)$page;
    if($page<=0) $page=1;
    $list_url="https://openapi.youku.com/v2/videos/by_me.json?client_id=$mm_youku_client_id&access_token=$oauth_data[access_token]&page=$page";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$list_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    exit($response);
}