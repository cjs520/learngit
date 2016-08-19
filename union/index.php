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
 * $Id: index.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';

if(!$is_mobile)
{
    //弹窗广告
    $cycle_path="data/malldata/cycle_$page_member_id.data.php";
    include $cycle_path;
    if($popup) $popup['body']=stripslashes($popup['body']);

    //轮转广告
    list($flash_img,$img_small,$flash_title,$flash_link)=get_flash('index');
    $pics=$flash_img;
    $title=$flash_title;
    $links=$flash_link;

    //友情链接
    $friend_links = $cache->get_cache('links',$page_member_id);
    //自定义区域
    $def_mod=$db->get_one("SELECT content FROM `{$tablepre}def_mod` WHERE m_uid='$page_member_id' AND type='0' LIMIT 1");
    $def_mod['content']=stripslashes($def_mod['content']);
    if(!$def_mod['content'])    //读取默认自定义代码
    {
        $custom_code_file=$shop_file['sellshow']==1?'templates/':'show_templates/';
        $custom_code_file.="$mm_skin_name/custom_code.php";
        if(file_exists($custom_code_file)) $def_mod['content']=file_get_contents($custom_code_file);
    }
    $def_goods=$db->get_one("SELECT content FROM `{$tablepre}def_mod` WHERE m_uid='$page_member_id' AND type='1' LIMIT 1");
    if(!$def_goods['content']) $def_goods['content']='0';
    $arr_goods=array();
    $q=$db->query("SELECT uid,supplier_id,goods_name,goods_file1,goods_status,type,goods_hit,goods_sale_price
                   FROM `{$goods_table}` 
                   WHERE uid IN ($def_goods[content]) AND supplier_id='$page_member_id' 
                   LIMIT 6");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl=goods_array($rtl);
        $arr_goods[]=$rtl;
    }
    $db->free_result();
}


$arr_certi=array();
if($shop_file['sellshow']==2 && !$is_mobile)
{
    $q=$db->query("SELECT s_img,b_img FROM `{$tablepre}certi` WHERE supplier_id='$page_member_id' ORDER BY uid DESC LIMIT 8");
    while ($rtl=$db->fetch_array($q))
    {
        $arr_certi[]=$rtl;
    }
    $db->free_result();
    
    $detail_url=GetBaseUrl('page','shopdesc','action');
    $rtl=$db->get_one("SELECT page_body FROM `{$tablepre}page_table` WHERE supplier_id='$page_member_id' AND page_name='shopdesc'");
    $rtl && $detail=$rtl['page_body'];
    $detail=strip_tags(str_replace('/upload/image','union/upload/image',$detail),'<p>,<br>');
}

$mm_mall_title='首页';
include template('index');
footer();

function GetGoodsByCat($cat_uid,$num=4)
{
    global $db,$goods_table,$page_member_id;
    $arr=array();
    $num=(int)$num;
    $num<=0 && $num=4;
    $str_cat=implode(',',get_category_chidlren($cat_uid));
    $q=$db->query("SELECT uid,supplier_id,goods_name,goods_file1,goods_status,type,goods_hit,goods_sale_price 
                   FROM `{$goods_table}` 
                   WHERE supplier_id='$page_member_id' AND supplier_cat IN ($str_cat) 
                   ORDER BY register_date DESC 
                   LIMIT $num");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl=goods_array($rtl);
        $arr[]=$rtl;
    }
    
    return $arr;
}

function get_flash($script_name)
{
	global $db,$tablepre,$page_member_id;
	$q=$db->query("SELECT title,link,img,img_small FROM `{$tablepre}cycle` WHERE supplier_id='$page_member_id' ORDER BY cat_tag DESC");
	$arr_img=array();
	$arr_img_small=array();
	$arr_title=array();
	$arr_link=array();
	while($rtl=$db->fetch_array($q))
	{
		$arr_img[]=ProcImgPath($rtl['img']);
		$arr_title[]=$rtl['title'];
		$arr_link[]=$rtl['link'];
		$arr_img_small[]=ProcImgPath($rtl['img_small']);
	}
	$db->free_result();
	$flash_img=implode('|',$arr_img);
	$flash_title=implode('|',$arr_title);
	$flash_link=implode('|',$arr_link);
	$img_small=implode('|',$arr_img_small);
	return array($flash_img,$img_small,$flash_title,$flash_link);
}