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
 * $Id: province.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');
require_once 'include/common.inc.php';
require 'header.php';

$mm_mall_title='找商铺';

//热门地区
$hot_area_file='data/malldata/hot_area.php';
if(file_exists($hot_area_file)) include $hot_area_file;
else $arr_hot_area=array();

//普通地区
$arr_rtl=array();
for($c=ord('A');$c<=ord('Z');$c++) $arr_rtl[chr($c)]=array();

$q=$db->query("SELECT uid,cls,province,city,county FROM `{$tablepre}search_area` FORCE INDEX (`cls`) ORDER BY cls,od");
while ($rtl=$db->fetch_array($q))
{
    if(!isset($arr_rtl[$rtl['cls']])) continue;
    $rtl['area']=$rtl['province'];
    if($rtl['city']) $rtl['area']=$rtl['city'];
    if($rtl['county']) $rtl['area']=$rtl['county'];
    $rtl['url']='shop.php?province_s='.urlencode($rtl['province']).'&city_s='.urlencode($rtl['city']).'&county_s='.urlencode($rtl['county']);

    $arr_rtl[$rtl['cls']][]=$rtl;
}
$db->free_result();
foreach ($arr_rtl as $key=>$val)
{
    if(!$val) unset($arr_rtl[$key]);
}

include template('province');
footer();

