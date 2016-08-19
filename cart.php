<?php

/**
 * MVM_MALL 网上商店系统  购物车
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-12 $
 * $Id: cart.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
require_once 'include/cart.class.php';
$cart = new cart();

if($action=='simple_info')
{
    $info=$cart->get_simple_info();
    exit(json_encode($info));
}
else if($action=='simple_list')
{
    $list=$cart->cart_simple_list(4);
    exit(json_encode($list));
}
else if($action=='cart_list')
{
    $list=$cart->cart_list();
    exit(json_encode($list));
}
else if($action=='del')
{
    $uid=(int)$uid;
    exit($cart->del($uid)?'OK':'ERR');
}
else if($action=='to_fav')
{
    if(!$m_check_id) exit('ERR');
    
    $uid=(int)$uid;
    $cart_row=$cart->del($uid,true);
    if(!$cart_row) exit('ERR');
    $db->query("REPLACE INTO `{$tablepre}favorite` (m_uid,f_uid,t,module,goods_table) 
                VALUES ('$m_check_uid','$cart_row[g_uid]','1','$cart_row[module]','$cart_row[goods_table]')");
    exit('OK');
}
else if($action=='to_cart')
{
    $goods_table=dhtmlchars($gt);
    $module=dhtmlchars($module);
    $uid=(int)$uid;
    $ps_num=(int)$ps_num;
    $ps_num<=0 && $ps_num=1;
    $attr=dhtmlchars($attr);
    
    $rtl=$db->get_one("SELECT type FROM `$goods_table` WHERE uid='$uid' LIMIT 1");
    $db->query("DELETE FROM `{$tablepre}favorite` WHERE t='1' AND m_uid='$m_check_uid' AND f_uid='$uid' AND goods_table='$goods_table'");
    $db->free_result();
    if(!$rtl) exit('ERR:检索不到指定的商品');
    $add_rtl=false;
    if($rtl['type']==2) $add_rtl=$cart->add_bat_cart($uid,$goods_table,array($ps_num),array($attr),$module);
    else $add_rtl=$cart->add($uid,$goods_table,$ps_num,$attr,$module);
    
    exit($add_rtl?'OK':'ERR:'.$cart->get_last_error());
}
else if($action=='change_num')
{
    $uid=(int)$uid;
    $num=(int)$num;
    $arr_info=array('num'=>$num,'err'=>'');
    $arr_info['num']=$cart->change_num($uid,$num);
    if($cart->get_last_error()) $arr_info['err']=$cart->get_last_error();
    $arr_info=array_merge($arr_info,$cart->get_simple_info());
    
    exit(json_encode($arr_info));
}
else if($action=='buy')
{
    if(!$_POST || (int)$_POST['step']!=1) move_page(GetBaseUrl('cart','list'));
    $str_cart_uids=dhtmlchars($_POST['cart_uids']);
    if(!$str_cart_uids) move_page(GetBaseUrl('cart','list'));
    if(!$m_check_id) move_page(GetBaseUrl('cart','list'));
    
    $cart_list=$cart->cart_spec_list($str_cart_uids);
    if(!$cart_list) move_page(GetBaseUrl('cart','list'));
    //var_export($cart_list);exit;
    $arr_supplier_id=array_keys($cart_list['cart_list']);
    $arr_cess=array();
    foreach ($arr_supplier_id as $val)
    {
        $rtl=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE cf_name='mm_close_cess' AND supplier_id='$val' LIMIT 1");
        $arr_cess[$val]=(int)$rtl['cf_value']==1?"style='display:none;'":'';
    }
    
    require 'header.php';
	require template('cart_buy');
	footer();
}
else if($action=='pay')
{
    require 'include/order.class.php';
    $order=new order();
    $order->set_address_info($province,$city,$county,$address,$consignee,$mobile,$zipcode);
    $order->set_cart_info($cart_uid,$memo,$invoice,$ship_uid,$ship_price,$coupon_uid);
    $order->set_payment_info($advance,$pay_pass,$pay_id);
    $rtl=$order->cart_to_order();
    $err='';
    if(!$rtl) $err=$order->get_last_error();
    exit(json_encode(array('err'=>$err,'form_code'=>$order->form_code)));
}
else if($action=='list')
{
    $cart_list=$cart->cart_list();
	require 'header.php';
	require template('cart_list');
	footer();
}