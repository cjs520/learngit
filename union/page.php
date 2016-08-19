<?php

/**
 * MVM_MALL 网上商店系统  一般页面
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-05-12 $
 * $Id: page.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
!$action && show_msg('page_code');
$action = dhtmlchars($action);
$page = ShowPage($action);
$mm_mall_title = "$page[title] -- $mm_mall_title";    //标题
$page['page_key'] && $mm_keywords = $page['page_key'];    //关键字
$page['page_desc'] && $mm_description = $page['page_desc'];    //描述
$navigation = $page['title'];

require 'header.php';
require_once template('page');
footer();

function ShowPage($ps_pname='page_company')
{
	global $page_member_id,$db,$tablepre,$m_check_rank;
    $rtl = $db->get_one("SELECT uid,page_subject,page_body,page_key,page_desc 
                         FROM `{$tablepre}page_table` 
                         WHERE page_name = '$ps_pname' AND `supplier_id`='$page_member_id' 
                         LIMIT 1");
    if(!$rtl) return array();
    
    $page['conter'] = stripslashes($rtl['page_body']);
    $page['title'] = $rtl['page_subject'];
    $page['page_key'] = $rtl['page_key'];
    $page['page_desc'] = $rtl['page_desc'];
    
    return $page;
}