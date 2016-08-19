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
 * $Id: life.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';

$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community` WHERE m_uid='$page_member_id' AND approval_date>10");
if($rtl['cnt']<=0) show_msg("$shop_file[shop_name]还没有开通生活圈");

if($rtl['cnt']==1)
{
    $rtl=$db->get_one("SELECT uid FROM `{$tablepre}community` WHERE m_uid='$page_member_id' AND approval_date>10 LIMIT 1");
    move_page(GetBaseUrl('life_detail',$rtl['uid'],'action',1,0,true));
}

$hot_community=$cache->get_cache('get_hot_community');

$my_comm=array();
$q=$db->query("SELECT uid,c_name,c_logo,c_intro,register_date 
               FROM `{$tablepre}community` 
               WHERE m_uid='$page_member_id' AND approval_date>10");
while ($rtl=$db->fetch_array($q))
{
    if(!$rtl['c_logo']) $rtl['c_logo']='images/noimages/noproduct.jpg';
    else $rtl['c_logo']="$main_settings[mm_mall_url]/$rtl[c_logo]";
    
    $rtl['detail_url']=GetBaseUrl('life_detail',$rtl['uid'],'action','1',0,true);
    $rtl['join_url']=GetBaseUrl('life_join',$rtl['uid'],'action','1',0,true);
    
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE c_uid='$rtl[uid]' AND approval_date>10");
    $rtl['member_num']=$rtl_tmp['cnt'];
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE c_uid='$rtl[uid]' AND approval_date>10");
    $rtl['topic_num']=$rtl_tmp['cnt'];
    
    $rtl['topic']=false;
    $topic=$db->get_one("SELECT uid,t_name,content,register_date FROM `{$tablepre}community_topic` 
                         WHERE c_uid='$rtl[uid]' AND approval_date>10 
                         ORDER BY approval_date LIMIT 1");
    if($topic)
    {
        $topic['topic_url']=GetBaseUrl('life_post',$topic['uid'],'action','1',0,true);
        $topic['content']=mb_substr(strip_tags($topic['content']),0,150,'UTF-8');
        $topic['register_date']=date('Y-m-d H:i',$topic['register_date']);
        $rtl['topic']=$topic;
    }
    
    $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
    
    $my_comm[]=$rtl;
}
$db->free_result();

$mm_mall_title='圈子';
include template('life');
footer();