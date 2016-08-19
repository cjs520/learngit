<?php
/**
 * MVM_MALL 网上商店系统
 * ============================================================================
 * 版权所有 (C) 2007-2010 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * ============================================================================
 * 
 * @author :     www.mvmmall.cn <admin@mvmmall.cn> 
 * @version :    v4.X
---------------------------------------------
 */
if(!defined('MVMMALL')) exit('Access Denied');

//插件的代码必须和文件名保持一致
$payment['tenpay']['name'] = '财付通';

//描述
$payment['tenpay']['desc'] = '财付通';

//申请地址
$payment['tenpay']['reg'] = 'http://union.tenpay.com/mch/mch_register.shtml?posid=123&actid=84&opid=50&whoid=31&sp_suggestuser=1202036501';

//版权信息
$payment['tenpay']['license'] = '<a href="http://union.tenpay.com/mch/mch_register.shtml?posid=123&actid=84&opid=50&whoid=31&sp_suggestuser=1202036501" target="_blank">携手财付通 免费签约</a>';

//接口需要的参数
$payment['tenpay']['cfg'] =array(
    array('name' => 'v_mid', 'value' => '','label'=>'财付通账号'),
    array('name' => 'v_key', 'value' => '','label'=>'财付通密钥'),
);


require_once dirname(__FILE__).'/tenpay_classes/PayRequestHandler.class.php';
require_once dirname(__FILE__).'/tenpay_classes/PayResponseHandler.class.php';

class tenpay
{
    var $cfg;
    
    function tenpay($cfg = array())
    {
        foreach ($cfg as $key=>$val)
        {
            $this->cfg[$val['name']] = $val['value'];
        }
    }
    
    function pay_send($sn,$amount)	
	{
	    global $main_settings,$sessionID;
	    $bargainor_id = $this->cfg['v_mid'];
	    $key = $this->cfg['v_key'];
	    
        $return_url = "{$main_settings[mm_mall_url]}/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php');
        $strDate = date("Ymd");
        $strTime = date("His");

        $randNum = rand(1000, 9999);
        $strReq = $strTime . $randNum;

        /* 商家订单号,长度若超过32位，取前32位。财付通只记录商家订单号，不保证唯一。 */
        $sp_billno = $sn;
        /* 财付通交易单号，规则为：10位商户号+8位时间（YYYYmmdd)+10位流水号 */
        $transaction_id = $bargainor_id . $strDate . $strReq;
        /* 商品价格（包含运费），以分为单位 */
        $total_fee = $amount*100;
        /* 商品名称 */
        $desc = mb_convert_encoding("订单号：$transaction_id",'GBK','UTF8');
        
        /* 创建支付请求对象 */
        $reqHandler = new PayRequestHandler();
        $reqHandler->init();
        $reqHandler->setKey($key);
        $reqHandler->setParameter("bargainor_id", $bargainor_id);			//商户号
        $reqHandler->setParameter("sp_billno", $sp_billno);					//商户订单号
        $reqHandler->setParameter("transaction_id", $transaction_id);		//财付通交易单号
        $reqHandler->setParameter("total_fee", $total_fee);					//商品总金额,以分为单位
        $reqHandler->setParameter("return_url", $return_url);				//返回处理地址
        $reqHandler->setParameter("desc", $desc);	//商品名称
        $reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);
        $result = $reqHandler->getRequestURL();
	    
		$result='<a href="'.trim($result).'" target="_blank"><img src="images/pay/tenpay.gif" /></a>';
		return $result;
	}
	
    function pay_receive()
    {
        $bargainor_id = $this->cfg['v_mid'];
	    $key = $this->cfg['v_key'];
	    
	    /* 创建支付应答对象 */
	    $resHandler = new PayResponseHandler();
	    $resHandler->setKey($key);
	    //判断签名
	    if($resHandler->isTenpaySign())
	    {
	        $sp_billno = $resHandler->getParameter("sp_billno");    //商户订单号
	        $total_fee = $resHandler->getParameter("total_fee");    //金额,以分为单位
	        $pay_result = $resHandler->getParameter("pay_result");    //支付结果

	        if( "0" == $pay_result )
	        {
	            //处理业务开始
                $list = order_info($sp_billno);
                if (round($list['order_amount'],2) == round($total_fee/100,2))
                {
            	    change_order($sp_billno);
                    return true;
                }
	            //调用doShow, 打印meta值跟js代码,告诉财付通处理成功,并在用户浏览器显示$show页面.
	            $resHandler->doShow('/');
	            return true;
	        }
	        return false;
	    }
	    return false;
    }
}