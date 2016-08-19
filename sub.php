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
 * $Id: sub.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require 'header.php';
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';

$action=(int)$action;
$cat_list = $db->get_one("SELECT uid,category_id,category_name FROM `{$tablepre}category`  
                          WHERE uid = '$action' AND supplier_id='0' 
                          LIMIT 1");
!$cat_list && show_msg('检索不到指定的分类');
if($cat_list['category_id']!=0) move_page(GetBaseUrl('category',$cat_list['uid']));


//顶部类别
$sub_top_cat=array();
$cache_file="data/cache/sub_top_{$action}.php";
$sub_top_cat=$cache->read_cache($cache_file,'get_top_category',array('cat_uid'=>$action),'sub_top_cat');

//新品速递
$new_goods=array();
$cache_file="data/cache/sub_new_goods_{$action}.php";
$new_goods=$cache->read_cache($cache_file,'get_new_goods',array('cat_uid'=>$action),'new_goods');

//底部类别
$cat_sub_bottom=array();
$cache_file="data/cache/sub_bottom_{$action}.php";
$cat_sub_bottom=$cache->read_cache($cache_file,'get_bottom_category',array('cat_uid'=>$action),'cat_sub_bottom');

//轮转广告数据
$arr_btitle=array();
$arr_bpic=array();
$arr_burl=array();
foreach ($AD->GetAd('banner',2,$action) as $val)
{
    $arr_btitle[]=$val['title'];
    $arr_bpic[]=$val['pic'];
    $arr_burl[]=$val['url'];
}
$btitles=implode('|',$arr_btitle);
$bpics=implode('|',$arr_bpic);
$burls=implode('|',$arr_burl);

$mm_mall_title = $cat_list['category_name'];

require_once template('sub');
footer();


function get_top_category($param)
{
    global $db,$tablepre;
    $sub_top_cat=array();
    
    do
    {
        $p_uids=array();
        $q=$db->query("SELECT uid,category_name,category_file1 
                       FROM `{$tablepre}category` 
                       WHERE category_id='$param[cat_uid]' AND supplier_id='0' ORDER BY category_rank LIMIT 7");
        while($rtl=$db->fetch_array($q))
        {
	        $rtl['url']=GetBaseUrl('category',$rtl['uid']);
	        if(!$rtl['category_file1'] || !file_exists($rtl['category_file1'])) $rtl['category_file1']='images/noimages/no_cat.png';
	        $p_uids[]=(int)$rtl['uid'];
            $sub_top_cat[$rtl['uid']]=$rtl;
            
            $q2=$db->query("SELECT uid,category_name FROM `{$tablepre}category` WHERE category_id='$rtl[uid]' AND supplier_id='0' ORDER BY category_rank LIMIT 6");
            while ($rtl2=$db->fetch_array($q2))
            {
                $sub_top_cat[$rtl['uid']]['child'][]=array(
                    0=>$rtl2['category_name'],
                    'url'=>GetBaseUrl('category',$rtl2['uid'])
                );
            }
            $db->free_result(1);
        }
        $db->free_result(1);
    }while(0);
    
    return $sub_top_cat;
}

function get_bottom_category($param)
{
    global $db,$tablepre,$AD;
    $q=$db->query("SELECT uid,category_name FROM `{$tablepre}category` 
                   WHERE category_id='$param[cat_uid]' AND supplier_id='0' 
                   ORDER BY category_rank 
                   LIMIT 3");
    $i=1;
    while($rtl=$db->fetch_array($q))
    {
	    //类别的链接
	    $rtl['url']=GetBaseUrl('category',$rtl['uid']);
	    //子分类
	    $c_cat=array();
	    $cat_children=get_children($rtl['uid'],$uid_2_pid,$cat);
	    for($j=0;$j<5 && $cat_children[$j];$j++)
	    {
	        $c_cat[]=array(
	            'uid'=>$cat_children[$j][0],
	            'category_name'=>$cat_children[$j][1],
	            'url'=>GetBaseUrl('category',$cat_children[$j][0])
	        );
	    }
	    $rtl['children']=$c_cat;
	    
	   //广告1
	    $rtl['cat_left_ad']=$AD->GetAd("cat_left_ad$i",2,$param['cat_uid']);
	    //广告2
	    $rtl['cat_right_ad']=$AD->GetAd("cat_right_ad$i",2,$param['cat_uid']);
	    //广告3
	    $rtl['cat_news']=$AD->GetAd("cat_news$i",2,$param['cat_uid']);
	
	    $i++;
	    $cat_sub_bottom[]=$rtl;
    }
    $db->free_result();
    
    return $cat_sub_bottom;
}

function get_new_goods($param)
{
    global $db,$tablepre,$uid_2_pid,$cat;
    
    $children_uids=get_chidldren_uids($param['cat_uid'],$uid_2_pid,$cat);
    $children_uids[]=(int)$param['cat_uid'];
    $cat_in=implode(',',$children_uids);
    $q=$db->query("SELECT gt.uid,gt.supplier_id,gt.goods_name,gt.goods_file1,gt.goods_sale_price,gt.goods_status FROM `{$tablepre}goods_table` gt  
                   JOIN `{$tablepre}member_shop` ms 
                   ON gt.supplier_id=ms.m_uid 
                   WHERE goods_category IN ($cat_in) AND ms.isSupplier>1 
                   ORDER BY uid DESC 
                   LIMIT 48");
    while($rtl=$db->fetch_array($q)) $new_goods[]=goods_array($rtl);
    $db->free_result();
    
    return $new_goods;
}