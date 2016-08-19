<?php

/**
 * MVM_MALL 网上商店系统 团购活动
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-04-12 $
 * $Id: tv_detail.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require 'header.php';
include 'data/malldata/video_time.config.php';

$time_area=-1;
$cur_second=$m_now_time-strtotime(date('Y-m-d 0:0:0'));
foreach ($arr_video_time as $key=>$val)
{
    if($val[4]<=$cur_second && $val[5]>=$cur_second)
    {
        $time_area=$key;
        break;
    }
}

$action=(int)$action;
$video=$db->get_one("SELECT uid,youku_id,pic,title,description,url,time_area,good,bad,m_uid,time_area,cat_uid 
                     FROM `{$tablepre}video_ad` 
                     WHERE uid='$action' AND approval=1 
                     LIMIT 1");
if(!$video) show_msg('检索不到指定的视频广告','tv_shopping.php');

$shop=$db->get_one("SELECT m_uid,m_id,shop_name,province,city,county FROM `{$tablepre}member_shop` WHERE m_uid='$video[m_uid]' LIMIT 1");
include 'qrcode/func.php';
$shop_url=GetBaseUrl('index','','',$shop['m_uid']);
$qrcode_img=create_qrcode(md5($shop['m_id']),GetBaseUrl($shop_url));


$arr_qq=array();
$q=$db->query("SELECT cf_value FROM `{$tablepre}config` WHERE cf_name IN ('mm_client_qq1','mm_client_qq2') AND supplier_id='$shop[m_uid]'");
while ($rtl=$db->fetch_array($q))
{
    if(!$rtl['cf_value']) continue;
    $arr_qq[]=$rtl['cf_value'];
}
$db->free_result();

//可能还喜欢
$arr_may_fav=array();
$q=$db->query("SELECT uid,pic,title,description FROM `{$tablepre}video_ad` 
               WHERE approval=1 AND cat_uid='$video[cat_uid]' AND time_area='$time_area' AND uid<>'$video[uid]' 
               ORDER BY rate DESC 
               LIMIT 3");
while ($rtl=$db->fetch_array($q))
{
    $rtl['description']=mb_substr($rtl['description'],0,45,'UTF-8');
    $rtl['url']=GetBaseUrl('tv_detail',$rtl['uid']);
    $arr_may_fav[]=$rtl;
}
$db->free_result();

//大家都在看
$arr_all_view=array();
$q=$db->query("SELECT uid,pic,title,description FROM `{$tablepre}video_ad` 
               WHERE approval=1 AND time_area='$time_area'  
               ORDER BY rate DESC 
               LIMIT 3");
while ($rtl=$db->fetch_array($q))
{
    $rtl['description']=mb_substr($rtl['description'],0,45,'UTF-8');
    $rtl['url']=GetBaseUrl('tv_detail',$rtl['uid']);
    $arr_all_view[]=$rtl;
}
$db->free_result();

//评论
$total_comment=$video['good']+$video['bad'];
$total_all_view=sizeof($arr_all_view);
$arr_comment=array();
$q=$db->query("SELECT m_uid,comment,good_bad,register_date FROM `{$tablepre}video_comment` WHERE video_uid='$video[uid]' ORDER BY register_date DESC LIMIT 6");
while ($rtl=$db->fetch_array($q))
{
    $m=$db->get_one("SELECT uid,member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
    $m['member_image']=ProcImgPath($m['member_image'],'face');
    
    $rtl['me']=$m;
    $rtl['my_fav']=$arr_all_view[rand(0,$total_all_view-1)];
    $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
    $arr_comment[]=$rtl;
}
$db->free_result();

require_once template('tv_detail');
footer();