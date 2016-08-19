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
 * $Id: certi.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');
require_once 'include/common.inc.php';
require 'header.php';

require_once 'include/pager.class.php';

$certi = array();
$search_sql="WHERE supplier_id='$page_member_id'";
$total_count = $db->counter("{$tablepre}certi",$search_sql);
$page = $page ? (int)$page:1;
$list_num = 20;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q = $db->query("SELECT b_img,s_img FROM `{$tablepre}certi` 
                 $search_sql 
                 ORDER BY uid DESC 
                 LIMIT $from_record,$list_num");
while($rtl = $db->fetch_array($q))
{
    $certi[] = $rtl;
}
$db->free_result();
$page_list = $rowset->link("certi.php?page=");

include template('certi');
footer();