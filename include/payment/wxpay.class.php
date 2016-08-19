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

$payment['wxpay']['name'] = '微信支付';    //插件的代码必须和文件名保持一致
$payment['wxpay']['desc'] = '微信支付';    //描述
//申请地址
$payment['wxpay']['reg'] = 'https://pay.weixin.qq.com';
$payment['wxpay']['license'] = '<a href="https://pay.weixin.qq.com" target="_blank">签约由此进</a>';    //版权信息
//接口需要的参数
$payment['wxpay']['cfg'] =array(
    array('name' => 'appid', 'value' => '','label'=>'应用ID(APPID)'),
    array('name' => 'mchid', 'value' => '','label'=>'商户ID(MCHID)'),
    array('name' => 'appkey', 'value' => '','label'=>'商户KEY'),
    array('name' => 'secret', 'value' => '', 'label'=>'APP SECRET'),
    array('name' => 'apiclient_cert.pem', 'value' => 'apiclient_cert.pem', 'label'=>'SSLCERT证书','t'=>'upload'),
    array('name' => 'apiclient_key.pem', 'value' => 'apiclient_key.pem', 'label'=>'SSLKEY证书','t'=>'upload'),
);

require_once dirname(__FILE__).'/wx_classes/WxPayPubHelper.php';

class wxpay
{
    var $cfg;
    var $wxpay_config;
    var $is_mobile;
    function wxpay($cfg = array())
    {
        global $is_mobile,$mm_mall_url,$sessionID;
        $this->is_mobile=$is_mobile;
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
        
        WxPayConf_pub::$APPID=$this->cfg['appid'];
        WxPayConf_pub::$MCHID=$this->cfg['mchid'];
        WxPayConf_pub::$KEY=$this->cfg['appkey'];
        WxPayConf_pub::$APPSECRET=$this->cfg['secret'];
        WxPayConf_pub::$NOTIFY_URL="{$mm_mall_url}/wap_respond-wxpay-$sessionID.html";
    }
    
    function pay_send($sn,$amount)    //提交支付请求
    {
        global $mm_mall_url,$sessionID;
        
        if($this->is_mobile && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger'))    //在微信浏览器中
        {
            WxPayConf_pub::$JS_API_CALL_URL="{$mm_mall_url}/wx_jsapi.php?param={$sn}_{$amount}";
            $result="<a href='".WxPayConf_pub::$JS_API_CALL_URL."'>点击进入微信支付</a>";
        }
        else
        {
            $unifiedOrder = new UnifiedOrder_pub();
            $unifiedOrder->setParameter("body",$sn);//商品描述
            $out_trade_no = $sn;
            $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
            $unifiedOrder->setParameter("total_fee",$amount*100);//总金额
            $unifiedOrder->setParameter("notify_url",WxPayConf_pub::$NOTIFY_URL);//通知地址
            $unifiedOrder->setParameter("trade_type","NATIVE");//交易类型

            $unifiedOrderResult = $unifiedOrder->getResult();

            $result='';
            if ($unifiedOrderResult["return_code"] == "FAIL")
            {
                $result="ERR：".$unifiedOrderResult['return_msg'];
            }
            else if($unifiedOrderResult["result_code"] == "FAIL")
            {
                $result="ERR：".$unifiedOrderResult['err_code'].' '.$unifiedOrderResult['err_code_des']."<br>";
            }
            else if($unifiedOrderResult["code_url"] != NULL)    //通信返回成功
            {
                $code_url = $unifiedOrderResult["code_url"];
                $result='<div align="center" id="wx_qrcode"></div>';
                $result.='<script src="include/javascript/wx_qrcode.js"></script>';
                $result.="<script type='text/javascript'>";
                $result.="var qr = qrcode(10, 'M');";
                $result.="qr.addData('$code_url');";
                $result.="qr.make();";
                $result.="$('#wx_qrcode').append('<div>'+qr.createImgTag()+'</div>');";
                $result.="$('#wx_qrcode').append('<p>扫我进行支付</p>');";
                $result.="$('#wx_qrcode').append('<p><a href=\"member.php?action=order\" target=\"_blank\">支付完成后请点击这里</a></p>');";
                $result.="</script>";
            }
        }
        
        return $result;
    }
    
    public function pay_receive()    //提交返回处理
    {
        global $m_check_uid;
        
        $notify = new Notify_pub();
	    $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
	    $notify->saveData($xml);
	    if($notify->checkSign() == FALSE)
	    {
		    $notify->setReturnParameter("return_code","FAIL");
		    $notify->setReturnParameter("return_msg","签名失败");
	    }
	    else
	    {
		    $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
	    }
	    $returnXml = $notify->returnXml();
	    echo $returnXml;
	    
	    if($notify->checkSign() == false) exit('ERR:sign error');
	    if($notify->data["return_code"] == "FAIL" || $notify->data["result_code"] == "FAIL") exit('ERR:return error');
	    $oXml=simplexml_load_string($xml);
	    
	    $amount=round(floatval($oXml->total_fee)/100,2);
	    $sn=$oXml->out_trade_no;

        require_once 'include/order.class.php';
        $salt=substr($sn,strlen($sn)-4,4);
        $sn=substr($sn,0,strlen($sn)-4);
        if(order::check_pay_log($sn,$salt,$amount)) add_money($m_check_uid,$amount,'预付款充值','微信支付'.$sn.'充值',$sn);
        order_prepare($sn);
        change_order($sn);
    }//end function pay_receive
    
}