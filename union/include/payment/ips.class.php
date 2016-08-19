<?php
/**
 * MVM_MALL 网上商店系统
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 
 * @author :     www.mvmmall.cn <admin@mvmmall.cn> 
 * @version :    v4.X
---------------------------------------------
 */
if(!defined('MVMMALL')) exit('Access Denied');


$payment['ips']['name'] = '环迅支付';    //插件的代码必须和文件名保持一致
$payment['ips']['desc'] = '环迅支付';    //描述
$payment['ips']['reg'] = 'http://express.ips.com.cn/merchant/register.asp?agent=000263';    //申请地址
$payment['ips']['license'] = '<a href="http://express.ips.com.cn/merchant/register.asp?agent=000263" target="_blank">免费签约</a>';    //版权信息
//接口需要的参数
$payment['ips']['cfg'] =array(
    array('name' => 'Mer_code', 'value' => '','label'=>'商户号'),
    array('name' => 'strcert', 'value' => '','label'=>'证书'),
);
    
class ips
{
    var $cfg;
    var $Currency_Type='RMB';    //支付币种 RMB 人民币 HKD 港币 ADSL ADSL支付
    var $OrderEncodeType='2';    //订单支付加密方式 0 不加密 2 md5摘要
    var $RetEncodeType='12';    //交易返回加密方式 11 md5withRsa 12 md5摘要
    var $RetType='0';    //是否提供Server返回方式 0 无,1有
    var $serverurl='';    //Server返回地址
    var $Gateway_type='01';    //支付方式
    var $Lang='GB';    //语言
    
    function ips($cfg = array())
    {
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    
    function pay_send($sn,$amount)    //提交支付请求
	{
	    global $main_settings,$sessionID;
		$Merchanturl="$main_settings[mm_mall_url]/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php');    //商户返回地址
	    $datestr=date("Ymd",time());
	    $amount=number_format($amount,2,'.','');
	    /**'订单号后6位，订单号为12位：6位商户号+6位随机号，一天内不能重复
	    '此例子给了一个订单号生成算法，取系统当前时间做为订单号
	    '但我们建议商户自己建立一个sequence序列号来作为6位随机号**/
	    //$billstr=date("His",time());
	    //$mer_code="000015";    //测试商户号为000015
	    //$amount="0.02";    //测试金额默认为0.01
	    //$billno=$mer_code.$billstr;    //测试订单号
	    //$strcert="GDgLwwdK270Qj1w4xho8lyTpRQZV9Jm5x4NwWOTThUa4fMhEBK9jOXFrKRT6xhlJuU2FEa89ov0ryyjfJuuPkcGzO5CeVx5ZIrkkt1aBlZV36ySvHOMcNv8rncRiy3DQ";    //测试证书
	    //正式环境接口地址:http://pay.ips.com.cn/ipayment.aspx
	    $strcontent=$sn.$amount.$datestr."$this->Currency_Type".$this->cfg['strcert'];    //签名验证串
	    $signmd5=MD5($strcontent);
		$result="<form action='http://pay.ips.com.cn/ipayment.aspx' method='post' target='_blank'>
                 <input type='hidden' name='Mer_code' value='{$this->cfg[Mer_code]}'>
                 <input type='hidden' name='Billno' value='$sn'>
                 <input type='hidden' name='Gateway_type' value='{$this->Gateway_type}'>
                 <input type='hidden' name='Currency_Type'  value='{$this->Currency_Type}'>
                 <input type='hidden' name='Lang'  value='{$this->Lang}'>
                 <input type='hidden' name='Amount'  value='$amount'>
                 <input type='hidden' name='Date' value='$datestr'>
                 <input type='hidden' name='DispAmount' value=''>
                 <input type='hidden' name='OrderEncodeType' value='{$this->OrderEncodeType}'>
                 <input type='hidden' name='RetEncodeType' value='{$this->RetEncodeType}'>
                 <input type='hidden' name='Merchanturl' value='$Merchanturl'>
                 <input type='hidden' name='SignMD5' value='$signmd5'>
                 <input type='submit' value='环迅支付'>           
                 </form>";
		return $result;
	}

    function pay_receive()    //提交返回处理
    {
        $billno=trim($_REQUEST['billno']);    //订单号
        $amount=trim($_REQUEST['amount']);    //金额
        $mydate=trim($_REQUEST['date']);
        $succ=trim($_REQUEST['succ']);
        $msg=trim($_REQUEST['msg']);
        $attach=trim($_REQUEST['attach']);
        $ipsbillno=trim($_REQUEST['ipsbillno']);
        $retEncodeType=trim($_REQUEST['retencodetype']);
        $currency_type=trim($_REQUEST['Currency_type']);
        $signature=trim($_REQUEST['signature']);
        //----------------------------------------------------
        //  判断交易是否成功
        //  See the successful flag of this transaction
        //----------------------------------------------------
        if ($succ=='Y')
        {
            //'----------------------------------------------------
            //'   Md5摘要认证
            //'   verify  md5
            //'----------------------------------------------------
            $content=$billno . $amount . $mydate . $succ . $ipsbillno .$currency_type;
            /**请在该字段中放置商户登录merchant.ips.com.cn的网站中的证书 假设为**/
            $cert=$this->cfg[strcert];

            //Md5摘要认证
            if ($content == '' || $cert == '') $signature1 = '';
            else $signature_1ocal = md5($content.$cert);

            if ($signature_1ocal == $signature)    //签名正确
            {
                $list = order_info($billno);
                if ($list['order_amount'] == $amount)
                {
                    change_order($billno);
                    return true;
                }
            }
        }
        return false;
    }
}