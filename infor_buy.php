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
 * $Id: infor_buy.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';
require 'header.php';

$arr_tmp=array(0=>'-- 全部 --');
foreach ($cat_parent as $val) $arr_tmp[$val['uid']]=$val['category_name'];
$sel_cat=drop_menu($arr_tmp,'cat_uid');

$mm_mall_title='求购信息';

include template('infor_buy');
footer();

function get_infor_list($cat_uid,$is_top=0,$limit=5)
{
    global $cache;
    
    $arr_infor_buy=$cache->read_cache("data/cache/buy_info_{$cat_uid}_{$is_top}.cache.php",
                                      'get_infor_list_from_db',
                                      array('cat_uid'=>$cat_uid,'is_top'=>$is_top,'limit'=>$limit),
                                      'infor_buy');
    
    return $arr_infor_buy;
}

function get_infor_list_from_db($arr_param)
{
    global $db,$tablepre,$cat,$uid_2_pid;
    $cat_uid=(int)$arr_param['cat_uid'];
    $limit=(int)$arr_param['limit'];
    $limit<=0 && $limit=5;
    
    $arr_child_uid=get_chidldren_uids($cat_uid,$uid_2_pid,$cat);
    array_push($arr_child_uid,$cat_uid);
    $str_child_uid=implode(',',$arr_child_uid);
    
    $search_sql='';
    if($arr_param['is_top']) $search_sql=" AND od>0";
    $arr_infor_buy=array();
    $q=$db->query("SELECT uid,goods_name,intro,province,city,county,register_date FROM `{$tablepre}want_buy` 
                   WHERE goods_category IN ($str_child_uid) AND approval_date>=10 $search_sql 
                   ORDER BY approval_date DESC 
                   LIMIT $limit");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
        $rtl['url']=GetBaseUrl('infor_buy_detail',$rtl['uid']);
        $arr_infor_buy[]=$rtl;
    }
    $db->free_result();
    
    return $arr_infor_buy;
}