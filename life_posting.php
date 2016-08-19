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
 * $Id: life_posting.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';

if(!$m_check_id) move_page('logging.php?action=login');
$action=(int)$action;
$comm=$db->get_one("SELECT uid,c_name,m_uid FROM `{$tablepre}community` WHERE uid='$action' AND approval_date>10 LIMIT 1");
if(!$comm) show_msg('检索不到指定的生活圈','life.php');

if($_POST && (int)$step==1)
{
    if($m_now_time-(int)$_SERVER['community_topic_time']<30) show_msg('您发表太快了，请稍等');
    
    $detail=preg_replace('/<script.*?script>/i','',$detail);
    $title=daddslashes(dhtmlchars($title));
    $tags=dhtmlchars(trim($tags));
    
    if($mm_code_use==1)
	{
		require_once 'include/captcha.class.php';
		$Captcha = new Captcha();
		!$Captcha->CheckCode($code) && show_msg('code_wrong');
	}
    if(!$detail) show_msg('请填写主题内容');
    if(!$title) show_msg('请填写标题内容');
    
    if($cover && strstr($cover,'union'))
    {
        $start_pos=strpos($cover,'union');
        $cover=substr($cover,$start_pos);
    }
    
    
    $arr_tmp=explode(' ',$tags);
    $arr_tmp2=array();
    foreach ($arr_tmp as $key=>$val)
    {
        if($key>=5) break;
        $arr_tmp2[]=$val;
    }
    $tags=implode(' ',$arr_tmp2);
    
    $row=array(
        'c_uid'=>$comm['uid'],
        'c_m_uid'=>$comm['m_uid'],
        'm_uid'=>$m_check_uid,
        't_name'=>$title,
        'content'=>$detail,
        'cover'=>$cover,
        'tags'=>$tags,
        'register_date'=>$m_now_time
    );
    $db->insert("`{$tablepre}community_topic`",$row);
    $db->free_result();
    $_SERVER['community_topic_time']=$m_now_time;
    
    show_msg('您的主题已提交，请等待管理员审核',GetBaseUrl('life_detail',$comm['uid']));
}

$detail_url=GetBaseUrl('life_detail',$comm['uid']);
$is_member=false;
if($comm['m_uid']==$m_check_uid || $db->get_one("SELECT uid FROM `{$tablepre}community_member` WHERE m_uid='$m_check_uid' AND c_uid='$comm[uid]' AND approval_date>10"))
{
    $is_member=true;
}
if(!$is_member) show_msg("您不是生活圈$comm[c_name]的成员，无法发表话题",$detail_url);



include template('life_posting');
footer();