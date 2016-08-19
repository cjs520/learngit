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
 * $Id: settings.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
//require_once 'include/pic.class.php';
$shop=$db->get_one("SELECT run_product,province,city,county,shop_address,shop_level,up_logo,shop_name 
                    FROM `{$tablepre}member_shop` WHERE m_uid='$page_member_id' LIMIT 1");
$shop_file=array_merge($shop_file,$shop);
$shop_banner=$db->get_one("SELECT banner_file1 FROM `{$tablepre}banner_table` WHERE supplier_id='$page_member_id' AND banner_point='shop_banner' LIMIT 1");

$up_logo='<img src="'.ProcImgPath($shop_file['up_logo'],'logo').'" border="0" />';
$shop_banner_img='<img src="'.ProcImgPath($shop_banner['banner_file1'],'banner').'" border="0" width="500" />';
$wx_logo='<img src="'.ProcImgPath($ucfg['mm_wx_logo'],'wx').'" border="0" />';
$wx_res_img='<img src="'.ProcImgPath($ucfg['mm_wx_res_img']).'" border="0" />';
$mm_wx_shop_logo='<img src="'.ProcImgPath($ucfg['mm_wx_shop_logo']).'" border="0" />';

if ($_POST && (int)$step==1)
{
	
    //短信设置
    if ($type=='sms_set' || $type=='all')
    {
	    !$config['mm_sms_order'] ? $config['mm_sms_order'] = 0:(int)$config['mm_sms_order'];
	    !$config['mm_sms_receipt'] ? $config['mm_sms_receipt'] = 0:(int)$config['mm_sms_receipt'];
	    !$config['mm_sms_delivery'] ? $config['mm_sms_delivery'] = 0:(int)$config['mm_sms_delivery'];
	    !$config['mm_sms_comment'] ? $config['mm_sms_comment'] = 0:(int)$config['mm_sms_comment'];
	    !$config['mm_sms_group_order'] ? $config['mm_sms_group_order'] = 0:(int)$config['mm_sms_group_order'];
	    !$config['mm_sms_order_pay_to_shop'] ? $config['mm_sms_order_pay_to_shop'] = 0:(int)$config['mm_sms_order_pay_to_shop'];
	    !$config['mm_sms_order_stock_up_shop'] ? $config['mm_sms_order_stock_up_shop'] = 0:(int)$config['mm_sms_order_stock_up_shop'];
    }
    
    //伪静态如果变更后，更新导航条缓存
    $config['rewrite'] !==$rewrite && $rewrite=$config['rewrite'];
    $cache->delete('nav',$page_member_id);
    $cache->delete('nav2',$page_member_id);
    
    /**
    if ($_FILES['up_wx_logo']['name']!='')
    {
        require_once 'include/upfile.class.php';
        file_unlink(ProcImgPath($ucfg['mm_wx_logo']));
        $rowset = new upfile('gif,jpg,png,bmp','union/upload/logo/');
		$config['mm_wx_logo'] = $rowset->upload('up_wx_logo');
		$config['mm_wx_logo']=pic::PicZoomWithWH($config['mm_wx_logo'],250);
    }
    
    if ($_FILES['up_wx_res_img']['name']!='')
    {
        require_once 'include/upfile.class.php';
        file_unlink(ProcImgPath($ucfg['mm_wx_res_img']));
        $rowset = new upfile('jpeg,jpg,png','union/upload/logo/');
		$config['mm_wx_res_img'] = $rowset->upload('up_wx_res_img');
    }
    
    if ($_FILES['mm_wx_shop_logo']['name']!='')
    {
        require_once 'include/upfile.class.php';
        file_unlink(ProcImgPath($ucfg['mm_wx_shop_logo']));
        $rowset = new upfile('jpeg,jpg,png','union/upload/logo/');
		$config['mm_wx_shop_logo'] = $rowset->upload('mm_wx_shop_logo');
    }
    */
    foreach ($config as $key => $value)
    {
        $db->query("REPLACE INTO `{$tablepre}config` (cf_name,cf_value,supplier_id) VALUES ('$key','$value','$page_member_id')");
    }
    
    if(!is_array($post_shop)) $post_shop=array();
    $post_shop=dhtmlchars($post_shop);
    if(isset($config['mm_mall_title'])) $post_shop['shop_name']=$config['mm_mall_title'];
    if(isset($config['mm_description'])) $post_shop['shop_intro']=$config['mm_description'];

    if ($_FILES['up_logo']['name']!='')
    {
        require_once 'include/upfile.class.php';
      
        $shop_file['up_logo']=str_replace('@!web_log','',$shop_file['up_logo']);
        file_unlink('union/'.$shop_file['up_logo'],'bucket');
        $rowset = new upfile('gif,jpg,png,bmp','union/upload/logo/');
		$up_logo_text = $rowset->upload('up_logo');
		$post_shop['up_logo']= $up_logo_text.'@!web_log';
    }
    
    if ($_FILES['shop_banner']['name']!='')
    {
        require_once 'include/upfile.class.php';
        file_unlink('union/'.$shop_banner['banner_file1'],'bucket');
        $rowset = new upfile('gif,jpg,png,bmp','union/images/banner/');
		$shop_banner_text = $rowset->upload('shop_banner');
		$shop_banner_text=str_replace('union/','',$shop_banner_text);
		$start_date=$m_now_time;
		$end_date=$m_now_time+24*3600*90;
		$db->query("DELETE FROM `{$tablepre}banner_table` WHERE supplier_id='$page_member_id' AND banner_point='shop_banner'");
		$db->query("INSERT INTO `{$tablepre}banner_table` (banner_point,banner_subject,banner_width,banner_height,banner_file1,supplier_id) 
		            VALUES ('shop_banner','shop_banner','990','125','$shop_banner_text','$page_member_id')");
    }
    
    $post_shop['shop_step']=$shop_file['shop_step'] | 1;
    $db->update("`{$tablepre}member_shop`",$post_shop,"m_uid='$page_member_id'");
    $cache->flush(false);
    show_msg('编辑成功',"sadmin.php?module=$module&action=$action&type=$type"); 
}


//短信
if ($type=='sms_set' || $type=='all')
{
    drop_check($ucfg['mm_sms_order'],'mm_sms_order');
    drop_check($ucfg['mm_sms_receipt'],'mm_sms_receipt');
    drop_check($ucfg['mm_sms_delivery'],'mm_sms_delivery');
    drop_check($ucfg['mm_sms_comment'],'mm_sms_comment');
    drop_check($ucfg['mm_sms_group_order'],'mm_sms_group_order');
    drop_check($ucfg['mm_sms_order_pay_to_shop'],'mm_sms_order_pay_to_shop');
    drop_check($ucfg['mm_sms_order_stock_up_shop'],'mm_sms_order_stock_up_shop');

}
if($type=='env_set' || $type=='all')
{
    $ucfg['mm_close_cess']==0?$mm_close_cess_n='checked':$mm_close_cess_y='checked';
    $ucfg['mm_close']==0?$mm_close_n='checked':$mm_close_y='checked';
    
    $tpl=$db->get_one("SELECT b_img,tpl_name FROM `{$tablepre}tpl` WHERE tpl_code='$ucfg[mm_skin_name]' AND sellshow='$shop_file[sellshow]' LIMIT 1");
    $b_img=$tpl['b_img'];
    if(!$b_img || !file_exists($b_img)) $b_img='images/noimages/noproduct.jpg';
    
   	drop_check($ucfg['wap_user_index'],'wap_user_index');
   	
   	//收款设置
   	$member_account=$db->get_one("SELECT * FROM `{$tablepre}member_account` WHERE member_uid='$page_member_id' LIMIT 1");
   	if($member_account)
   	{
   	    $account_type=$member_account['type']==0?'<span class="span_left">支付宝</span>':'<span class="span_left">银行卡</span>';
   	}
   	
}

$rtl=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE cf_name='mm_shop_sms' AND supplier_id='$page_member_id' LIMIT 1");
$mm_shop_sms=(int)$rtl['cf_value'];
include template('sadmin_settings');

function drop_check($var,$out)
{
	global ${$out.'_y'},${$out.'_n'};
	if($var) ${$out.'_y'}='checked'; else ${$out.'_n'}='checked';
}