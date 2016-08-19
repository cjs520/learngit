<?php

/**
 * MVM_MALL 网上商店系统  分类显示
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: category.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require 'header.php';
require_once 'include/pager.class.php';

$action=(int)$action;
$cat=$db->get_one("SELECT uid,category_name FROM `{$tablepre}category` WHERE uid='$action' AND supplier_id='$page_member_id' LIMIT 1");
if(!$cat) show_msg('检索不到指定的商品分类');


$arr_children_uid=get_category_chidlren($action);
$str_uid=implode(',',$arr_children_uid);

$arr_goods=array();
$search_sql=" WHERE supplier_id='$page_member_id' AND (supplier_cat IN ($str_uid) OR supplier_cat2 IN ($str_uid) OR supplier_cat3 IN ($str_uid))";
$total_count = $db->counter("$goods_table",$search_sql);
$page = $page ? (int)$page:1;
$list_num = 20;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q = $db->query("SELECT uid,goods_name,goods_file1,goods_hit,goods_sale_price,goods_status,goods_stock,supplier_id 
                 FROM `$goods_table` FORCE INDEX (`register_date`)
                 $search_sql 
                 ORDER BY register_date DESC
                 LIMIT $from_record,$list_num");
while($rtl = $db->fetch_array($q))
{
    $rtl=goods_array($rtl);
    $arr_goods[] = $rtl;
}
$db->free_result();
$page_list = $rowset->link("category.php?action=$action&page=");

$navigation="<a href='./'>首页</a> &gt;&gt; $cat[category_name]";

require_once template('category_grid');
footer();
