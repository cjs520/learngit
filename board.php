<?php

/**
 * MVM_MALL 网上商店系统  公告牌列表
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-14 $
 * $Id: board.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once MVMMALL_ROOT . 'header.php';
require_once MVMMALL_ROOT.'include/pager.class.php';

if($action)
{
    $action = dhtmlchars($action);
    $mm_board=$db->get_one("SELECT board_title FROM `{$tablepre}badmin_table` WHERE supplier_id='0' AND board_name_code='$action' LIMIT 1");
    if(!$mm_board) show_msg('检索不到指定的文章版块');
}
else if($ps_search)
{
    $ps_search=dhtmlchars(strip_tags($ps_search));
    $mm_board=array('board_title'=>"搜索 $ps_search");
}
else show_msg('参数传递错误');


$b_cat=array();
$q=$db->query("SELECT uid,board_name_code,board_title FROM `{$tablepre}badmin_table` 
               WHERE supplier_id='0' 
               ORDER BY od DESC");
while($rtl=$db->fetch_array($q))
{
    $rtl['url']=GetBaseUrl('board',$rtl['board_name_code']);
    $b_cat[$rtl['board_name_code']]=$rtl;
}


//文章列表
$arr_board=array();
$search_sql=" WHERE supplier_id='0' ";
$order_sql=" ORDER BY register_date DESC ";
if($action)
{
    $search_sql.=" AND ps_name='$action' ";
    $order_sql=" ORDER BY od DESC ";
}
if($ps_search) $search_sql.=" AND board_subject LIKE '%$ps_search%' ";
$total_count = $db->counter("{$tablepre}bmain",$search_sql);
$page = (int)$page;
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$result = $db->query("SELECT uid,author,board_subject,board_body,register_date,ps_name 
                      FROM `{$tablepre}bmain` 
                      $search_sql 
                      $order_sql 
                      LIMIT $from_record,$list_num");
while($rtl = $db->fetch_array($result))
{
    if($ps_search) $rtl['board_subject']=str_replace($ps_search,"<font color='#f00'>$ps_search</font>",$rtl['board_subject']);
    $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
    $rtl['board_body']=mb_substr(strip_tags($rtl['board_body']),0,100,'UTF-8');
    $rtl['url'] = $rewrite==1 ? "article-$rtl[ps_name]-$rtl[uid].html":"article.php?action=$rtl[ps_name]&id=$rtl[uid]";
	$arr_board[]=$rtl;
}

$page_list = $rowset->link("board.php?action=$action&ps_search=".urlencode($ps_search)."&page=");

$mm_mall_title = $mm_board['board_title'];     //标题
$mm_keywords = $mm_board['board_title'];     //关键字
$mm_description = $mm_board['board_title'];    //描述
require_once template('board_list');
footer();