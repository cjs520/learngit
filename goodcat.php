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
 * $Id: goodcat.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require 'header.php';
require_once 'include/cat_func.func.php';
//获取分类缓存数据
$cat=array();
$cat_config_file='data/malldata/category.config.php';
if(file_exists($cat_config_file)) include $cat_config_file;

$arr_cat=array();
foreach ($cat[0]['child'] as $key=>$val)
{
    $arr=cat_bucket_level($key,$cat);
    $arr_cat[]=array(
        'uid'=>$val['data']['uid'],
        'category_name'=>$val['data']['category_name'],
        'child'=>$arr
    );
}

$mm_mall_title='商品分类';

require_once template('goodcat');
footer();


