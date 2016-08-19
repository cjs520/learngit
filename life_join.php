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
 * $Id: life_join.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';

$action=(int)$action;
if(!$m_check_id) move_page(GetBaseUrl('logging','login'));
$comm=$db->get_one("SELECT * FROM `{$tablepre}community` WHERE uid='$action' LIMIT 1");
if(!$comm) show_msg('检索不到指定的生活圈');
if($comm['approval_date']<10 && $m_check_uid!=1 && $m_check_uid!=$comm['m_uid']) show_msg('检索不到指定的生活圈！');

if(!$comm['c_logo'] || !file_exists($comm['c_logo'])) $comm['c_logo']='images/noimages/noproduct.jpg';
$comm['register_date']=date('Y-m-d',$comm['register_date']);
$p_url=base64_encode($mm_refer_url);
$cat=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$comm[c_cat]' LIMIT 1");
$comm['c_cat']=$cat['category_name'];

$invite_url=GetBaseUrl('life_friends',$comm['uid']);
$join_url=GetBaseUrl('life_join',$comm['uid']);
$is_member=false;
if($comm['m_uid']==$m_check_uid || $db->get_one("SELECT uid FROM `{$tablepre}community_member` WHERE m_uid='$m_check_uid' AND c_uid='$comm[uid]' AND approval_date>10"))
{
    $is_member=true;
}

$rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE c_uid='$comm[uid]' AND approval_date>10");
$member_num=$rtl_tmp['cnt'];
$rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE c_uid='$comm[uid]' AND approval_date>10");
$topic_num=$rtl_tmp['cnt'];
$rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE c_uid='$comm[uid]' AND approval_date>10");
$comment_num=$rtl_tmp['cnt'];

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

//最新加入
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

include template('life_join');
footer();