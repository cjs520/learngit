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
 * $Id: video_time.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    include 'data/malldata/video_time.config.php';
    
    
    require_once template('video_time');
    footer();
}
else if($action=='add')
{
    if($_POST && (int)$step==1)
    {
        if(!is_array($start_hour) || !is_array($start_minute) || !is_array($end_hour) || !is_array($end_minute) || !is_array($apply_point)) exit('ERR:提交格式错误');
        $start_hour_size=sizeof($start_hour);
        if($start_hour_size!=sizeof($start_minute) || $start_hour_size!=sizeof($end_hour) || $start_hour_size!=sizeof($end_minute) || $start_hour_size!=sizeof($apply_point)) exit('ERR:数据记录数量错误');
        
        $arr_video_time=array();
        foreach ($start_hour as $key=>$val)
        {
            $t1=(int)$start_hour[$key];
            $t2=(int)$start_minute[$key];
            $t3=(int)$end_hour[$key];
            $t4=(int)$end_minute[$key];
            $p=(int)$apply_point[$key];
            if($t1<=0 && $t3<=0 && $p<=0) continue;
            
            $arr_video_time[]=array(
                $t1,$t2,$t3,$t4,$t1*3600+$t2*60,$t3*3600+$t4*60,$p
            );
        }
        file_put_contents('data/malldata/video_time.config.php','<?php $arr_video_time='.var_export($arr_video_time,true).'; ?>');
        
        admin_log("编辑视频广告时段");
    }
    exit('OK:时段设置成功');
}
else show_msg('pass_worng');

