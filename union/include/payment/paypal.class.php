<?php

/**
 * MVM_MALL 网上商店系统  贝宝支付
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-25 $
 * $Id: cart.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL')) exit('Access Denied');

$payment['paypal']['name'] = 'paypal';    //插件的代码必须和文件名保持一致
$payment['paypal']['desc'] = 'paypal';    //描述
$payment['paypal']['reg'] = 'https://www.paypal.com/us/cgi-bin/webscr?cmd=_registration-run';    //申请地址
$payment['paypal']['license'] = '<a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_registration-run" target="_blank">免费签约</a>';    //版权信息
//接口需要的参数
$payment['paypal']['cfg'] =array(
    array('name' => 'business', 'value' => '','label'=>'商户帐号'),
    array('name' => 'currency_code', 'value' => 'USD','label'=>'支付货币','type'=>'1'),
);
    
class paypal
{
    var $cfg;
    function paypal($cfg = array())
    {
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    
    function pay_send($sn,$amount)    //提交支付请求
    {
        global $main_settings,$sessionID;
    	$notify_url = "$main_settings[mm_mall_url]/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php');
        $invoice= time();
        $result="<form  name='re' method='post' action='https://www.paypal.com/cgi-bin/webscr' target='_blank'>
			     <input type='hidden' name='cmd' value='_xclick'>
			     <input type='hidden' name='business' value='{$this->cfg[business]}'>
			     <input type='hidden' name='return' value='$PHP_SELF'>
			     <input type='hidden' name='amount' value='$amount'>
			     <input type='hidden' name='invoice' value='$invoice'>
			     <input type='hidden' name='charset' value='utf-8'>
			     <input type='hidden' name='no_shipping' value='1'>
			     <input type='hidden' name='no_note' value=''>
			     <input type='hidden' name='currency_code' value='{$this->cfg[currency_code]}'>
			     <input type='hidden' name='notify_url' value='$notify_url'>
			     <input type='hidden' name='item_name' value='$sn'>
			     <input type='image' name='PayBtnUrl' src='images/pay/paypal.gif'>
			     </form>";
        return $result;
    }
    
    function pay_receive()    //提交返回处理
    {
    	$merchant_id = $this->cfg['business'];    ///获取商户编号
        //read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value)
        {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        //post back to PayPal system to validate
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) ."\r\n\r\n";
        $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
        //assign posted variables to local variables
        $item_name = $_POST['item_name'];
        $item_number = $_POST['item_number'];
        $payment_status = $_POST['payment_status'];
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        $txn_id = $_POST['txn_id'];
        $receiver_email = $_POST['receiver_email'];
        $payer_email = $_POST['payer_email'];
        $order_sn = $_POST['invoice'];
        $action_note = $txn_id . '（paypal 交易号）' . $_POST['memo'];

        if (!$fp)
        {
            fclose($fp);
            return false;
        }
        fputs($fp, $header . $req);
        while (!feof($fp))
        {
        	$res = fgets($fp, 1024);
        	if (strcmp($res, 'INVALID') == 0)
            {
            	fclose($fp);
                return false;
            }
            else if (strcmp($res, 'VERIFIED') == 0)
            {
            	// check the payment_status is Completed
                if ($payment_status != 'Completed')
                {
                	fclose($fp);
                    return false;
                }
                // check that txn_id has not been previously processed
                // check that receiver_email is your Primary PayPal email
                if ($receiver_email != $merchant_id)
                {
                	fclose($fp);
                    return false;
                }
                // check that payment_amount/payment_currency are correct
                if ($payment_currency != '$data_pay_account')
                {
                	fclose($fp);
                	return false;
                }
                //比较返回的订单号和金额与数据库中的金额是否相符
                $v_oid= strip_tags($$item_name);
                $list = order_info($v_oid);
                if ($list['order_amount']==$payment_amount)
                {
                    change_order($v_oid);
                    fclose($fp);
                    return true;
                }
                else return false;
            }
        }
    }
}