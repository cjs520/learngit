<?php

/**
 * MVM_MALL 网上商店系统  品牌
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-27 $
 * $Id: topics.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once MVMMALL_ROOT.'header.php';

$topic=(int)$topic;
if(!in_array($topic,array(1,2,3,4,5))) $topic=1;

//使用专题
$rtl=$db->get_one("SELECT data FROM `{$tablepre}onsale_page` WHERE type='topic_use' AND topic=0 LIMIT 1");
$arr_topics=array();
if($rtl['data']) $arr_topics=explode(',',$rtl['data']);
if(!in_array($topic,$arr_topics)) show_msg('您指定的专题未开放，请联系管理员');

//各专题按钮
$str_topics=implode(',',$arr_topics);
$arr_top_btn=array();
$q=$db->query("SELECT topic,data FROM `{$tablepre}onsale_page` WHERE topic IN ($str_topics) AND type='top_btn'");
while ($rtl=$db->fetch_array($q))
{
    $arr_top_btn[$rtl['topic']]=$rtl['data'];
}
$db->free_result();
foreach ($arr_topics as $val)
{
	$arr_top_btn[$val]=IMG_URL.$arr_top_btn[$val];
    if(!$arr_top_btn[$val] || !@fopen($arr_top_btn[$val],'r')) $arr_top_btn[$val]='images/noimages/noproduct.jpg';
}


$onsale_data=array();
$q=$db->query("SELECT * FROM `{$tablepre}onsale_page` WHERE topic='$topic'");
while($rtl=$db->fetch_array($q))
{
	$onsale_data[$rtl['type']][$rtl['level']]=$rtl;
}
$db->free_result();
$onsale_data['top_ad'][0]['data']=IMG_URL.$onsale_data['top_ad'][0]['data'];
$onsale_data['top_btn'][0]['data']=IMG_URL.$onsale_data['top_btn'][0]['data'];
$onsale_data['top_btn_hl'][0]['data']=IMG_URL.$onsale_data['top_btn_hl'][0]['data'];
	
//背景颜色
if(!$onsale_data['bk_color'][0]['data']) $onsale_data['bk_color'][0]['data']='#ffffff';

//楼层使用
$arr_level_in_use=explode(',',$onsale_data['level_use'][0]['data']);


$mm_mall_title='专题活动';
include template('topics');
footer();
