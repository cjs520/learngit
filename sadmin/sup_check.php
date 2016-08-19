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
 * $Id: sup_check.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/pic.class.php';
if($action=='list')
{
	$shop_info=$db->get_one("SELECT certified_type,up_licence,up_licence_thumb,up_id_card,xb_money 
                             FROM `{$tablepre}member_shop` 
                             WHERE m_uid='$page_member_id' 
                             LIMIT 1");
    if($shop_info) $shop_file=array_merge($shop_file,$shop_info);
    unset($shop_info);
	
	if($shop_file['up_id_card']) $id_card_img='<img src="'.ProcImgPath($shop_file['up_id_card']).'" width="229" />';
	if($shop_file['up_licence']) $licence_img='<img src="'.ProcImgPath($shop_file['up_licence']).'" width="229" />';
	
	$personal_certify='<span class="red">未认证</span>';
	$enterprice_certify='<span class="red">未认证</span>';
	if($shop_file['certified_type'] & 1) $personal_certify='已认证';
	if($shop_file['certified_type'] & 2) $enterprice_certify='已认证';
	if($shop_file['certified_type'] & 4) $cp_certify='已认证';
	
	$mm_member_money_txt=currency($mm_member_money);
	$shop_file['xb_money']=currency($shop_file['xb_money']);
	
	$not_allow_list=array('integral','COD');
	$payment_list= $cache->get_cache('payment');
	foreach ($payment_list as $key=>$val)
        if (in_array($val['class_name'],$not_allow_list)) unset($payment_list[$key]);
	
	include template('sadmin_sup_check');
}
else if($action=='edit')
{
    $shop=$db->get_one("SELECT up_licence,up_licence_thumb,up_id_card,shop_step 
	                    FROM `{$tablepre}member_shop` 
	                    WHERE m_uid='$page_member_id' LIMIT 1");
    if($_POST && (int)$step==1)
	{
	    if ($_FILES['up_licence']['name']!='')
	    {
	    	file_unlink('union/'.$shop['up_licence'],'bucket');
	        require_once 'include/upfile.class.php';
			$rowset = new upfile('gif,jpg,png','union/upload/licence/');
			$up_licence = $rowset->upload('up_licence');
			
			$big_licence=str_replace('union/','',$up_licence);
			$up_licence_thumb=$big_licence.'@!web_licence';
			if($up_licence_thumb==ProcImgPath($big_licence)) $up_licence_thumb=$big_licence;
			else $up_licence_thumb=str_replace('union/','',$up_licence_thumb);
			
			$shop['shop_step']=$shop['shop_step'] | 8;
			
			$db->query("UPDATE `{$tablepre}member_shop` SET 
			            up_licence='$big_licence',
			            up_licence_thumb='$up_licence_thumb',
			            shop_step='$shop[shop_step]' 
			            WHERE m_uid='$page_member_id'");
			$db->free_result();
		}
		show_msg('营业执照上传成功',"sadmin.php?module=$module&action=list");
	}
	if($_POST && (int)$step==2)
	{
	    if ($_FILES['up_id_card']['name']!='')
	    {
	    	file_unlink('union/'.$shop['up_id_card'],'bucket');
	        require_once 'include/upfile.class.php';
			$rowset = new upfile('gif,jpg,png','union/upload/id_card/');
			$up_id_card = $rowset->upload('up_id_card');
			$up_id_card=str_replace('union/','',$up_id_card);
			
			$shop['shop_step']=$shop['shop_step'] | 8;
			
			$db->query("UPDATE `{$tablepre}member_shop` SET 
			            up_id_card='$up_id_card',
			            shop_step='$shop[shop_step]' 
			            WHERE m_uid='$page_member_id'");
		}
		show_msg('身份证上传成功',"sadmin.php?module=$module&action=list");
	}
	if($_POST && (int)$step==3)
	{
	    $advance=(int)$advance==1?1:0;
	    $pay_pass=dhtmlchars($pay_pass);
	    $xb_money=floatval($xb_money);
		$pay_id = (int)$pay_id;
		
		$arr_rtl=array('succ'=>0,'err'=>'');
		do
		{
		    if($xb_money<=0 || $xb_money<floatval($mm_member_money))
		    {
		        $arr_rtl['err']='请的支付金额不付合要求';
		        break;
		    }
		    
		    if($advance)
		    {
		        if(!$pay_pass)
		        {
		            $arr_rtl['err']='请填写您的支付密码';
		            break;
		        }
		        $shop_info=$db->get_one("SELECT pay_pass FROM `{$tablepre}member_table` WHERE uid='$page_member_id' LIMIT 1");
		        if(!$shop_info)
		        {
		            $arr_rtl['err']='检索不到指定的商铺';
		            break;
		        }
		        if($shop_info['pay_pass']!=md5($pay_pass))
		        {
		            $arr_rtl['err']='您的支付密码错误';
		            break;
		        }
		    }
		    
		    $payment = $db->get_one("SELECT name,class_name,cfg FROM `{$tablepre}payment_table` WHERE id='$pay_id' AND supplier_id='0' LIMIT 1");
		    if(!$payment)
		    {
		        $arr_rtl['err']='检索不到指定的支付方式';
		        break;
		    }
		    
		    if(!file_exists('include/payment/'. $payment['class_name'].'.class.php'))
		    {
		        $arr_rtl['err']='指定的支付方式不存在';
		        break;
		    }
		    
		    require_once 'include/order.class.php';
		    require_once 'include/payment/'. $payment['class_name'].'.class.php';
		    
		    $o_payment=new $payment['class_name'](unserialize($payment['cfg']));
		    
		    $ordersn='XB'.date('YmdHis');
		    $salt=rand(1000,9999);
		    $pay_form='';
		    if($advance)
		    {
		        if($mvm_member['member_money']>=$xb_money)
		        {
		            order::create_pay_log($ordersn,$salt,0,$xb_money);
		            $pay_form="<a href='respond.php?sn=$ordersn'><img src='images/pay/yufu.gif' /></a>";
		        }
		        else
		        {
		            order::create_pay_log($ordersn,$salt,$xb_money-$mvm_member['member_money'],$xb_money);
		            $pay_form=$o_payment->pay_send($ordersn.$salt,$xb_money-$mvm_member['member_money']);
		        }
		    }
		    else
		    {
		        order::create_pay_log($ordersn,$salt,$xb_money,$xb_money);
		        $pay_form=$o_payment->pay_send($ordersn.$salt,$xb_money);
		    }
		    
		    $arr_rtl['code_form']='<div class="fct pay_way">'.
                                  '<p>结算金额：<strong class="red f14">'.currency($xb_money).'</strong></p>'.
                                  '<p class="mt10">'.$pay_form.'</p>'.
                                  '</div>';
		    $arr_rtl['succ']=1;
		}while (0);
		
        exit(json_encode($arr_rtl));
	}
}