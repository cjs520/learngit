<?php

/**
 * MVM_MALL 网上商店系统  品牌
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-27 $
 * $Id: brand.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'include/pager.class.php';
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';

if ($action == 'list')
{
    $filter_sql=" WHERE isCheck='1'";
    $cat_id=(int)$cat_id;
    if($cat_id>0)
    {
        $children_uids=get_chidldren_uids($cat_id,$uid_2_pid,$cat);
        array_push($children_uids,$cat_id);
        $cat_in=implode(',',$children_uids);
        $filter_sql.=" AND category_id IN ($cat_in)";
    }
    $brand_url=$rewrite==1?"brand-list-0.html":"brand.php?action=list";
    
	$total_count = $db->counter("{$tablepre}brand_table",$filter_sql);
	$page = $page ? (int)$page : 1;
	$list_num = 24;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$q = $db->query("SELECT id,brandname,logo,weburl,train 
	                 FROM `{$tablepre}brand_table` 
	                 $filter_sql 
	                 ORDER BY `train` 
	                 LIMIT $from_record,$list_num");
	while($list  = $db->fetch_array($q))
	{
		$list['url'] = "brand.php?action=view&id=$list[id]" ;
		$brand[] = $list;
	}
	$db->free_result();
	
	$page_list = $rowset->link("brand.php?action=$action&cat_id=$cat_id&page=");

	$mm_mall_title =  '品牌';
	require 'header.php';
	require_once template('brand_list');
	footer();
}
else if ($action == 'view')
{
    $id=(int)$id;
	$brand = $db->get_one("SELECT id,logo,brandname,brief,weburl FROM `{$tablepre}brand_table` WHERE id='$id' AND isCheck='1' LIMIT 1");
	if(!$brand) show_msg('找不到相关品牌');
	
	$sellshow=(int)$sellshow;
    if($sellshow!=1 && $sellshow!=2) $sellshow=1;
	$table=$sellshow==1?"{$tablepre}goods_table":"{$tablepre}goods_show";
	
	
	//处理排序
    if($rel=='change') $ac=$ac=='ASC'?'DESC':'ASC';
    if($ac!='ASC' && $ac!='DESC') $ac='DESC';
    $od=strtolower($od);
    if(!in_array($od,array('register_date','goods_sale_price','goods_hit'))) $od='register_date';
    $order_sql=" ORDER BY $od $ac";
	
    $arr_goods=array();
	$search_sql = "WHERE goods_brand='$id'";
	$total_count = $db->counter("$table","$search_sql");
	$page = $page ? (int)$page : 1;
	$list_num = 15;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$sql = "SELECT uid,goods_name,goods_sale_price,goods_file1,goods_status,goods_stock,supplier_id,goods_hit 
		    FROM `$table` 
		    $search_sql 
		    $order_sql 
		    LIMIT $from_record,$list_num";
	$q = $db->query($sql);
	while($rtl =  $db->fetch_array($q))
	{
	    $arr_goods[]=goods_array($rtl);
	}
	
	if ($rewrite == 1)
	{
		$baseurl ="brand-view-$id-" ;
		$exc='.html';
	}
	else $baseurl = "brand.php?action=view&id=$id&page=";
	
	$page_list = $rowset->link($baseurl,$exc);
	$id = (int)$id;
	
	$mm_mall_title = $brand['brandname'];
	$brand['keywords'] && $mm_keywords = $brand['keywords'];
	$brand['brief']  && $mm_description = mb_substr($brand['brief'],0,200,'UTF-8');
	$mm_description=str_replace('&nbsp;','',strip_tags($mm_description));

    require 'header.php';
	include template('brand_view');
	footer();
}
else show_msg('pass_worng');

