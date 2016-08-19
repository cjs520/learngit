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
 * $Id: video_mall.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
include 'data/malldata/video_time.config.php';

$arr_cat=array();
foreach ($cat_parent as $val) $arr_cat[$val['uid']]=$val['category_name'];

if($action=='list')
{
    $arr_video=array();
	require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}video_ad","m_uid='$page_member_id' AND register_date>0");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,youku_link,title,cat_uid,description,pic,start_date,end_date,time_area,register_date,approval,total_point,url 
                     FROM `{$tablepre}video_ad` 
                     WHERE m_uid='$page_member_id' AND register_date>0 
                     ORDER BY `register_date` DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {   
        $rtl['start_date']=date('Y-m-d',$rtl['start_date']);
        $rtl['cat_name']=$arr_cat[$rtl['cat_uid']];
        $rtl['end_date']=date('Y-m-d',$rtl['end_date']);
        $rtl['time_area']="{$arr_video_time[$rtl['time_area']][0]}:{$arr_video_time[$rtl['time_area']][1]}~{$arr_video_time[$rtl['time_area']][2]}:{$arr_video_time[$rtl['time_area']][3]}";
        $rtl['status']=$arr_status[$rtl['approval']];
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
        
        $arr_video[]=$rtl;
    }
    
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&page=");
    $db->free_result();
	include template('sadmin_video_mall');
}
else if($action=='add')
{
    $uid=(int)$uid;
    $video_ad=$db->get_one("SELECT uid,youku_id,youku_link,pic,title,url FROM `{$tablepre}video_ad` 
                            WHERE uid='$uid' AND m_uid='$page_member_id' AND register_date<=0 
                            LIMIT 1");
    if(!$video_ad) show_msg('检索不到您的推送记录',"sadmin.php?module=$module&action=list");
    
	if($_POST && (int)$step==1)
	{
	    $title=dhtmlchars($title);
	    $description=mb_substr(dhtmlchars($description),0,100);
	    $start_date=strtotime($start_date);
	    $end_date=strtotime($end_date)+24*3600-1;
	    $time_area=(int)$time_area;
	    $pic=$video_ad['pic'];
	    $cat_uid=(int)$cat_uid;
	    
	    if($start_date>$end_date) show_msg('终止时间不能小于起始时间');
	    if(!key_exists($time_area,$arr_video_time)) show_msg('您选择的广告时段不存在');
	    $total_days=ceil(($end_date-$start_date)/24/3600);
	    $total_point=$arr_video_time[$time_area][6]*$total_days;
	    if($mvm_member['member_point']<$total_point) show_msg('您的积分不足，请先充值');
	    $rate=$total_point/$total_days*(1+$total_days/10);
	    
        if ($_FILES['pic']['name']!='')
        {
            require_once 'include/upfile.class.php';
            require_once 'include/pic.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','upload/video/');
            $pic = $rowset->upload('pic');
            $pic=pic::PicZoom($pic,291,218);
        }
        
        $row=array(
            'title'=>$title,
            'description'=>$description,
            'url'=>$_POST['url'],
            'cat_uid'=>$cat_uid,
            'start_date'=>$start_date,
            'end_date'=>$end_date,
            'time_area'=>$time_area,
            'pic'=>$pic,
            'total_point'=>$total_point,
            'rate'=>$rate,
            'register_date'=>$m_now_time
        );
        $db->update("`{$tablepre}video_ad`",$row," uid='$video_ad[uid]' ");
        add_score($m_check_uid,-$total_point,'申请视频广告',"申请视频广告$title");
        
        show_msg('推送成功，管理员将在24小时之内进行审核',"sadmin.php?module=$module&action=list");
    }
    
    $sel_cat=drop_menu($arr_cat,'cat_uid');
    
    //格式化
    foreach ($arr_video_time as $key=>$val)
    {
        $arr_video_time[$key][0]=sprintf('%02d',$val[0]);
        $arr_video_time[$key][1]=sprintf('%02d',$val[1]);
        $arr_video_time[$key][2]=sprintf('%02d',$val[2]);
        $arr_video_time[$key][3]=sprintf('%02d',$val[3]);
    }
    
    include template('sadmin_video_mall_add');
}
else if($action=='edit')
{
    $score=(int)$score;
    $uid=(int)$uid;
    
    if($score<=0) exit('ERR:追加的分数错误，请正确填写');
    if($score>$mvm_member['member_point']) exit('ERR:您的积分不足，请先充值');
    $video_ad=$db->get_one("SELECT uid,start_date,end_date,total_point,rate,approval FROM `{$tablepre}video_ad` WHERE uid='$uid' AND m_uid='$page_member_id' LIMIT 1");
    if(!$video_ad) exit('ERR:检索不到指定的视频');
    if($video_ad['approval']<0) exit('被驳回的视频，无法追加积分');
    
    $total_days=ceil(($video_ad['end_date']-$video_ad['start_date'])/24/3600);
    $total_point=$video_ad['total_point']+$score;
    $rate=$total_point/$total_days*(1+$total_days/10);
    $row=array(
        'total_point'=>$total_point,
        'rate'=>$rate
    );
    $db->update("`{$tablepre}video_ad`",$row," uid='$video_ad[uid]' ");
    
    $cache->remove_cache("data/cache/rcm_tv_{$video['time_area']}.php");
    $cache->remove_cache("data/cache/hot_tv_{$video['time_area']}.php");
    exit('OK:分数追加成功');
}