<?php

/**
 * MVM_MALL 网上商店系统 显示文章
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2009-03-27 $
 * $Id: article.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';

$id=(int)$id;
$article=$db->get_one("SELECT * FROM `{$tablepre}bmain` WHERE uid='$id' AND supplier_id='$page_member_id' LIMIT 1");
if(!$article) show_msg('检索不到指定的资讯');

$board=$db->get_one("SELECT board_name_code,board_title FROM `{$tablepre}badmin_table` WHERE supplier_id='$page_member_id' AND board_name_code='$article[ps_name]' LIMIT 1");
if(!$board) show_msg('检索不到指指定的资讯版块');

$db->query("UPDATE `{$tablepre}bmain` SET board_hit=board_hit+1 WHERE uid='$article[uid]'");
$db->free_result();
$article['register_date']=date('Y-m-d H:i',$article['register_date']);
$article['board_body']=stripslashes($article['board_body']);

$mm_mall_title = $article['board_subject']; //标题
$mm_keywords = $article['board_subject']; //关键字

//取得上下篇文章 by dxd
$sibling_art=GetSiblingArticle($article['uid'],$article['ps_name']);

require 'header.php';
require_once template('article');
footer();

function GetSiblingArticle($art_id,$art_mod)
{
	global $db,$tablepre,$page_member_id;
	$searchSql=" AND supplier_id='$page_member_id' AND ps_name='$art_mod'";
	
	$rtl=$db->get_one("SELECT uid,ps_name,board_subject FROM `{$tablepre}bmain` FORCE INDEX (`supplier_id_2`) WHERE uid<'$art_id' $searchSql ORDER BY register_date DESC LIMIT 1");
    $prev_art=$rtl?"article.php?action=$art_mod&id=$rtl[uid]":'#';
    $prev_art_name=$rtl?$rtl['board_subject']:'没有了';
    $rtl=$db->get_one("SELECT uid,ps_name,board_subject FROM `{$tablepre}bmain` FORCE INDEX (`supplier_id_2`) WHERE uid>'$art_id' $searchSql ORDER BY register_date ASC LIMIT 1");
    $next_art=$rtl?"article.php?action=$art_mod&id=$rtl[uid]":'#';
    $next_art_name=$rtl?$rtl['board_subject']:'没有了';
    
    return array('prev'=>array('link'=>$prev_art,'art_name'=>$prev_art_name),
                 'next'=>array('link'=>$next_art,'art_name'=>$next_art_name));
}