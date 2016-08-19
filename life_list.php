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
 * $Id: life_list.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require_once 'include/pager.class.php';
require 'header.php';

$c_uid=(int)$c_uid;
$m_uid=(int)$m_uid;
if($c_uid<=0 && $m_uid<=0)
{
    $title='生活圈话题';
    
    //活跃成员
    $title2='活跃成员';
    $new_join=array();
    $q=$db->query("SELECT m_uid FROM `{$tablepre}member_statistics` WHERE topic_num>0 ORDER BY topic_num DESC LIMIT 9");
    while ($rtl=$db->fetch_array($q))
    {
        $m=$db->get_one("SELECT member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
        if(!$m) continue;
        $new_join[]=array(
            'm_uid'=>$rtl['m_uid'],
            'member_id'=>$m['member_id'],
            'member_image'=>ProcImgPath($m['member_image'],'face')
        );
    }
    $db->free_result();
}
else if($m_uid>0)
{
    $m=$db->get_one("SELECT uid,member_id FROM `{$tablepre}member_table` WHERE uid='$m_uid' LIMIT 1");
    if(!$m) show_msg('检索不到指定的会员');
    $title="$m[member_id]的话题";
    
    //活跃成员
    $title2='活跃成员';
    $new_join=array();
    $q=$db->query("SELECT m_uid FROM `{$tablepre}member_statistics` WHERE topic_num>0 ORDER BY topic_num DESC LIMIT 9");
    while ($rtl=$db->fetch_array($q))
    {
        $m=$db->get_one("SELECT member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
        if(!$m) continue;
        $new_join[]=array(
            'm_uid'=>$rtl['m_uid'],
            'member_id'=>$m['member_id'],
            'member_image'=>ProcImgPath($m['member_image'],'face')
        );
    }
    $db->free_result();
}
else
{
    $comm=$db->get_one("SELECT * FROM `{$tablepre}community` WHERE uid='$c_uid' LIMIT 1");
    if(!$comm) show_msg('检索不到指定的生活圈');
    if($comm['approval_date']<10 && $m_check_uid!=1 && $m_check_uid!=$comm['m_uid']) show_msg('检索不到指定的生活圈！');
    $title=$comm['c_name'];
    
    if(!$comm['c_logo'] || !file_exists($comm['c_logo'])) $comm['c_logo']='images/noimages/noproduct.jpg';
    $comm['register_date']=date('Y-m-d',$comm['register_date']);
    $p_url=base64_encode($mm_refer_url);

    $m=$db->get_one("SELECT member_image FROM `{$tablepre}member_table` WHERE uid='$comm[m_uid]' LIMIT 1");
    $man_image=ProcImgPath($m['member_image'],'face');

    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}friend` WHERE belong_uid='$m_check_uid'");
    $man_friend_num=$rtl_tmp['cnt'];
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE m_uid='$m_check_uid'");
    $man_community_num=$rtl_tmp['cnt'];
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE m_uid='$m_check_uid'");
    $man_topic_num=$rtl_tmp['cnt'];
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE m_uid='$m_check_uid'");
    $man_comment_num=$rtl_tmp['cnt'];
    
    //最新加入
    $title2='最新加入';
    $new_join=array();
    $q=$db->query("SELECT m_uid FROM `{$tablepre}community_member` WHERE c_uid='$comm[uid]' AND approval_date>10 ORDER BY approval_date DESC LIMIT 9");
    while ($rtl=$db->fetch_array($q))
    {
        $m=$db->get_one("SELECT member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
        if(!$m) continue;
        $new_join[]=array(
            'm_uid'=>$rtl['m_uid'],
            'member_id'=>$m['member_id'],
            'member_image'=>ProcImgPath($m['member_image'],'face')
        );
    }
    $db->free_result();
}


//话题
$t=dhtmlchars($t);
if($t!='new' && $t!='hot') $t='new';
$filter_sql=" WHERE approval_date>10 ";
if($c_uid>0) $filter_sql.=" AND c_uid='$c_uid'";
if($m_uid>0) $filter_sql.=" AND m_uid='$m_uid'";
if($t=='hot')
{
    $order_sql=" ORDER BY hits DESC ";
    $hot_topic_class="class='down1'";
}
else
{
    $order_sql=" ORDER BY approval_date DESC ";
    $new_topic_class="class='down1'";
}

//话题
$arr_topic=array();
$total_count = $db->counter("{$tablepre}community_topic",$filter_sql);
$page = $page ? (int)$page : 1;
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q=$db->query("SELECT uid,t_name,m_uid,register_date,content,cover,tags 
               FROM `{$tablepre}community_topic` 
               $filter_sql  
               $order_sql 
               LIMIT $from_record,$list_num");
while ($rtl=$db->fetch_array($q))
{
    $m=$db->get_one("SELECT member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
    $m['member_image']=ProcImgPath($m['member_image'],'face');
    $rtl=array_merge($rtl,$m);
    
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE t_uid='$rtl[uid]' AND approval_date>10");
    $rtl['comment_num']=$rtl_tmp['cnt'];
    
    $rtl['content']=mb_substr(strip_tags($rtl['content']),0,120,'UTF-8');
    $rtl['register_date']=date('Y-m-d H:i:s',$rtl['register_date']);
    $rtl['detail_url']=GetBaseUrl('life_post',$rtl['uid']);
    $arr_topic[]=$rtl;
}
$db->free_result();
$page_list = $rowset->link("life_list.php?c_uid=$c_uid&m_uid=$m_uid&t=$t&page=");

include template('life_list');
footer();