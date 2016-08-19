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
 * $Id: tv_list.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'include/pager.class.php';
require 'header.php';

//判断时段
include 'data/malldata/video_time.config.php';
if(isset($time_area)) $time_area=(int)$time_area;
else
{
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
}
//格式化
foreach ($arr_video_time as $key=>$val)
{
    $arr_video_time[$key][0]=sprintf('%02d',$val[0]);
    $arr_video_time[$key][1]=sprintf('%02d',$val[1]);
    $arr_video_time[$key][2]=sprintf('%02d',$val[2]);
    $arr_video_time[$key][3]=sprintf('%02d',$val[3]);
}

$cat_uid=(int)$cat_uid;
$arr_video=array();
$search_sql=" WHERE approval=1 AND time_area='$time_area'";
if($cat_uid>0) $search_sql.=" AND cat_uid='$cat_uid'";
$total_count = $db->counter("{$tablepre}video_ad",$search_sql);
$page = $page ? (int)$page : 1;
$list_num = 24;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q = $db->query("SELECT uid,youku_id,title,description,pic FROM `{$tablepre}video_ad` 
	             $search_sql 
	             ORDER BY rate DESC 
	             LIMIT $from_record,$list_num");
while($rtl = $db->fetch_array($q))
{
    $rtl['url']=GetBaseUrl('tv_detail',$rtl['uid']);
    $rtl['description']=mb_substr($rtl['description'],0,80,'UTF-8');
    $arr_video[]=$rtl;
}
$db->free_result();
$page_list = $rowset->link("tv_list.php?time_area=$time_area&cat_uid=$cat_uid&page=");

require_once template('tv_list');
footer();