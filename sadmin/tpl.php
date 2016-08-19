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
 * $Id: tpl.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
$shop=$db->get_one("SELECT allow_tpl FROM `{$tablepre}member_shop` WHERE m_uid='$page_member_id' LIMIT 1");
$shop_file=array_merge($shop_file,$shop);
$shop_file['member_money']=$mvm_member['member_money'];
$shop_file['uid']=$mvm_member['uid'];

if($action=='list')
{
    $mvm_member['member_money']=currency($mvm_member['member_money']);
    
    //所有模板
    $arr_all_tpl=array();
    $q=$db->query("SELECT * FROM `{$tablepre}tpl` WHERE sellshow='$shop_file[sellshow]' ORDER BY price DESC");
    while($rtl=$db->fetch_array($q))
    {
        if(!$rtl['s_img'] || !file_exists($rtl['s_img'])) $rtl['s_img']='images/noimages/noproduct.jpg';
        if(!$rtl['b_img'] || !file_exists($rtl['b_img'])) $rtl['b_img']='images/noimages/noproduct.jpg';
        if(!$rtl['tpl_name']) $rtl['tpl_name']='未命名';
        $rtl['fprice']=$rtl['price']>0?currency($rtl['price']):'免费';
        
        $arr_all_tpl[$rtl['tpl_code']]=$rtl;
    }
    
    //可用模板
    $arr_tpl_use=array();
    $arr_tpl_use_name=array('default');
    $arr_tmp=explode('|',$shop_file['allow_tpl']);
    foreach ($arr_tmp as $val)
    {
        $val=trim($val);
        if(!$val) continue;
        if(in_array($arr_tpl_use_name)) continue;
        $arr_tpl_use_name[]=$val;
    }
    $arr_tpl_use_name=array_unique($arr_tpl_use_name);
    foreach ($arr_tpl_use_name as $val)
    {
        if($arr_all_tpl[$val]) $arr_tpl_use[$val]=$arr_all_tpl[$val];
        unset($arr_all_tpl[$val]);
    }
    
    //还未购买模板
    $arr_unbuy_tpl=$arr_all_tpl;
    
    $not_allow_list=array('integral','COD');
	$payment_list= $cache->get_cache('payment');
	foreach ($payment_list as $key=>$val)
        if (in_array($val['class_name'],$not_allow_list)) unset($payment_list[$key]);
    
    include template('sadmin_tpl');
}
else if($action=='import_tpl')
{
    $v=dhtmlchars($_POST['v']);
    if(!$v) exit('ERROR:请指定正确的模板');
    
    $arr_tmp=explode('|',$shop_file['allow_tpl']);
    if($v!='default' && !in_array($v,$arr_tmp)) exit('ERROR:您无权使用该模板');
    
    $tpl=$db->get_one("SELECT * FROM `{$tablepre}tpl` WHERE tpl_code='$v' AND sellshow='$shop_file[sellshow]' LIMIT 1");
    if(!$tpl) exit('ERROR:检索不到您指定的模板');
    
    $db->query("DELETE FROM `{$tablepre}config` WHERE cf_name='mm_skin_name' AND supplier_id='$page_member_id'");
    $db->query("INSERT INTO `{$tablepre}config` SET 
                cf_name='mm_skin_name',
                cf_value='$tpl[tpl_code]',
                supplier_id='$page_member_id'");
    $db->free_result();
    $cache->delete('cfg',$page_member_id);
    exit('OK:模板导入成功，请及时到的商铺装修中更新广告图片');
}
else if($action=='buy_tpl' && $_POST)
{
    $v=dhtmlchars($_POST['v']);
    if(!$v) exit('ERR:请指定正确的模板');
    
    $arr_tmp=explode('|',$shop_file['allow_tpl']);
    if($v=='default' || in_array($v,$arr_tmp)) exit('ERR:您已经购买了该模板，无需重复购买');
    
    $tpl=$db->get_one("SELECT * FROM `{$tablepre}tpl` WHERE tpl_code='$v' AND sellshow='$shop_file[sellshow]' LIMIT 1");
    if(!$tpl) exit('ERR:检索不到您指定的模板');
    
    if($tpl['price']<=0)    //免费模板直接导入
    {
        $arr_tmp[]=$tpl['tpl_code'];
        foreach ($arr_tmp as $key=>$val)
        {
            $val=trim($val);
            if(!$val) unset($arr_tmp[$key]);
        }
        $arr_tmp=array_unique($arr_tmp);
        $str_tpl=implode('|',$arr_tmp);
        $db->query("UPDATE `{$tablepre}member_shop` SET 
                    allow_tpl='$str_tpl' 
                    WHERE m_uid='$page_member_id'");
        exit('OK：免费模板成功导入');
    }
    else
    {
        $ordersn='TP'.date('YmdHis');
        $pay_id = (int)$_POST['pay_id'];
        $advance=(int)$advance;
        $pay_pass=dhtmlchars($pay_pass);
        
        if($advance)
        {
            $m=$db->get_one("SELECT pay_pass FROM `{$tablepre}member_table` WHERE uid='$m_check_uid' LIMIT 1");
            if(md5($pay_pass)!=$m['pay_pass']) exit('ERR:支付密码错误');
        }
        
        $pay = $db->get_one("SELECT * FROM `{$tablepre}payment_table` WHERE id='$pay_id' AND supplier_id='0'");
		if(!$pay) exit('ERROR:请指定正确的付款方式');
		if(!file_exists('include/payment/'. $pay['class_name'].'.class.php')) exit('ERROR:指定的付款方式不存在');
		
		require_once 'include/payment/'. $pay['class_name'].'.class.php';
		require_once 'include/order.class.php';
		$total_money=$total_price=floatval($tpl['price']);
		$ordersn='TP'.date('YmdHis').rand(10,99);
		$salt=rand(1000,9999);
		
		
		//生成支付代码
        $pay_form='';
        $o_payment = new $pay['class_name'](unserialize($pay['cfg']));
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
        order::create_pay_log($ordersn,$salt,$total_price,$total_money,$tpl['tpl_code']);
		
		echo $pay_form;
		exit;
    }
}