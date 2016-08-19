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
 * $Id: infor_buy_more.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require_once 'include/pager.class.php';
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';
require 'header.php';

$cat_uid=(int)$cat_uid;
$arr_sub_cat=array();
$arr_cat_parents=get_parents($cat_uid,$uid_2_pid);
$root_parent=(int)$arr_cat_parents[1];
$filter_sql=" WHERE approval_date>=10 ";
if($cat_uid>0)
{
    $rtl=$db->get_one("SELECT uid,category_name FROM `{$tablepre}category` WHERE uid='$cat_uid' AND supplier_id='0' LIMIT 1");
    $title=$rtl['category_name'];
    
    $q=$db->query("SELECT uid,category_name FROM `{$tablepre}category` WHERE supplier_id='0' AND category_id='$cat_uid' ORDER BY category_rank");
    while ($rtl=$db->fetch_array($q))
    {
        $arr_sub_cat[]=array('uid'=>$rtl['uid'],'category_name'=>$rtl['category_name']);
    }
    $db->free_result();
    
    $arr_child_uid=get_chidldren_uids($cat_uid,$uid_2_pid,$cat);
    array_push($arr_child_uid,$cat_uid);
    $str_child_uid=implode(',',$arr_child_uid);
    $filter_sql.=" AND goods_category IN ($str_child_uid)";
}
else
{
    $title='供应信息搜索';
    foreach ($cat_parent as $val)
    {
        $arr_sub_cat[]=array('uid'=>$val['uid'],'category_name'=>$val['category_name']);
    }
}

$search_txt=dhtmlchars($search_txt);
if($search_txt) $filter_sql.=" AND goods_name LIKE '%$search_txt%'";

$arr_info=array();
$total_count = $db->counter("{$tablepre}want_buy",$filter_sql);
$page = $page ? (int)$page : 1;
$list_num = 15;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q = $db->query("SELECT uid,goods_name,intro,province,city,county,register_date FROM `{$tablepre}want_buy` 
	             $filter_sql 
	             ORDER BY approval_date DESC 
	             LIMIT $from_record,$list_num");
while($rtl = $db->fetch_array($q))
{
    $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
    $rtl['url']=GetBaseUrl('infor_buy_detail',$rtl['uid']);
    if($search_txt) $rtl['goods_name']=str_replace($search_txt,"<b class='red'>$search_txt</b>",$rtl['goods_name']);
    $arr_info[]=$rtl;
}
$db->free_result();

$page_list = $rowset->link("infor_buy_more.php?cat_uid=$cat_uid&search_txt=".urlencode($search_txt)."&page=");

$arr_tmp=array(0=>'-- 全部 --');
foreach ($cat_parent as $val) $arr_tmp[$val['uid']]=$val['category_name'];
$sel_cat=drop_menu($arr_tmp,'cat_uid');

include template('infor_buy_more');
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