<?php

/**
 * MVM_MALL 网上商店系统  商品设置管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: settings.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if (!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if ($_POST && (int)$step==1 && $action=='edit')
{
    //把不存在的数据库配置值写入数据库
    $query = $db->query("SELECT * FROM `{$tablepre}config` WHERE supplier_id='0'");
    while ($rt = $db->fetch_array($query))
    {
        $rt['cf_name'] = str_replace("'","\'",$rt['cf_name']);
        $rt_cf[$rt['cf_name']] = $rt['cf_value'];
    }
    //短信设置
    if ($type=='sms_set' || $type=='all')
    {
	    !$config['mm_sms_member'] ? $config['mm_sms_member'] = 0:(int)$config['mm_sms_member'];
	    !$config['mm_sms_order'] ? $config['mm_sms_order'] = 0:(int)$config['mm_sms_order'];
	    !$config['mm_sms_receipt'] ? $config['mm_sms_receipt'] = 0:(int)$config['mm_sms_receipt'];
	    !$config['mm_sms_delivery'] ? $config['mm_sms_delivery'] = 0:(int)$config['mm_sms_delivery'];
    }
    //邮件设置
    if ($type=='email_set' || $type=='all')
    {
	    !$config['mm_mail_order'] ? $config['mm_mail_order'] = 0:1;
	    !$config['mm_mail_receipt'] ? $config['mm_mail_receipt'] = 0:1;
	    !$config['mm_mail_delivery'] ? $config['mm_mail_delivery'] = 0:1;
	    !$config['mm_mail_shop_apply'] ? $config['mm_mail_shop_apply'] = 0:1;
	    !$config['mm_mail_shop_pass'] ? $config['mm_mail_shop_pass'] = 0:1;
	    !$config['mm_mail_comment'] ? $config['mm_mail_comment'] = 0:1;
	    !$config['mm_mail_shop_update'] ? $config['mm_mail_shop_update'] = 0:1;
    }
    if($type=='basis' || $type=='all')
    {
        if ($_FILES['mm_logo']['name']!='')
        {
            file_unlink(str_replace(IMG_URL, '', $mm_logo),'bucket');
            require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp',"upload/logo/");
            $config['mm_logo'] = $f->upload('mm_logo');
        }
        
        if ($_FILES['mm_wx_logo']['name']!='')
        {
            require_once 'include/pic.class.php';
            file_unlink(str_replace(IMG_URL, '', $mm_wx_logo),'bucket');
            require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp',"upload/logo/");
            $config['mm_wx_logo'] = $f->upload('mm_wx_logo');
            //$config['mm_wx_logo']=pic::PicZoomWithWH($config['mm_wx_logo'],250);
        }
    }
    if($type=='reg_set' || $type=='all')
    {
        if ($_FILES['mm_wx_focus_img']['name']!='')
        {
            file_unlink(str_replace(IMG_URL, '', $mm_wx_focus_img),'bucket');
            require_once 'include/upfile.class.php';
            $f = new upfile('jpg,png',"upload/logo/");
            $config['mm_wx_focus_img'] = $f->upload('mm_wx_focus_img');
        }
        
        if ($_FILES['mm_wx_shop_logo']['name']!='')
        {
        	
            file_unlink(str_replace(IMG_URL, '', $mm_wx_shop_logo),'bucket');
            require_once 'include/upfile.class.php';
            $f = new upfile('jpg,png',"upload/logo/");
            $config['mm_wx_shop_logo'] = $f->upload('mm_wx_shop_logo');
        }
        
        if ($_FILES['mm_wx_res_img']['name']!='')
        {
            file_unlink(str_replace(IMG_URL, '', $mm_wx_res_img),'bucket');
            require_once 'include/upfile.class.php';
            $f = new upfile('jpg,png',"upload/logo/");
            $config['mm_wx_res_img'] = $f->upload('mm_wx_res_img');
        }
    }
    //伪静态如果变更后，更新导航条缓存
    $config['rewrite'] !==$rewrite && $rewrite=$config['rewrite'];
    $cache->put_cache('nav');

    foreach ($config as $key => $value)
    {
        if ($rt_cf[$key]!=$value)
        {
            $cf_name = $db->get_one("SELECT cf_name FROM `{$tablepre}config` WHERE cf_name='$key' AND supplier_id='0' LIMIT 1");
            if ($cf_name) $db->query("UPDATE `{$tablepre}config` SET cf_value='$value' WHERE cf_name='$key' AND supplier_id='0'");
            else $db->query("INSERT INTO `{$tablepre}config` (cf_name,cf_value,supplier_id) VALUES ('$key','$value','0')");
        }
    }
    $cache->delete('cfg',0);
    admin_log("修改商城设置");
    
    show_msg('success',"admincp.php?module=$module&action=list&type=$type"); 
}

//基础设置
if ($type=='basis' || $type=='all')
{
    $rtl=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='0' AND cf_name='mm_skin_name' LIMIT 1");
   	$tplpath_menu = drop_menu(tpl_array('templates','','admincp'),'config[mm_skin_name]',$rtl['cf_value']);
   	
   	(int)$mm_subdomain==1?$mm_subdomain_y='checked':$mm_subdomain_n='checked';
   	if(!$mm_logo || !@fopen($mm_logo,'r')) $mm_logo='images/noimages/nologo.jpg';
    if(!$mm_wx_logo || !@fopen($mm_wx_logo,'r')) $mm_wx_logo='images/noimages/noproduct.jpg';
   	
   	drop_check($mm_obstart,'mm_obstart');
   	drop_check($rewrite,'rewrite');
   	drop_check($mm_rewrite,'mm_rewrite');
    drop_check($mm_close,'mm_close');
    drop_check($wap_user_index,'wap_user_index');
}

//注册设置
if ($type=='reg_set' || $type=='all')
{
    drop_check($mm_member_reg,'mm_member_reg');
    drop_check($mm_code_use,'mm_code_use');
    drop_check($mm_wx_event_type,'mm_wx_event_type');
    
    if(!$mm_wx_focus_img || !@fopen($mm_wx_focus_img,'r')) $mm_wx_focus_img='images/noimages/noproduct.jpg';
    if(!$mm_wx_res_img || !@fopen($mm_wx_res_img,'r')) $mm_wx_res_img='images/noimages/noproduct.jpg';
    if(!$mm_wx_shop_logo || !@fopen($mm_wx_shop_logo,'r')) $mm_wx_shop_logo='images/noimages/noproduct.jpg';
}
//积分设置
if ($type=='point_set' || $type=='all')
{
    drop_check($mm_point_use,'mm_point_use');
}
//短信
if ($type=='sms_set' || $type=='all')
{
    drop_check($mm_sms_use,'mm_sms_use');
    drop_check($mm_sms_useadmin,'mm_sms_useadmin');
    drop_check($mm_sms_member,'mm_sms_member');
    drop_check($mm_sms_order,'mm_sms_order');
    drop_check($mm_sms_receipt,'mm_sms_receipt');
    drop_check($mm_sms_delivery,'mm_sms_delivery');
}
//邮件
if ($type=='email_set' || $type=='all')
{
    drop_check($mm_mail_smtpauth,'mm_mail_smtpauth');
    drop_check($mm_mail_member,'mm_mail_member');
    drop_check($mm_mail_order,'mm_mail_order');
    drop_check($mm_mail_receipt,'mm_mail_receipt');
    drop_check($mm_mail_delivery,'mm_mail_delivery');
    drop_check($mm_mail_comment,'mm_mail_comment');
    $mm_mail_order_admin==1?$mm_mail_order_admin='checked':'';
    $mm_mail_shop_apply==1?$mm_mail_shop_apply='checked':'';
    $mm_mail_shop_pass==1?$mm_mail_shop_pass='checked':'';
    $mm_mail_shop_update==1?$mm_mail_shop_update='checked':'';
}

$total_sms_apply=0;
$q=$db->query("SELECT cf_value FROM `{$tablepre}config` WHERE cf_name='mm_shop_sms'");
while($rtl=$db->fetch_array($q)) $total_sms_apply+=(int)$rtl['cf_value'];

require_once template('settings');
footer();


function lang_arr()
{
    $m_mall_skin = get_dirinfo(MVMMALL_ROOT.'language/');
    for($i =0 ; $i<count($m_mall_skin) ; $i++)
       $arr[$m_mall_skin[$i]] = $m_mall_skin[$i];
    
    return $arr;
}

function drop_check($var,$out)
{
	global ${$out.'_y'},${$out.'_n'};
	if($var) ${$out.'_y'}='checked'; else ${$out.'_n'}='checked';
}