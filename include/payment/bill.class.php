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


$payment['bill']['name'] = '快钱';    //插件的代码必须和文件名保持一致
$payment['bill']['desc'] = '快钱支付';    //描述
$payment['bill']['reg'] = 'https://www.99bill.com/website/signup/websignup.htm';    //申请地址
$payment['bill']['license'] = 'www.mvmmall.cn';    //版权信息
//接口需要的参数
$payment['bill']['cfg'] =array(
    array('name' => 'merchant_id', 'value' => '','label'=>'商户编号'),
    array('name' => 'merchant_key', 'value' => '','label'=>'商户密钥'),
);
    
class bill
{
    var $cfg;
    
    function  bill($cfg = array())
    {
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    
    function pay_send($sn,$amount)    //提交支付请求
	{
	    global $mm_mall_url,$sessionID;
	    $merchant_id  = $this->cfg['merchant_id'];    //商户编号
	    $merchant_key = $this->cfg['merchant_key'];    //商户密钥
	    $orderid = $sn;    //订单编号
	    //$amount订单金额
	    $curr = '1';    //货币类型,1为人民币
	    $isSupportDES = '2';    //是否安全校验,2为必校验,推荐
	    $merchant_url = "$mm_mall_url/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php');    //支付结果返回地址
	    $pname = urlencode($GLOBALS['m_check_id']);    //支付人姓名
	    $commodity_info = $sn;    //商品信息
	    $merchant_param = '';    //商户私有参数
	    $pemail = '';    //传递email到快钱网关页面
	    $pid = '';    //代理/合作伙伴商户编号


	    ///生成加密串,注意顺序
	    $ScrtStr="merchant_id=".$merchant_id."&orderid=".$orderid."&amount=".$amount."&merchant_url=".$merchant_url."&merchant_key=".$merchant_key;
	    $mac = strtoupper(md5($ScrtStr));
		$result='=<form name="frm" method="post" action="https://www.99bill.com/webapp/receiveMerchantInfoAction.do" target="_blank">
                  <input name="merchant_id" type="hidden" value='.$merchant_id.'>
			      <input name="orderid"  type="hidden" value='.$orderid.'>
			      <input name="amount"  type="hidden" value='.$amount.'>
			      <input name="currency"  type="hidden" value='.$curr.'>
			      <input name="isSupportDES"  type="hidden" value='.$isSupportDES.'>
			      <input name="mac"  type="hidden" value='.$mac.'>

			      <input name="merchant_url"  type="hidden"  value='.$merchant_url.'>
			      <input name="pname"  type="hidden" value='.$pname.'>
			      <input name="commodity_info"  type="hidden"  value='.$commodity_info.'>
			      <input name="merchant_param" type="hidden"  value='.$merchant_param.'>
			      <input name="pemail" type="hidden"  value='.$pemail.'>
			      <input name="pid" type="hidden"  value='.$pid.'>
			      <input name="payby99bill"  type="image" src="images/pay/99bil.gif"  value="快钱支付">
                 </form>';
		$result=trim($result);
		return   $result;
	}
    
    function pay_receive()    //提交返回处理
    {
        global $m_check_uid;
        $merchant_key = $this->cfg['merchant_key'];    //商户密钥
        $merchant_id =trim($_REQUEST['merchant_id']);    //获取商户编号
        $orderid = trim($_REQUEST['orderid']);    //获取订单编号
        $amount = trim($_REQUEST['amount']);    //获取订单金额
        $dealdate = trim($_REQUEST['date']);    //获取交易日期
        $succeed = trim($_REQUEST['succeed']);    //获取交易结果,Y成功,N失败
        $mac = trim($_REQUEST['mac']);    //获取安全加密串
        $merchant_param = trim($_REQUEST['merchant_param']);    //获取商户私有参数
        $couponid = trim($_REQUEST['couponid']);    //获取优惠券编码
        $couponvalue = trim($_REQUEST['couponvalue']);    //获取优惠券面额
        //生成加密串,注意顺序
        $ScrtStr = "merchant_id=".$merchant_id."&orderid=".$orderid."&amount=".$amount."&date=".$dealdate."&succeed=".$succeed."&merchant_key=".$merchant_key;
        $mymac = md5($ScrtStr);
        
        if(strtoupper($mac)!=strtoupper($mymac)) show_msg('支付验证失败','./');
        if($succeed!='Y') show_msg('支付失败','./');
        
        $sn= strip_tags($orderid);
        require_once 'include/order.class.php';
        $salt=substr($sn,strlen($sn)-4,4);
        $sn=substr($sn,0,strlen($sn)-4);
        if(order::check_pay_log($sn,$salt,$amount)) add_money($m_check_uid,$amount,'预付款充值','快钱支付'.$sn.'充值',$sn);
        
        order_prepare($sn);
        change_order($sn);
    }
}