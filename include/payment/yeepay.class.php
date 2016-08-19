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

$payment['yeepay']['name'] = '易宝支付';    //插件的代码必须和文件名保持一致
$payment['yeepay']['desc'] = '易宝支付介绍';    //描述
$payment['yeepay']['reg'] = 'https://www.yeepay.com/selfservice/registPage.action';    //申请地址
$payment['yeepay']['license'] = '<a href="https://www.yeepay.com/selfservice/registPage.action" target="_blank">免费签约</a>';    //版权信息
/*接口需要的参数 */
$payment['yeepay']['cfg'] =array(
    array('name' => 'p1_MerId', 'value' => '','label'=>'商户编号'),
    array('name' => 'keyValue', 'value' => '','label'=>'易宝密钥'),
);

class yeepay
{
    var $cfg;
    var $p0_Cmd = 'Buy';    //扣款请求
    var $p4_Cur = 'CNY';    //货币符号
    var $p9_SAF = '0';    //送货信息 0：不需要 1:需要
    
    function yeepay($cfg = array())
    {
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    
    function pay_send($sn,$amount)    //提交支付请求，sn订单号，$amount支付价格
	{
	    global $mm_mall_url,$sessionID;
	    $p8_Url = "$mm_mall_url/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php');
		$sNewString=$this->getReqHmacString($sn,$amount,$p8_Url);	
		$result="<form name=\"yeepay\" action='https://www.yeepay.com/app-merchant-proxy/node' method='post' target='_blank'>
                 <input type='hidden' name='p0_Cmd'  value='$this->p0_Cmd'>
                 <input type='hidden' name='p1_MerId'  value='{$this->cfg[p1_MerId]}'>
                 <input type=\"hidden\" name=\"p2_Order\"  value='$sn'>
                 <input type='hidden' name='p3_Amt'  value='$amount'>
                 <input type='hidden' name='p4_Cur'  value='$this->p4_Cur'>
                 <input type='hidden' name='p5_Pid'    value=''>
                 <input type='hidden' name='p6_Pcat'   value=''>
                 <input type='hidden' name='p7_Pdesc'  value=''>
                 <input type='hidden' name='p8_Url'  value='$p8_Url'>
                 <input type='hidden' name='p9_SAF'  value='0'>
                 <input type='hidden' name='pa_MP'  value=''>
                 <input type='hidden' name='pd_FrpId'  value=''>
                 <input type='hidden' name='pr_NeedResponse'  value=''>
                 <input type='hidden' name='hmac'  value='$sNewString'>
                 <input type='image' name='PayBtnUrl' src='images/pay/yeepay.gif'>
                 </form>";
		return $result;
	}
	
    function pay_receive()    //提交返回处理
    {
        global $m_check_uid;
    	$sNewString = $this->getCallBackValue();
        $sn = trim($_REQUEST['r6_Order']);    //本系统订单号
        $succeed = trim($_REQUEST['r1_Code']);    //获取交易结果,1成功,-1失败
        $svrHmac = trim($_REQUEST['hmac']);
        $amount = trim($_REQUEST['r3_Amt']);    //获取订单金额
	    if (strtoupper($svrHmac)!=strtoupper($sNewString) || $succeed != '1') show_msg('订单返回数据校验失败','./');
	    
	    require_once 'include/order.class.php';
        $salt=substr($sn,strlen($sn)-4,4);
        $sn=substr($sn,0,strlen($sn)-4);
        if(order::check_pay_log($sn,$salt,$amount)) add_money($m_check_uid,$amount,'预付款充值','易宝支付'.$sn.'充值',$sn);
        
        order_prepare($sn);
        change_order($sn);
    }
 

    function getReqHmacString($orderId,$amount,$p8_Url)    //加密字符串 
    {
    	//进行加密串处理，一定按照下列顺序进行
        //取得加密前的字符串
        $productId='';
        $productCAT='';
        $productDESC='';
        $mct_properties='';
        $sbOld = $this->p0_Cmd . $this->cfg[p1_MerId] . $orderId . $amount . $this->p4_Cur . $productId . $productCAT. $productDESC . $p8_Url . $this->p9_SAF . $mct_properties;
        return $this->hmac($this->cfg[keyValue],$sbOld);
    }
    
    function getCallBackValue()    //解析返回字符串
    {
        $date  = $this->cfg[p1_MerId] . trim($_REQUEST['r0_Cmd']) . trim($_REQUEST['r1_Code']) . trim($_REQUEST['r2_TrxId']) . trim($_REQUEST['r3_Amt']) . 
                 trim($_REQUEST['r4_Cur']) . trim($_REQUEST['r5_Pid']) . trim($_REQUEST['r6_Order']) . trim($_REQUEST['r7_Uid']) . trim($_REQUEST['r8_MP']) . 
                 trim($_REQUEST['r9_BType']);
        return $this->hmac($this->cfg[keyValue],$date);   
    }

    function hmac($key, $data)
    {
    	// RFC 2104 HMAC implementation for php.
        // Creates an md5 HMAC.
        // Eliminates the need to install mhash to compute a HMAC
        // Hacked by Lance Rushing(NOTE: Hacked means written)
        //需要配置环境支持iconv，否则中文参数不能正常处理
        $key = iconv("GB2312","UTF-8",$key);
        $data = iconv("GB2312","UTF-8",$data);
        $b = 64; // byte length for md5
        if (strlen($key) > $b) $key = pack("H*",md5($key));
        
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;
        return md5($k_opad . pack("H*",md5($k_ipad . $data)));
    }
}