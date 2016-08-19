<?php

/**
 * MVM_MALL 网上商店系统 收汇宝接口
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-04-29 $
 * $Id: ctopay.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL')) exit('Access Denied');


$payment['ctopay']['name'] = '收汇宝';    //插件的代码必须和文件名保持一致
$payment['ctopay']['desc'] = '收汇宝';    //描述
$payment['ctopay']['reg'] = 'http://www.ctopay.com/';    //申请地址
$payment['ctopay']['license'] = 'www.mvmmall.cn';    //版权信息
/*接口需要的参数*/
$payment['ctopay']['cfg'] =array(
    array('name' => 'MerNo', 'value' => '','label'=>'商户号'),
    array('name' => 'MD5key', 'value' => '','label'=>'MD5私钥'),
);

class ctopay
{
    var $cfg;
    
    function ctopay($cfg = array())
    {
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    /*提交支付请求*/
    function pay_send($sn,$amount)	
	{
	    global $main_settings,$sessionID;
        $MD5key = $this->cfg['MD5key'];    //MD5私钥
        $MerNo = $this->cfg['MerNo'];    //商户号
        $BillNo = $sn;    //订单号
        $Currency = '2';    //币种
        $Amount = $amount;    //金额
        $DispAmount = $amount;    //外币金额
        $Language = '1';    //语言
        $ReturnURL = "$main_settings[mm_mall_url]/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php');    //返回地址
        $Remark = '';    //备注
        $md5src = $MerNo.$BillNo.$Currency.$Amount.$Language.$ReturnURL.$MD5key;    //校验源字符串
        $MD5info = strtoupper(md5($md5src));    //MD5检验结果
	    $result="<form name=re METHOD=post ACTION='http://payment.ctopay.com/payment/Interface' target='_blank'>
				 <input type='hidden' name='MerNo' value='$MerNo'>
				 <input type='hidden' name='Currency' value='$Currency'>
				 <input type='hidden' name='BillNo' value='$BillNo'>
				 <input type='hidden' name='Amount'  value='$Amount'>
				 <input type='hidden' name='DispAmount' value='$DispAmount'>
				 <input type='hidden' name='ReturnURL' value='$ReturnURL'>
				 <input type='hidden' name='Language' value='$Language'>
				 <input type='hidden' name='MD5info' value='$MD5info'>
				 <input type='hidden' name='Remark' value='$Remark'>
				 <input type='submit' value='联银通支付平台'>
				 </form>";
	    return $result;
	}
    
    function pay_receive()    //提交返回处理
    {
    	$BillNo = $_POST['BillNo'];    //订单号
    	$Currency = $_POST['Currency'];    //币种
    	$BankID = $_POST['BankID'];    //银行ID号
    	$Amount = $_POST['Amount'];    //金额
    	$Succeed = $_POST['Succeed'];    //支付状态
    	$TradeNo = $_POST['TradeNo'];    //支付平台流水号
    	$Result = $_POST['Result'];    //支付结果
    	$MD5info = $_POST['MD5info'];    //取得的MD5校验信息
    	$Remark = $_POST['Remark'];    //备注
    	$Drawee = $_POST['Drawee'];    //支付人名称

    	$MD5key =  $this->cfg['MD5key'];    //MD5私钥
    	$md5src = $BillNo.$Currency.$Amount.$Succeed.$MD5key;    //校验源字符串
    	$md5sign = strtoupper(md5($md5src));    //MD5检验结果
	
        if ($MD5info==$md5sign)
        {
            if ($Succeed == '1')
            {
                $BillNo= strip_tags($BillNo);
                $list = order_info($BillNo);
                if ($list['order_amount'] == $Amount)
                {
                    change_order($BillNo);
                    return true;
                }
            }
        }
        return false;
    }
}