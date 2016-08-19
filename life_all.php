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
 * $Id: life_all.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require_once 'include/pager.class.php';
require 'header.php';

$cat_uid=(int)$cat_uid;
$search_sql=" WHERE approval_date>10";
$use_index=" FORCE INDEX (`approval_date`)";

do
{
    if($cat_uid<=0) break;
    $idx=-1;
    foreach ($cat_parent as $key=>$val)
    {
        if($val['uid']==$cat_uid)
        {
            $idx=$key;
            break;
        }
    }
    if($idx<0) break;
    
    $arr_child=array(0=>$cat_uid);
    foreach ($cat_parent[$idx]['children'] as $val) $arr_child[]=$val['uid'];
    $str_cat_uid=implode(',',$arr_child);
    $search_sql.=" AND c_cat IN ($str_cat_uid)";
    $use_index=" FORCE INDEX (`c_cat`)";
}while (0);

$arr_community=array();
$total_count = $db->counter("{$tablepre}community",$search_sql);
$page = $page ? (int)$page : 1;
$list_num = 15;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q = $db->query("SELECT uid,c_logo,c_name FROM `{$tablepre}community` $use_index 
	             $search_sql 
	             ORDER BY approval_date DESC 
	             LIMIT $from_record,$list_num");
while($rtl = $db->fetch_array($q))
{
    if(!$rtl['c_logo'] || !file_exists($rtl['c_logo'])) $rtl['c_logo']='images/noimages/noproduct.jpg';
    $rtl['detail_url']=GetBaseUrl('life_detail',$rtl['uid']);
    $rtl['join_url']=GetBaseUrl('life_join',$rtl['uid']);
    
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE c_uid='$rtl[uid]' AND approval_date>10");
    $rtl['member_num']=$rtl_tmp['cnt'];
        
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE c_uid='$rtl[uid]' AND approval_date>10");
    $rtl['topic_num']=$rtl_tmp['cnt'];
    
    $arr_community[]=$rtl;
}
$db->free_result();

$page_list = $rowset->link("life_all.php?cat_uid=$cat_uid&page=");

include template('life_all');
footer();