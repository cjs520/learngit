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
 * $Id: daren.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';

$order_share_cat=$cache->read_cache('data/cache/order_share_cat.cache.php',
                                    'get_share_cat',
                                    false,
                                    'get_share_cat');

$share_star=array();
$q=$db->query("SELECT m_uid FROM `{$tablepre}member_statistics` WHERE week_share_num>0 ORDER BY week_share_num DESC LIMIT 5");
while ($rtl=$db->fetch_array($q))
{
    $m=$db->get_one("SELECT uid,member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
    if(!$m) continue;
    $m['member_image']=ProcImgPath($m['member_image'],'face');
    
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_share` WHERE m_uid='$m[uid]'");
    $m['share_num']=$rtl_tmp['cnt'];
    
    $share_star[]=$m;
}
$db->free_result();

include template('daren');
footer();

function get_share_cat()
{
    global $db,$tablepre;
    
    $order_share_cat=array();
    $q=$db->query("SELECT cat_uid,category_name FROM `{$tablepre}order_share_cat` WHERE category_id=0 ORDER BY od DESC");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['children']=array();
        $q2=$db->query("SELECT cat_uid,category_name FROM `{$tablepre}order_share_cat` WHERE category_id='$rtl[cat_uid]' ORDER BY od DESC");
        while ($rtl2=$db->fetch_array($q2))
        {
            $rtl['children'][$rtl2['cat_uid']]=$rtl2;
        }
        $db->free_result(1);
        
        $order_share_cat[$rtl['cat_uid']]=$rtl;
    }
    $db->free_result();
    
    return $order_share_cat;
}