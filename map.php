<?php

/**
 * MVM_MALL 网上商店系统 标 签
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-01 $
 * $Id: map.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';

if($action=='get_shop_info')
{
    $sup_cat=(int)$sup_cat;
    $keywords=dhtmlchars($keywords);
    $search_sql="WHERE lat<>'0' AND lng<>'0'  ";
    if($sup_cat>0)
    {
        include 'data/malldata/category.config.php';
        include 'data/malldata/category_pid.config.php';
        require_once 'include/cat_func.func.php';
        $children_uids=get_chidldren_uids($sup_cat,$uid_2_pid,$cat);
        array_push($children_uids,$sup_cat);
        $str_children_uids=implode(',',$children_uids);
        
        $search_sql.=" AND supplier_cat IN ($str_children_uids) ";
    }
    if($keywords) $search_sql.=" AND shop_name LIKE '%$keywords%'";
    
    $shop=array();
    $q=$db->query("SELECT m_uid,shop_name,lat,lng,map_tip,province,city,county,shop_address,up_logo 
                   FROM `{$tablepre}member_shop` 
                   $search_sql");
    while($rtl=$db->fetch_array($q))
    {
        $rtl_tmp=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$rtl[m_uid]' AND cf_name='mm_tel' LIMIT 1");
        $rtl['member_tel1']=$rtl_tmp['cf_value'];
        
        $rtl_tmp=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$rtl[m_uid]' AND cf_name='mm_mobile' LIMIT 1");
        $rtl['member_tel2']=$rtl_tmp['cf_value'];
        
        !$rtl['map_tip'] && $rtl['map_tip']='尚未填写';
        $rtl['up_logo']=ProcImgPath($rtl['up_logo'],'logo');
        $rtl['url']=GetBaseUrl('index','','',$rtl['m_uid']);

        $shop[]=$rtl;
    }
    
    $str_json=json_encode($shop);
    exit($str_json);
}
else if($action=='load_fav')
{
    $arr_rtl=array();
    if(!$m_check_uid)
    {
        $arr_rtl['str_err']='您还未登录';
        exit(json_encode($arr_rtl));
    }
    
    $arr_rtl['shop']=array();
    $q=$db->query("SELECT f_uid AS shop_uid FROM `{$tablepre}favorite` WHERE m_uid='$m_check_uid' AND t='0'");
    while ($rtl=$db->fetch_array($q))
    {
        $shop=$db->get_one("SELECT m_uid,shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[shop_uid]' LIMIT 1");
        if(!$shop)
        {
            $db->query("DELETE FROM `{$tablepre}favorite` WHERE m_uid='$m_check_uid' AND f_uid='$rtl[shop_uid]' AND t='0'");
            continue;
        }
        $shop['url']=GetBaseUrl('index','','',$shop['m_uid']);
        $arr_rtl['shop'][]=$shop;
    }
    exit(json_encode($arr_rtl));
}
else if($action=='fav_shop')
{
    if(!$m_check_id) exit('ERROR:您还未登录，无法收藏商铺');
    
    $sid=(int)$sid;
    $m=$db->get_one("SELECT m_uid FROM `{$tablepre}member_shop` WHERE m_uid='$sid' LIMIT 1");
    if(!$m) exit('ERROR:该商铺不是正式商铺，无法收藏');
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}favorite` WHERE m_uid='$m_check_uid' AND t='0'");
    if($rtl['cnt']>=30) exit('ERROR:您收藏的商铺已超过30家，无法继续收藏');
    $db->query("REPLACE INTO `{$tablepre}favorite` (m_uid,f_uid,t) VALUES ('$m_check_uid','$m[m_uid]','0')");
    exit('OK:收藏成功');
}
else if($action=='del_fav')
{
    if(!$m_check_uid) exit;
    $sid=(int)$sid;
    $db->query("DELETE FROM `{$tablepre}favorite` WHERE m_uid='$m_check_uid' AND f_uid='$sid' AND t='0'");
    exit;
}
else if($action=='clear_fav')
{
    if(!$m_check_uid) exit;
    $db->query("DELETE FROM `{$tablepre}favorite` WHERE m_uid='$m_check_uid' AND t='0'");
    exit;
}

$mm_mall_title='找商铺';
require 'header.php';
include template('map');
footer();