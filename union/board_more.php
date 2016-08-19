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
 * $Id: board_more.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'header.php';
require_once 'include/pager.class.php';

$action=dhtmlchars($action);
$board=$db->get_one("SELECT board_name_code,board_title FROM `{$tablepre}badmin_table` WHERE board_name_code='$action' AND supplier_id='$page_member_id' LIMIT 1");
if(!$board) show_msg('检索不到指定的资讯版块');

$arr_article=array();
$search_sql=" WHERE supplier_id='$page_member_id' AND ps_name='$board[board_name_code]'";
$total_count = $db->counter("{$tablepre}bmain",$search_sql);
$page = $page ? (int)$page : 1;
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q=$db->query("SELECT uid,board_subject,board_body,register_date,author,cover 
               FROM `{$tablepre}bmain` 
               $search_sql 
               ORDER BY register_date DESC 
               LIMIT $from_record,$list_num");
while ($rtl=$db->fetch_array($q))
{
    $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
    if(!$rtl['cover'] || !file_exists($rtl['cover'])) $rtl['cover']='images/noimages/noproduct.jpg';
    $rtl['url']=GetBaseUrl('article',$rtl['uid'],'id',$rtl['uid']);
    $rtl['board_body']=mb_substr(strip_tags($rtl['board_body']),0,130,'UTF-8');
    $arr_article[]=$rtl;
}
$db->free_result();


$page_list = $rowset->link("board_more.php?action=$action&page=");

require_once template('board_more');
footer();