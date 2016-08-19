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
 * $Id: tv_shopping.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require 'header.php';

//判断时段
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
//格式化
foreach ($arr_video_time as $key=>$val)
{
    $arr_video_time[$key][0]=sprintf('%02d',$val[0]);
    $arr_video_time[$key][1]=sprintf('%02d',$val[1]);
    $arr_video_time[$key][2]=sprintf('%02d',$val[2]);
    $arr_video_time[$key][3]=sprintf('%02d',$val[3]);
}

//推荐购
$cache_file="data/cache/rcm_tv_{$time_area}.php";
$rcm_tv=$cache->read_cache($cache_file,'get_rcm_tv',array('time_area'=>$time_area,'num'=>8),'rcm_tv');

//热门视频
$cache_file="data/cache/hot_tv_{$time_area}.php";
$hot_tv=$cache->read_cache($cache_file,'get_hot_tv',array('time_area'=>$time_area),'hot_tv');


require_once template('tv_shopping');
footer();

function get_rcm_tv($param)
{
    global $db,$tablepre;
    $param['num']=(int)$param['num'];
    if($param['num']<=0) $param['num']=4;
    $param['time_area']=(int)$param['time_area'];
    
    $arr_video=array();
    $q=$db->query("SELECT uid,youku_id,pic,title,description FROM `{$tablepre}video_ad` 
                   WHERE approval=1 AND time_area='$param[time_area]' 
                   ORDER BY rate DESC 
                   LIMIT $param[num]");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['description']=mb_substr($rtl['description'],0,40,'UTF-8');
        $arr_video[]=$rtl;
    }
    $db->free_result(1);
    
    return $arr_video;
}

function get_hot_tv($param)
{
    global $db,$tablepre;
    $param['time_area']=(int)$param['time_area'];
    
    $arr_video=array();
    $q=$db->query("SELECT uid,youku_id,pic,title,description FROM `{$tablepre}video_ad` 
                   WHERE approval=1 AND time_area='$param[time_area]' 
                   ORDER BY rate DESC 
                   LIMIT 20");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['url']=GetBaseUrl('tv_detail',$rtl['uid'],'action');
        $rtl['description']=mb_substr($rtl['description'],0,80,'UTF-8');
        $arr_video[]=$rtl;
    }
    $db->free_result(1);
    
    shuffle($arr_video);
    for ($i=4;$i<20;$i++) unset($arr_video[$i]);
    
    return $arr_video;
}
