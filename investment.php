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
 * $Id: investment.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require_once 'header.php';
if(file_exists('data/malldata/config.inc.php')) include 'data/malldata/config.inc.php';

if($_POST && (int)$step==1)    //开店
{
	$m_now_time-(int)$_SESSION['apply_time']<10*60 && show_msg('10分钟之内请不要重复提交您的申请');
	if(trim($form_shop_name)=='') show_msg('请填写商铺名称');
	if(trim($run_product)=='') show_msg('请填写经营商品');
	if((int)$supplier_cat==0) show_msg('请选择商铺的分类');
	if(trim($address1)=='') show_msg('请把联系地址填写完整');
	if(trim($name)=='') show_msg('请填写联系人姓名');
	if(trim($tel)=='') show_msg('请填写联系电话');
	if(strlen(trim($shop_desc))<30) show_msg('商铺描述不少于30个字');
	//取得上传文件
	if ($_FILES['up_logo']['name']!='')
	{
		require_once 'include/upfile.class.php';
		$f_logo = new upfile('jpg,jpeg,gif,bmp,png',"upload/logo/");
        $up_logo_file = $f_logo->upload('up_logo');
	}
	if ($_FILES['banner']['name']!='')
	{
		require_once 'include/upfile.class.php';
		$f_banner = new upfile('jpg,jpeg,gif,bmp,png',"upload/acc/");
        $banner_file = $f_banner->upload('banner');
	}
	
	$row=array(
	    'shop_name'=>trim($form_shop_name),
	    'sellshow'=>(int)$sellshow,
	    'run_product'=>trim($run_product),
	    'shop_cat'=>(int)$supplier_cat,
	    'address'=>trim($address1),
	    'tel'=>trim($tel),
	    'qq'=>trim($qq),
	    'name'=>trim($name),
	    'up_logo'=>$up_logo_file,
	    'logo_tip'=>trim($logo_tip),
	    'banner'=>$banner_file,
	    'banner_tip'=>trim($banner_tip),
	    'shop_desc'=>trim($shop_desc),
	    'msg'=>trim($msg),
	    'reg_time'=>$m_now_time
	);
	$db->insert("`{$tablepre}shop_apply`",$row);
	$_SESSION['apply_time']=$m_now_time;
	show_msg('提交成功，网站管理员会尽快与您联系','./');
}

if(!is_array($allow_upload_size)) $allow_upload_size=array();
foreach ($allow_upload_size as $key=>$val) $allow_upload_size[$key]=strval(round($val/1024/1024,2)).'MB';


$mm_mall_title='商家入驻申请';

include template('investment');
footer();