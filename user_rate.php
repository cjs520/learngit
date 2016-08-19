<?php

/**
 * MVM_MALL 网上商店系统  一般页面
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-05-12 $
 * $Id: user_rate.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'include/pager.class.php';

$supid=(int)$supid;
if($supid<=0) show_msg('请选择商家');
$roll=(int)$roll;
if($roll!=0 && $roll!=1) $roll=0;


$member=$db->get_one("SELECT uid,member_id FROM `{$tablepre}member_table` WHERE uid='$supid' LIMIT 1");
$member_statistics=$db->get_one("SELECT comment_data FROM `{$tablepre}member_statistics` WHERE m_uid='$supid' LIMIT 1");
$member_statistics['comment_data']=unserialize($member_statistics['comment_data']);
if(!$member_statistics['comment_data']) $member_statistics['comment_data']=InitCommentStruct();

$good_comment_buy=get_rate_class($member_statistics['comment_data']['buyer']['good']['total']);
$good_comment=get_rate_class($member_statistics['comment_data']['seller']['good']['total'],'seller_class_');
$buy_good_rate=round($member_statistics['comment_data']['buyer']['good']['total']/
                    ($member_statistics['comment_data']['buyer']['bad']['total']+$member_statistics['comment_data']['buyer']['normal']['total']+$member_statistics['comment_data']['buyer']['good']['total']),4)*100;
$seller_good_rate=round($member_statistics['comment_data']['seller']['good']['total']/
                        ($member_statistics['comment_data']['seller']['bad']['total']+$member_statistics['comment_data']['seller']['normal']['total']+$member_statistics['comment_data']['seller']['good']['total']),4)*100;

$week_comment=$member_statistics['comment_data']['seller']['bad']['week']+$member_statistics['comment_data']['seller']['normal']['week']+$member_statistics['comment_data']['seller']['good']['week'];
$month_comment=$member_statistics['comment_data']['seller']['bad']['month']+$member_statistics['comment_data']['seller']['normal']['month']+$member_statistics['comment_data']['seller']['good']['month'];
$half_year_comment=$member_statistics['comment_data']['seller']['bad']['half_year']+$member_statistics['comment_data']['seller']['normal']['half_year']+$member_statistics['comment_data']['seller']['good']['half_year'];
$total_comment=$member_statistics['comment_data']['seller']['bad']['total']+$member_statistics['comment_data']['seller']['normal']['total']+$member_statistics['comment_data']['seller']['good']['total'];

$week_comment_buy=$member_statistics['comment_data']['buyer']['bad']['week']+$member_statistics['comment_data']['buyer']['normal']['week']+$member_statistics['comment_data']['buyer']['good']['week'];
$month_comment_buy=$member_statistics['comment_data']['buyer']['bad']['month']+$member_statistics['comment_data']['buyer']['normal']['month']+$member_statistics['comment_data']['buyer']['good']['month'];
$half_year_comment_buy=$member_statistics['comment_data']['buyer']['bad']['half_year']+$member_statistics['comment_data']['buyer']['normal']['half_year']+$member_statistics['comment_data']['buyer']['good']['half_year'];
$total_comment_buy=$member_statistics['comment_data']['buyer']['bad']['total']+$member_statistics['comment_data']['buyer']['normal']['total']+$member_statistics['comment_data']['buyer']['good']['total'];

$comment_list=array();
if($roll==0)
{
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_goods_comment` WHERE to_id='$member[member_id]'");
    $total_count=$rtl['cnt'];
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    
    $q=$db->query("SELECT from_id,comment,level,g_uid,goods_table,module FROM `{$tablepre}order_goods_comment` 
                   WHERE to_id='$member[member_id]' 
                   ORDER BY reg_date DESC 
                   LIMIT $from_record,$list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $g=$db->get_one("SELECT goods_name,supplier_id FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
        if($g)
        {
            $rtl['goods_name']=$g['goods_name'];
            $rtl['goods_url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$g['supplier_id']);
        }
        
        $rtl['level']=$mm_comment_level[$rtl['level']];
        $comment_list[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("user_rate.php?roll=$roll&page=");
}
else if($roll==1)
{
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_user_comment` WHERE to_id='$member[member_id]' AND roll='1'");
    $total_count=$rtl['cnt'];
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q=$db->query("SELECT from_id,comment,level 
                   FROM `{$tablepre}order_user_comment` 
                   WHERE to_id='$member[member_id]' AND roll='1' 
                   ORDER BY reg_date DESC 
                   LIMIT $from_record,$list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['level']=$mm_comment_level[$rtl['level']];
        $comment_list[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("user_rate.php?roll=$roll&page=");
}

require 'header.php';
require_once template('user_rate');
footer();

function InitCommentStruct()
{
    return array(
        'seller'=>array(
            'good'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0),
            'normal'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0),
            'bad'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0)
        ),
        'buyer'=>array(
            'good'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0),
            'normal'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0),
            'bad'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0)
        ),
    );
}