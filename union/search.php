<?php

/**
 * MVM_MALL 网上商店系统  商品搜索
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-03-01 $
 * $Id: search.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';
require_once 'include/pager.class.php';

$ps_search=dhtmlchars(trim($ps_search));
if(!$ps_search) move_page('goods.php?action=new');
$mm_mall_title="搜索：$ps_search";

$arr_goods=array();
$search_sql=" WHERE supplier_id='$page_member_id' AND goods_name LIKE '%$ps_search%'";
$total_count = $db->counter("$goods_table",$search_sql);
$page = $page ? (int)$page : 1;
$list_num = 12;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q=$db->query("SELECT uid,goods_name,goods_file1,supplier_id,goods_status,goods_sale_price,goods_hit 
               FROM `$goods_table` 
               $search_sql 
               ORDER BY register_date DESC 
               LIMIT $from_record,$list_num");
while ($rtl=$db->fetch_array($q))
{
    $rtl['goods_name']=str_replace($ps_search,"<font color='red'>$ps_search</font>",$rtl['goods_name']);
    $rtl=goods_array($rtl);
    $arr_goods[]=$rtl;
}
$db->free_result();

$page_list = $rowset->link("search.php?ps_search=".urlencode($ps_search)."&page=");

require_once template('search');
footer();