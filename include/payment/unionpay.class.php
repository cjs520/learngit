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

$payment['unionpay']['name'] = '银联支付';    //插件的代码必须和文件名保持一致
$payment['unionpay']['desc'] = '银联支付';    //描述
//申请地址
$payment['unionpay']['reg'] = 'https://online.unionpay.com/';
$payment['unionpay']['license'] = 'www.mvmmall.cn';    //版权信息
//接口需要的参数
$payment['unionpay']['cfg'] =array(
    array('name' => 'mer_id', 'value' => '','label'=>'商户号'),
    array('name' => 'security_key', 'value' => '','label'=>'证书密码'),
    array('name' => 'unionpay_cfca.pfx', 'value' => 'unionpay_cfca.pfx', 'label'=>'签名证书(pfx)','t'=>'upload')
);

class unionpay
{
    var $cfg;
    function unionpay($cfg = array())
    {
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
        
        // 签名证书密码
        define(SDK_SIGN_CERT_PWD , $this->cfg['security_key']);
        
        include_once dirname(__FILE__) . '/unionpay_classes/common.php';
        include_once dirname(__FILE__) . '/unionpay_classes/SDKConfig.php';
        include_once dirname(__FILE__) . '/unionpay_classes/secureUtil.php';
        include_once dirname(__FILE__) . '/unionpay_classes/log.class.php';
    }
    
    function pay_send($sn,$amount)    //提交支付请求
    {
        global $mm_mall_url,$sessionID;
        
        $params = array(
		    'version' => '5.0.0',				//版本号
		    'encoding' => 'utf-8',				//编码方式
		    'certId' => getSignCertId (),			//证书ID
		    'txnType' => '01',				//交易类型	
		    'txnSubType' => '01',				//交易子类
	    	'bizType' => '000201',				//业务类型
		    'frontUrl' => "{$mm_mall_url}/member.php?action=order",  		//前台通知地址
		    'backUrl' => "{$mm_mall_url}/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php'),		//后台通知地址	
		    'signMethod' => '01',		//签名方法
		    'channelType' => '08',		//渠道类型，07-PC，08-手机
		    'accessType' => '0',		//接入类型
		    'merId' => $this->cfg['mer_id'],  //商户代码，请改自己的测试商户号
		    'orderId' => $sn,	//商户订单号
		    'txnTime' => date('YmdHis'),	//订单发送时间
		    'txnAmt' => $amount*100,		//交易金额，单位分
		    'currencyCode' => '156',	//交易币种
		    'defaultPayType' => '0001',	//默认支付方式	
		    //'orderDesc' => '订单描述',  //订单描述，网关支付和wap支付暂时不起作用
		    'reqReserved' => $sn, //透传信息，请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
		);
		
        unionpay_sign( $params );
        $front_uri = SDK_FRONT_TRANS_URL;
        $result = create_html ( $params, $front_uri );
        
        return $result;
    }
    
    function pay_receive()    //提交返回处理
    {
        global $m_check_uid;
        
        if(!verify($_POST)) show_msg('验签失败');
        
        //商户网站逻辑处理#
        $amount=round(floatval($_POST['txnAmt'])/100,2);
        $sn=dhtmlchars($_POST['orderId']);
        
        require_once 'include/order.class.php';
        $salt=substr($sn,strlen($sn)-4,4);
        $sn=substr($sn,0,strlen($sn)-4);
        if(order::check_pay_log($sn,$salt,$amount)) add_money($m_check_uid,$amount,'预付款充值','银联支付'.$sn.'充值',$sn);
        
        order_prepare($sn);
        change_order($sn);
    }
}