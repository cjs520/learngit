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
 * $Id: wx_jsapi.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
if(!$is_mobile || !strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')) exit('ERR: you\'re not in weixin browser');

$param=dhtmlchars($param);
$arr_param=explode('_',$param);
$sn=$arr_param[0];
$amount=floatval($arr_param[1]);
if(!$sn) exit('ERR: sn error');
if($amount<=0)
{
    exit('ERR: amount error '.(isset($_GET['code'])?$_GET['code']:'before code'));
}

$payment=$db->get_one("SELECT cfg FROM `{$tablepre}payment_table` WHERE class_name='wxpay' AND supplier_id=0 LIMIT 1");
if(!$payment) exit('ERR:no weixin payment installed');

require_once 'include/payment/wxpay.class.php';
$o_payment=new wxpay(unserialize($payment['cfg']));

$jsApi = new JsApi_pub();
if (!isset($_GET['code']))
{
    //触发微信返回code码
    $url = $jsApi->createOauthUrlForCode("{$mm_mall_url}/wx_jsapi.php?param=$param");
    move_page($url);
}else
{
    //获取code码，以获取openid
    $code = $_GET['code'];
    $jsApi->setCode($code);
    $openid = $jsApi->getOpenId();
}

$unifiedOrder = new UnifiedOrder_pub();
$unifiedOrder->setParameter("openid","$openid");
$unifiedOrder->setParameter("body",$sn);
$unifiedOrder->setParameter("out_trade_no","$sn"); 
$unifiedOrder->setParameter("total_fee",$amount*100);
$unifiedOrder->setParameter("notify_url",WxPayConf_pub::$NOTIFY_URL); 
$unifiedOrder->setParameter("trade_type","JSAPI");

$prepay_id = $unifiedOrder->getPrepayId();
$jsApi->setPrepayId($prepay_id);
$jsApiParameters = $jsApi->getParameters();

$amount_fmt=currency($amount);
include template('wx_jsapi');
footer();
?>