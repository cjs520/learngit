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
 * $Id: lucky.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';

if($_POST && (int)$step==1)
{
    $rec_uid=(int)$rec_uid;
    $name=dhtmlchars($name);
    $address=dhtmlchars($address);
    $tel=dhtmlchars($tel);
    $memo=dhtmlchars($memo);
    
    if($rec_uid<=0) exit('ERR:检索不到指定的获奖记录');
    $lucky_rec=$db->get_one("SELECT uid,m_uid FROM `{$tablepre}lucky_rec` WHERE uid='$rec_uid' LIMIT 1");
    if(!$lucky_rec) exit('ERR:检索不到指定的获奖记录！');
    if($lucky_rec['m_uid']!=$m_check_uid) exit('ERR:您指定的获奖记录错误，请联系管理员');
    
    $db->query("UPDATE `{$tablepre}lucky_rec` SET 
                name='$name',
                address='$address',
                tel='$tel',
                memo='$memo' 
                WHERE uid='$rec_uid'");
    exit('OK:您的收货信息已提交，请等待管理员发货');
}

$lucky=$db->get_one("SELECT * FROM `{$tablepre}lucky_table` ORDER BY uid DESC LIMIT 1");
$lucky['start_time']=date('Y-m-d H:i:s',$lucky['start_time']);
$lucky['end_time']=date('Y-m-d H:i:s',$lucky['end_time']);

$arr_cur_rec=array();
$q=$db->query("SELECT m_id,lucky_g_uid FROM `{$tablepre}lucky_rec` WHERE lucky_uid='$lucky[uid]' AND lucky_g_uid>0 LIMIT 18");
while ($rtl=$db->fetch_array($q))
{
    $rtl_tmp=$db->get_one("SELECT goods_name FROM `{$tablepre}lucky_goods` WHERE uid='$rtl[lucky_g_uid]' LIMIT 1");
    if(!$rtl_tmp) continue;
    $rtl['goods_name']=$rtl_tmp['goods_name'];
    $arr_cur_rec[]=$rtl;
}

$arr_old_rec=array();
$q=$db->query("SELECT m_id,lucky_g_uid FROM `{$tablepre}lucky_rec` WHERE lucky_uid<>'$lucky[uid]' AND lucky_g_uid>0 ORDER BY lucky_uid DESC LIMIT 24");
while ($rtl=$db->fetch_array($q))
{
    $rtl_tmp=$db->get_one("SELECT goods_name FROM `{$tablepre}lucky_goods` WHERE uid='$rtl[lucky_g_uid]' LIMIT 1");
    if(!$rtl_tmp) continue;
    $rtl['goods_name']=$rtl_tmp['goods_name'];
    $arr_old_rec[]=$rtl;
}

$page=$db->get_one("SELECT page_body FROM `{$tablepre}page_table` WHERE supplier_id='0' AND page_name='rule_lottery' LIMIT 1");
$lottery_rule=$page['page_body'];

//本期奖品
$arr_rewards=array();
if($lucky)
{
    $q=$db->query("SELECT lucky_name,goods_name,url,goods_img FROM `{$tablepre}lucky_goods` WHERE lucky_uid='$lucky[uid]' ORDER BY od");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl[goods_img]=IMG_URL.$rtl[goods_img];
        $arr_rewards[]=$rtl;
    }
    $db->free_result();
}



include template('lucky');
footer();