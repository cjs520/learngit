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

$hot_community=$cache->get_cache('get_hot_community');

$i=0;
$hot_topic=array();
foreach ($hot_community as $val)
{
    if($i>=5) break;
    $i++;
    $rtl=$db->get_one("SELECT uid,t_name,register_date,m_uid,tags 
                       FROM `{$tablepre}community_topic` 
                       WHERE c_uid='$val[uid]' AND approval_date>10 
                       ORDER BY approval_date DESC 
                       LIMIT 1");
    if(!$rtl) continue;
    
    $rtl['topic_url']=GetBaseUrl('life_post',$rtl['uid']);
    $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
    
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE t_uid='$rtl[uid]' AND approval_date>10");
    $rtl['comment_num']=(int)$rtl_tmp['cnt'];
    
    $rtl_tmp=$db->get_one("SELECT member_id FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
    $rtl['member_id']=$rtl_tmp['member_id'];
    
    $hot_topic[]=$rtl;
}

include template('life');
footer();

function get_rcm_community()
{
    global $cache;
    $arr_community=$cache->read_cache("data/cache/rcm_community.cache.php",
                                      'get_rcm_community_from_db',
                                      false,
                                      'arr_community');
    
    return $arr_community;
}

function get_rcm_community_from_db()
{
    global $db,$tablepre;
    $arr_community=array();
    
    $q=$db->query("SELECT uid,c_name,c_logo,c_proclaim,m_id,register_date 
                   FROM `{$tablepre}community` 
                   WHERE approval_date>10 AND od>0
                   ORDER BY approval_date DESC 
                   LIMIT 2");
    while ($rtl=$db->fetch_array($q))
    { 
        if(!$rtl['c_logo'] || !file_exists($rtl['c_logo'])) $rtl['c_logo']='images/noimages/noproduct.jpg';
        $rtl['detail_url']=GetBaseUrl('life_detail',$rtl['uid']);
        $rtl['join_url']=GetBaseUrl('life_join',$rtl['uid']);
        $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE c_uid='$rtl[uid]' AND approval_date>10");
        $rtl['member_num']=$rtl_tmp['cnt'];
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE c_uid='$rtl[uid]' AND approval_date>10");
        $rtl['topic_num']=$rtl_tmp['cnt'];
        
        $rtl['topic']=array();
        $topic=$db->get_one("SELECT uid,t_name,content,register_date FROM `{$tablepre}community_topic` 
                             WHERE c_uid='$rtl[uid]' AND approval_date>10 
                             ORDER BY approval_date DESC 
                             LIMIT 1");
        if($topic)
        {
            $topic['content']=mb_substr(strip_tags($topic['content']),0,60,'UTF-8');
            $topic['register_date']=date('Y-m-d',$topic['register_date']);
            $topic['topic_url']=GetBaseUrl('life_post',$topic['uid']);
            $rtl['topic']=$topic;
        }
        
        $arr_community[]=$rtl;
    }
    $db->free_result();
    
    return $arr_community;
}

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

function get_act_member()
{
    global $cache;
    $arr_member=$cache->read_cache("data/cache/act_member.cache.php",
                                   'get_act_member_from_db',
                                   false,
                                   'arr_member');
    
    return $arr_member;
}

function get_act_member_from_db()
{
    global $tablepre,$db;
    
    $arr_member=array();
    $q=$db->query("SELECT m_uid,topic_num FROM `{$tablepre}member_statistics` 
                   WHERE topic_num>0 
                   ORDER BY topic_num DESC 
                   LIMIT 5");
    while ($rtl=$db->fetch_array($q))
    {
        $m=$db->get_one("SELECT member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
        $m['member_image']=ProcImgPath($m['member_image'],'face');
        $rtl=array_merge($rtl,$m);
        
        $rtl['topic']=array();
        $topic=$db->get_one("SELECT uid,t_name FROM `{$tablepre}community_topic` WHERE m_uid='$rtl[m_uid]' AND approval_date>10 ORDER BY approval_date DESC LIMIT 1");
        if($topic)
        {
            $rtl['t_name']=$topic['t_name'];
            $rtl['t_url']=GetBaseUrl('life_post',$topic['uid']);
        }
        
        
        $arr_member[]=$rtl;
    }
    $db->free_result();
    
    return $arr_member;
}

function get_share()
{
    global $cache;
    $arr_share=$cache->read_cache('data/cache/life_share.cache.php',
                                  'get_share_from_db',
                                  false,
                                  'arr_share');
    
    return $arr_share;
}

function get_share_from_db()
{
    global $db,$tablepre,$cache;
    
    $arr_share=array();
    $q=$db->query("SELECT uid,goods_name,og_uid,module,g_uid,goods_table,supplier_id,m_uid,pics,comment FROM `{$tablepre}order_share` 
                   ORDER BY uid DESC
                   LIMIT 4");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
        if(!$rtl['comment']) $rtl['comment']='这家伙很懒，什么都没留下';
        else $rtl['comment']=mb_substr($rtl['comment'],0,20,'UTF-8').'...';
        
        $m=$db->get_one("SELECT member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
        $rtl['member_image']=ProcImgPath($m['member_image'],'face');
        $rtl['member_id']=$m['member_id'];
        
        $rtl['pics']=unserialize($rtl['pics']);
        if(!is_array($rtl['pics']) || !$rtl['pics'])
        {
            $g=$db->get_one("SELECT goods_file1 FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
            $rtl['pics']=array(0=>ProcImgPath($g['goods_file1']));
        }
        $rtl['cover']=$rtl['pics'][0];
        
        $arr_share[]=$rtl;
    }
    $db->free_result();
    
    return $arr_share;
}