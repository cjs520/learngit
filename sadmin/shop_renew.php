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
 * $Id: shop_renew.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

if($action=='list')
{
    $shop_expire=date('Y-m-j',$shop_file['shop_expire']);
    $shop_level=$lang['shop_level'][$shop_file['shop_level']];
    $mvm_member['member_money']=currency($mvm_member['member_money']);
    
    if($shop_file['shop_level']>0)
    {
        $renew_price=currency($update_money[$shop_file['shop_level']]-$update_money[0]);
    }
    else
    {
        $arr_renew_level=array();
        for($i=1;$i<sizeof($lang['shop_level']);$i++)
        {
            $arr_renew_level[$i]=array($lang['shop_level'][$i],currency($update_money[$i]-$update_money[0]));
        }
    }
    
    $not_allow_list=array('integral','COD');
    $payment= $cache->get_cache('payment');
    foreach ($payment as $key=>$val)
        if (in_array($val['class_name'],$not_allow_list)) unset($payment[$key]);
    
	include template('sadmin_renew');
}
else if($action=='add')
{
    $arr_rtl=array('err'=>'','form_code'=>'');
    
    do
    {
        $shop_level=(int)$renew_level;
    	$pay_id=(int)$pay_id;
    	$advance=(int)$advance;
    	$pay_pass=md5($pay_pass);
    	$year=(int)$year;
    	
    	if($shop_level<=0 || $shop_expire>=sizeof($lang['shop_level']))
    	{
    	    $arr_rtl['err']='您选择的续费等级错误';
    	    break;
    	}
    	
    	if($year<=0)
    	{
    	    $arr_rtl['err']='您选择的续费时间错误';
    	    break;
    	}
    	
    	if($advance==1)
    	{
    	    $m=$db->get_one("SELECT pay_pass FROM `{$tablepre}member_table` WHERE uid='$page_member_id' LIMIT 1");
    	    if($m['pay_pass']!=$pay_pass)
    	    {
    	        $arr_rtl['err']='您的支付密码错误';
    	        break;
    	    }
    	}
    	
    	$total_money=$total_price=floatval($update_money[$shop_level]-$update_money[0])*$year;
    	if($total_price<=0)
    	{
    	    $arr_rtl['err']='升级金额小于0，升级失败';
    	    break;
    	}
    	
    	$ordersn="RN".date('YmdHis').rand(10,99);
    	$salt=rand(1000,9999);
    	
    	$pay = $db->get_one("SELECT * FROM `{$tablepre}payment_table` WHERE id='$pay_id' AND supplier_id='0'");
        if(!$pay)
        {
            $arr_rtl['err']='支付方式错误，请重新选择';
            break;
        }
        if(!file_exists('include/payment/'. $pay['class_name'].'.class.php'))
        {
            $arr_rtl['err']='指定的支付方式不存在';
            break;
        }
    	
    	$code_form='';
    	
    	require_once 'include/payment/'. $pay['class_name'].'.class.php';
    	$o_payment = new $pay['class_name'](unserialize($pay['cfg']));
    	require_once 'include/order.class.php';
    	
    	if($advance==1)
    	{
    	    if($mvm_member['member_money']<$total_price)
    	    {
    	        $total_price-=$mvm_member['member_money'];
    	        $pay_form=$o_payment->pay_send($ordersn.$salt,$total_price);
    	    }
    	    else
    	    {
    	        $pay_form="<a href='respond.php?sn=$ordersn'><img src='images/pay/yufu.gif' /></a>";
    	    }
    	}
    	else $pay_form=$o_payment->pay_send($ordersn.$salt,$total_price);
    	order::create_pay_log($ordersn,$salt,$total_price,$total_money,"$year|$shop_level|$page_member_id");
    	
    	$arr_rtl['form_code']=$pay_form;
    }while (0);
    
    exit(json_encode($arr_rtl));
}