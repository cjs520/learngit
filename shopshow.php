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
 * $Id: shopshow.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';

require 'header.php';
$sid=(int)$sid;
$shop=$db->get_one("SELECT uid,member_id,member_name FROM `{$tablepre}member_table` WHERE uid='$sid' LIMIT 1");
$shop_detail=$db->get_one("SELECT video_code,promote_pic,supplier_notice,shop_name,supplier_cat,lat,lng,map_title,map_tip,province,city,county,shop_address,run_product,
                                  certified_type,supplier_notice,shop_level 
                           FROM `{$tablepre}member_shop` 
                           WHERE m_uid='$sid' 
                           LIMIT 1");
if(!$shop_detail) show_msg('检索不到您指定的商铺');

if($_POST && (int)$step==1)
{
	$environment=trim($environment);
	$service=trim($service);
	$product=trim($product);
	$price=trim($price);
	$msg=trim($msg);
	$environment=='' && show_msg('请选择环境条件');
	$service=='' && show_msg('请选择服务条件');
	$product=='' && show_msg('请选择产品条件');
	$price=='' && show_msg('请选择价格条件');
	$msg=='' && show_msg('请填写您的评价留言');
	if($mm_code_use==1)
	{
	    require_once 'include/captcha.class.php';
	    $Captcha= new Captcha();
	    !$Captcha->CheckCode($code) && show_msg('code_wrong');
	}
	
	$msg=dhtmlchars($msg);
	$select_evaluat="$environment|$service|$product|$price";
	$row=array(
	    'sid'=>$sid,
	    'username'=>$m_check_id?$m_check_id:'匿名',
	    'msg'=>$msg,
	    'select_evaluat'=>$select_evaluat,
	    'reg_time'=>$m_now_time
	);
	$db->insert("`{$tablepre}ss_comment`",$row);
	show_msg('评价成功',"shopshow.php?sid=$sid");
}

$shop=array_merge($shop,$shop_detail);
if(!trim($shop['video_code'])) $shop['video_code']='<embed src="http://player.youku.com/player.php/sid/XMjYwNjU3MzUy/v.swf" quality="high" width="480" height="400" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed>';
$config=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$sid' AND cf_name='mm_tel'");
$shop['ceo_phone']=$config['cf_value'];
if(!$shop['promote_pic'] || !file_exists($shop['promote_pic'])) $shop['promote_pic']='images/noimages/nopromote.jpg';

$category=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$shop[supplier_cat]' LIMIT 1");
$shop_cat_name=$category['category_name'];

$shop_url=GetBaseUrl('index','','',$sid);

$certified_url=GetBaseUrl('page','certification','action');

$detail_url=GetBaseUrl('page','shopdesc','action',$sid);
$rtl=$db->get_one("SELECT page_body FROM `{$tablepre}page_table` WHERE supplier_id='$sid' AND page_name='shopdesc'");
$rtl && $detail=$rtl['page_body'];
$detail=strip_tags(str_replace('/upload/image','union/upload/image',$detail),'<p>');

//QQ,WW
$rtl=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$sid' AND cf_name='mm_client_qq1' LIMIT 1");
$shop_qq1="<a target='_blank' href='http://wpa.qq.com/msgrd?v=3&uin=$rtl[cf_value]&site=qq&menu=yes'><img src='http://wpa.qq.com/pa?p=2:$rtl[cf_value]:52' alt='' title=''></a> ";
$rtl=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$sid' AND cf_name='mm_client_qq2' LIMIT 1");
$shop_qq2="<a target='_blank' href='http://wpa.qq.com/msgrd?v=3&uin=$rtl[cf_value]&site=qq&menu=yes'><img src='http://wpa.qq.com/pa?p=2:$rtl[cf_value]:52' alt='' title=''></a> ";
$rtl=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$sid' AND cf_name='mm_client_ww' LIMIT 1");
$shop_ww="<a target=\"_blank\" href=\"http://amos.im.alisoft.com/msg.aw?v=2&uid=$rtl[cf_value]&site=cntaobao&s=2&charset=utf-8\" ><img border=\"0\" src=\"http://amos.im.alisoft.com/online.aw?v=2&uid=$rtl[cf_value]&site=cntaobao&s=2&charset=utf-8\" alt=\"点击这里给我发消息\" /></a>";


//点评列表
$rtl=$db->get_one("SELECT COUNT(uid) AS cnt FROM `{$tablepre}ss_comment` WHERE sid='$sid'");
$comment_count=(int)$rtl['cnt'];
$comment_list=array();
$q=$db->query("SELECT uid,username,msg,select_evaluat,reply,reg_time 
               FROM `{$tablepre}ss_comment` WHERE sid='$sid' 
               ORDER BY reg_time DESC LIMIT 8");
while($rtl=$db->fetch_array($q))
{
	$rtl['reg_time']=date('Y-m-d H:i:s',$rtl['reg_time']);
	$arr_tmp=explode('|',$rtl['select_evaluat']);
	$rtl['environment']=$arr_tmp[0];
	$rtl['service']=$arr_tmp[1];
	$rtl['product']=$arr_tmp[2];
	$rtl['price']=$arr_tmp[3];
	!$rtl['reply'] && $rtl['reply']='暂无回复';
	$comment_list[]=$rtl;
}
$db->free_result();

//企业证书
$arr_certi=array();
$q=$db->query("SELECT s_img,b_img FROM `{$tablepre}certi` WHERE supplier_id='$sid' LIMIT 4");
while ($rtl=$db->fetch_array($q))
{
    $rtl['s_img']=ProcImgPath($rtl['s_img']);
    $rtl['b_img']=ProcImgPath($rtl['b_img']);
    $arr_certi[]=$rtl;
}
$db->free_result();

//类似商铺
$similar_shop=array();
$q=$db->query("SELECT m_uid,up_logo,shop_name FROM `{$tablepre}member_shop` WHERE shop_level='$shop[shop_level]' ORDER BY register_date DESC LIMIT 16");
while ($rtl=$db->fetch_array($q))
{
    $rtl['url']=GetBaseUrl('index','','',$rtl['m_uid']);
    $rtl['up_logo']=ProcImgPath($rtl['up_logo'],'logo');
    $similar_shop[]=$rtl;
}
$db->free_result();

//二维码
include 'qrcode/func.php';
$qrcode_img=create_qrcode(md5($shop['member_id']),$shop_url);

$mm_mall_title="$shop[shop_name],商家展示";

require_once template('shopshow');
footer();
