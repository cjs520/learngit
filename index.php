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
require_once 'header.php';

if(!$is_mobile)
{
    //弹窗广告
    $cycle_path='data/malldata/cycle.data.php';
    if(file_exists($cycle_path)) include $cycle_path;

    $friend_links = $cache->get_cache('links');    //友情链接
    
    //flash
    list($flash_img,$img_small,$flash_title,$flash_link)=get_flash('index');
    $pics=$flash_img;
    $title=$flash_title;
    $links=$flash_link;
    
    //微信扫我二维码
    if($mm_wx_app_id && $mm_wx_app_secret && !$is_mobile)
    {
        require_once 'include/mvm_oauth.class.php';
        require_once 'qrcode/func.php';
        $wx_oauth=new mvm_oauth(array('app_id'=>$mm_wx_app_id,'app_secret'=>$mm_wx_app_secret),OAUTH_WX);
        $wx_login_url=$wx_oauth->get_login_url();
        $wx_login_qrcode=create_qrcode('wx_login_qrcode',$wx_login_url);
    }

}

include 'data/malldata/index_floor.config.php';

//判断签到
$is_sign=false;
if($m_check_uid && $is_mobile)
{
    $rtl=$db->get_one("SELECT m_uid FROM `{$tablepre}member_sign` WHERE m_uid='$m_check_uid' AND register_date='$today_timestamp' LIMIT 1");
    if($rtl) $is_sign=true;
}

include template('index');
footer();

function get_flash($script_name)
{
	global $db,$tablepre;
	$q=$db->query("SELECT title,link,img,img_small FROM `{$tablepre}cycle` WHERE supplier_id='0' ORDER BY cat_tag DESC");
	$arr_img=array();
	$arr_img_small=array();
	$arr_title=array();
	$arr_link=array();
	while($rtl=$db->fetch_array($q))
	{
		$arr_img[]=IMG_URL.$rtl['img'];
		$arr_title[]=$rtl['title'];
		$arr_link[]=$rtl['link'];
		$arr_img_small[]=IMG_URL.$rtl['img_small'];
	}
	$flash_img=implode('|',$arr_img);
	$flash_title=implode('|',$arr_title);
	$flash_link=implode('|',$arr_link);
	$img_small=implode('|',$arr_img_small);
	return array($flash_img,$img_small,$flash_title,$flash_link);
}