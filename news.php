<?php

/**
 * MVM_MALL 网上商店系统  商城导航
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-02 $
 * $Id: sitemap.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once MVMMALL_ROOT . 'header.php';

$b_cat=array();
$q=$db->query("SELECT uid,board_name_code,board_title FROM `{$tablepre}badmin_table` 
               WHERE supplier_id='0' 
               ORDER BY od DESC");
while($rtl=$db->fetch_array($q))
{
    $rtl['url']=GetBaseUrl('board',$rtl['board_name_code']);
    $b_cat[$rtl['board_name_code']]=$rtl;
}

$arr_fashion=array();
$q = $db->query("SELECT uid,board_subject,register_date,author,ps_name,cover FROM `{$tablepre}bmain`
	             WHERE supplier_id='0' AND ps_name='fashion' 
	             ORDER BY od DESC 
	             LIMIT 2");
while ($rtl=$db->fetch_array($q))
{
	$rtl['cover']=IMG_URL.$rtl['cover'];
    if(!$rtl['cover']) $rtl['cover']='images/noimages/noproduct.jpg';
	$rtl['url'] = $rewrite==1 ? "article-$rtl[ps_name]-$rtl[uid].html":"article.php?action=$rtl[ps_name]&id=$rtl[uid]";
	$rtl['board_subject']= mb_substr($rtl['board_subject'],0,20,'UTF-8');
	$rtl['register_date'] = date('Y-m-d',$rtl['register_date']);
	$arr_fashion[]=$rtl;
}

$mm_mall_title='商城资讯';

require_once template('news');
footer();