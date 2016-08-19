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
 * $Id: life_post.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';

if($cmd=='preview')
{
    $topic=array(
        't_name'=>$title,
        'content'=>$detail,
        'tags'=>$tags
    );
    include template('life_post_preview');
    footer();
}

$action=(int)$action;
if($action<=0) show_msg('指定的话题错误','life.php');
$topic=$db->get_one("SELECT uid,c_uid,m_uid,t_name,hits,content,tags,register_date FROM `{$tablepre}community_topic` WHERE uid='$action' AND approval_date>10 LIMIT 1");
if(!$topic) show_msg('检索不到指定的话题','life.php');

$comm=$db->get_one("SELECT * FROM `{$tablepre}community` WHERE uid='$topic[c_uid]' LIMIT 1");
if(!$comm) show_msg('检索不到指定的生活圈');

$db->query("UPDATE `{$tablepre}community_topic` SET hits=hits+1 WHERE uid='$topic[uid]'");
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE t_uid='$topic[uid]'");
$topic['comment_num']=$rtl['cnt'];
$topic['register_date']=date('Y-m-d H:i:s',$topic['register_date']);
$topic['content']=filter_editor_img($topic['content']);

$topic_member=$db->get_one("SELECT uid,member_id,member_image FROM  `{$tablepre}member_table` WHERE uid='$topic[m_uid]' LIMIT 1");
$topic_member['member_image']=ProcImgPath($topic_member['member_image'],'face');
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE m_uid='$topic_member[uid]'");
$topic_member['community_num']=$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}friend` WHERE belong_uid='$topic_member[uid]'");
$topic_member['friend_num']=$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE m_uid='$topic_member[uid]'");
$topic_member['topic_num']=$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE m_uid='$topic_member[uid]'");
$topic_member['comment_num']=$rtl['cnt'];


if(!$comm['c_logo'] || !file_exists($comm['c_logo'])) $comm['c_logo']='images/noimages/noproduct.jpg';
$comm['register_date']=date('Y-m-d',$comm['register_date']);
$p_url=base64_encode($mm_refer_url);

$m=$db->get_one("SELECT uid,member_image,isSupplier FROM `{$tablepre}member_table` WHERE uid='$comm[m_uid]' LIMIT 1");
$man_image=ProcImgPath($m['member_image'],'face');
if($m['isSupplier']>1) $man_shop_url=GetBaseUrl('index','','',$m['uid']);

$rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}friend` WHERE belong_uid='$m_check_uid'");
$man_friend_num=$rtl_tmp['cnt'];
$rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE m_uid='$m_check_uid'");
$man_community_num=$rtl_tmp['cnt'];
$rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE m_uid='$m_check_uid'");
$man_topic_num=$rtl_tmp['cnt'];
$rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE m_uid='$m_check_uid'");
$man_comment_num=$rtl_tmp['cnt'];


include template('life_post');
footer();

function get_new_topic()
{
    global $cache;
    $arr_topic=$cache->read_cache("data/cache/new_topic.cache.php",
                                  'get_new_topic_from_db',
                                  false,
                                  'arr_topic');
    return $arr_topic;
}

function get_new_topic_from_db()
{
    global $tablepre,$db;

    $arr_topic=array();
    $q=$db->query("SELECT uid,t_name FROM `{$tablepre}community_topic` 
                   WHERE approval_date>10 
                   ORDER BY approval_date DESC 
                   LIMIT 11");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['url']=GetBaseUrl('life_post',$rtl['uid']);
        $arr_topic[]=$rtl;
    }
    $db->free_result();
    
    return $arr_topic;
}