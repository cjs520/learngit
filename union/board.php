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

$arr_board=array();
$q=$db->query("SELECT board_name_code,board_title FROM `{$tablepre}badmin_table` WHERE supplier_id='$page_member_id' ORDER BY od");
while ($rtl=$db->fetch_array($q))
{
    $rtl['article']=GetBoard($rtl['board_name_code'],0,12);
    $arr_board[]=$rtl;
}
$db->free_result();

$mm_mall_title='商铺动态';
require 'header.php';
require_once template('board_list');
footer();