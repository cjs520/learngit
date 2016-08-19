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
 * $Id: video_mall.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

$cat_parent=$cache->get_cache('right_tree');
include 'data/malldata/video_time.config.php';
$arr_cat=array(0=>'-- 全部 --');
foreach ($cat_parent as $val) $arr_cat[$val['uid']]=$val['category_name'];

//格式化
foreach ($arr_video_time as $key=>$val)
{
    $arr_video_time[$key][0]=sprintf('%02d',$val[0]);
    $arr_video_time[$key][1]=sprintf('%02d',$val[1]);
    $arr_video_time[$key][2]=sprintf('%02d',$val[2]);
    $arr_video_time[$key][3]=sprintf('%02d',$val[3]);
}

if($action=='list')
{
    $arr_video=array();
    $cat_uid=(int)$cat_uid;
    if(!isset($_POST['status']) && !isset($_GET['status'])) $status=-2;
    $status=(int)$status;
    
    require_once 'include/pager.class.php';
    $search_sql="WHERE register_date>0";
    
    if($m_id)
    {
        $m=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$m_id' LIMIT 1");
        if($m) $search_sql.=" AND m_uid='$m[uid]' ";
    }
    if($status>=-1) $search_sql.=" AND approval='$status'";
    if($cat_uid>0) $search_sql.=" AND cat_uid='$cat_uid'";
    
    $total_count = $db->counter("{$tablepre}video_ad",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,youku_link,title,cat_uid,description,pic,start_date,end_date,time_area,register_date,approval,total_point,rate,url 
                     FROM `{$tablepre}video_ad` 
                     $search_sql 
                     ORDER BY `uid` DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['start_date']=date('Y-m-d',$rtl['start_date']);
        $rtl['rate']=round($rtl['rate'],2);
        $rtl['cat_name']=$arr_cat[$rtl['cat_uid']];
        $rtl['end_date']=date('Y-m-d',$rtl['end_date']);
        $rtl['time_area']="{$arr_video_time[$rtl['time_area']][0]}:{$arr_video_time[$rtl['time_area']][1]}~{$arr_video_time[$rtl['time_area']][2]}:{$arr_video_time[$rtl['time_area']][3]}";
        $rtl['status']=$arr_status[$rtl['approval']];
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
        
        $arr_video[] = $rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&m_id=".urlencode($m_id)."&cat_uid=$cat_uid&status=$status&page=");
    
    $sel_status=drop_menu(array(-2=>'全部',-1=>'已驳回',0=>'未审核',1=>'已审核'),'status',$status);
    $sel_cat=drop_menu($arr_cat,'cat_uid',$cat_uid);
    
    require_once template('video_mall');
    footer();
}
else if($action=='view')
{
    $uid=(int)$uid;
    $video=$db->get_one("SELECT title,youku_id FROM `{$tablepre}video_ad` WHERE uid='$uid' LIMIT 1");
    if(!$video) exit('ERR:检索不到指定的推送视频');
    
    require_once template('video_mall_view');
    exit;
}
else if ($action=='edit' && is_numeric($uid))
{
	$uid = (int)$uid;
    $links_rt = $db->get_one("SELECT * FROM {$tablepre}forumlinks_table WHERE id='$uid' AND `supplier_id`='0' LIMIT 1");
    if($_POST && (int)$step==1)
    {
    	$logo =  $links_rt['logo'];
        if ($_FILES['logo_file']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','images/links/');
            $logo = $rowset->upload('logo_file');
            $links_rt['logo'] && file_unlink($links_rt['logo']);
        }
        $name = dhtmlchars($name);
        $url = dhtmlchars($url);
        $note = dhtmlchars($note);
        $disp = (int)$disp;
        
        $db->query("UPDATE `{$tablepre}forumlinks_table` SET 
                    displayorder = '$disp',
                    name = '$name',
                    url='$url',
                    note='$note',
                    logo='$logo' 
                    WHERE id='$uid' AND `supplier_id`='0'");
        admin_log("编辑友情链接：$name");
        $cache->delete('links',0);
        move_page(base64_decode($p_url));
    }
    @extract($links_rt,EXTR_OVERWRITE);
    require_once template('links_add');
    exit;
}
else if($action=='approval')
{
    $uid=(int)$uid;
    $v=(int)$_POST['v'];
    if($v==1)
    {
        $db->query("UPDATE `{$tablepre}video_ad` SET approval=1 WHERE uid='$uid'");
        $db->free_result();
        
        $video=$db->get_one("SELECT cat_uid,time_area FROM `{$tablepre}video_ad` WHERE uid='$uid' LIMIT 1");
        $cache->remove_cache("data/cache/rcm_tv_{$video['time_area']}.php");
        $cache->remove_cache("data/cache/hot_tv_{$video['time_area']}.php");
        exit('OK:审核成功');
    }
    else if($v==-1)
    {
        $reject=dhtmlchars($reject);
        if(!$reject) exit('ERR:请填写拒绝理由');
        $video=$db->get_one("SELECT m_uid,m_id,total_point,title FROM `{$tablepre}video_ad` WHERE uid='$uid' LIMIT 1");
        if($video)
        {
            $db->query("UPDATE `{$tablepre}video_ad` SET approval=-1,reject='$reject' WHERE uid='$uid'");
            $db->free_result();
            add_score($video['m_uid'],$video['total_point'],'视频审请驳回',"原因：$reject");
            inner_sms_send($m_check_id,$video['m_id'],'视频审请驳回',"拒绝原因：$reject");
        }
        
        exit('OK:驳回成功');
    }
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT title,pic FROM `{$tablepre}video_ad` WHERE uid='$uid'");
    if($rtl)
    {
        if(substr($rtl['pic'],0,4)!='http') file_unlink($rtl['pic']);
        admin_log("删除视频广告：$rtl[title]");
        $db->query("DELETE FROM `{$tablepre}video_ad` WHERE uid='$uid'");
        $db->free_result();
    }
    exit;
}
else show_msg('pass_worng');

