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

$payment['jdpay']['name'] = '京东支付';    //插件的代码必须和文件名保持一致
$payment['jdpay']['desc'] = '京东支付（原网银+）';    //描述
//申请地址
$payment['jdpay']['reg'] = 'https://biz.wangyin.com/index.jsp';
$payment['jdpay']['license'] = '<a href="https://biz.wangyin.com/index.jsp" target="_blank">签约由此进</a>';    //版权信息
//接口需要的参数
$payment['jdpay']['cfg'] =array(
    array('name' => 'merchant_num', 'value' => '','label'=>'商户ID'),
    array('name' => 'deskey', 'value' => '','label'=>'商户DES密钥'),
    array('name' => 'md5key', 'value' => '', 'label'=>'商户MD5密钥'),
    array('name' => 'my_rsa_private_key.pem', 'value' => 'my_rsa_private_key.pem', 'label'=>'商户证书(my_开头)','t'=>'upload'),
    array('name' => 'wy_rsa_public_key.pem', 'value' => 'wy_rsa_public_key.pem', 'label'=>'网银证书(wy_开头)','t'=>'upload'),
);

use wepay\join\demo\common\DesUtils;
use wepay\join\demo\common\SignUtil;
include dirname(__FILE__).'/jd_class/common/DesUtils.php';
include dirname(__FILE__).'/jd_class/common/SignUtil.php';

class jdpay
{
    var $cfg;
    var $jdpay_config;
    var $is_mobile;
    function jdpay($cfg = array())
    {
        global $is_mobile,$mm_mall_url,$sessionID;
        $this->is_mobile=$is_mobile;
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    
    function pay_send($sn,$amount)    //提交支付请求
    {
        global $mm_mall_url,$sessionID,$m_now_time;
        
        $result='';
        if($this->is_mobile)
        {
            $notify_url = "{$mm_mall_url}/wap_respond-jdpay-$sessionID.html";    //异步返回地址
            $return_url = "{$mm_mall_url}/ajax-jd-success_pay.html";
            $amount*=100;    //京东支付以分为单位，所以要乘以100
            
            $param = array();
		    $param["currency"] = 'CNY';
		    $param["failCallbackUrl"] = $return_url;
		    $param["merchantNum"] = $this->cfg['merchant_num'];
		    $param["merchantRemark"] = '';
		    $param["notifyUrl"] = $notify_url;
		    $param["successCallbackUrl"] = $return_url;
		    $param["tradeAmount"] = $amount;
		    $param["tradeDescription"] = '';
		    $param["tradeName"] = $sn;
		    $param["tradeNum"] = $sn;
		    $param["tradeTime"] = date('Y-m-d H:i:s', $m_now_time);
		    $param["version"] = '1.0';
		    $param["token"] = '';
            
		    $sign = SignUtil::sign($param,array ("merchantSign","token","version" ));
		    $param["merchantSign"] = $sign;
		    
            $result.='<form method="post" action="https://m.jdpay.com/wepay/web/pay" id="payForm">';
            $result.='<input type="hidden" name="version" value="'.$param['version'].'"/>';
            $result.='<input type="hidden" name="token" value="'.$param['token'].'"/>';
            $result.='<input type="hidden" name="merchantSign" value="'.$param['merchantSign'].'"/>';
            $result.='<input type="hidden" name="merchantNum" value="'.$param['merchantNum'].'"/>';
            $result.='<input type="hidden" name="merchantRemark" value="'.$param['merchantRemark'].'"/>';
            $result.='<input type="hidden" name="tradeNum" value="'.$param['tradeNum'].'"/>';
            $result.='<input type="hidden" name="tradeName" value="'.$param['tradeName'].'"/>';
            $result.='<input type="hidden" name="tradeDescription" value="'.$param["tradeDescription"].'"/>';
            $result.='<input type="hidden" name="tradeTime" value="'.$param["tradeTime"].'"/>';
            $result.='<input type="hidden" name="tradeAmount" value="'.$param["tradeAmount"].'"/>';
            $result.='<input type="hidden" name="currency" value="'.$param["currency"].'"/>';
            $result.='<input type="hidden" name="notifyUrl" value="'.$param["notifyUrl"].'"/>';
            $result.='<input type="hidden" name="successCallbackUrl" value="'.$param["successCallbackUrl"].'"/>';
            $result.='<input type="hidden" name="failCallbackUrl" value="'.$param["failCallbackUrl"].'"/>';
            $result.='<a href="#" id="J-next-btn" class="lost_button"></a>';
            $result.='</form>';
            $result.='<script>';
            $result.='$(function(){';
            $result.="$('#J-next-btn').click(function(e){";
            $result.="e.preventDefault();";
            $result.="document.getElementById('payForm').submit();";
            $result.='});';
            $result.='});';
            $result.='</script>';
        }
        else
        {
            $notify_url = "{$mm_mall_url}/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php');    //异步返回地址
            $return_url = "{$mm_mall_url}/ajax-jd-success_pay.html";
            $amount*=100;    //京东支付以分为单位，所以要乘以100
            
            $result.='<form method="post" action="'.$mm_mall_url.'/include/payment/jd_class/api/WebPaySign.php" id="payForm" target="iframeLayer">';
            $result.='<input type="hidden"  name="version" value="1.1" data-callback="input.status" />';
            $result.='<input type="hidden" class="" name="token" value="" data-callback="input.status" />';
            $result.='<input type="hidden" class="" name="merchantNum" value="'.$this->cfg['merchant_num'].'" data-callback="input.status" />';
            $result.='<input type="hidden" class="" name="merchantRemark" value="" data-callback="input.status" />';
            $result.='<input type="hidden" class="" name="tradeNum" value="'.$sn.'" maxlength="50" data-callback="input.status"/>';
            $result.='<input type="hidden" class="" name="tradeName" value="'.$sn.'" data-callback="input.status"/>';
            $result.='<input type="hidden" class="" name="tradeDescription" value="" data-callback="input.status"/>';
            $result.='<input type="hidden" class="" name="tradeTime" value="'.date('Y-m-d H:i:s', $m_now_time).'" data-callback="input.status"/>';
            $result.='<input type="hidden" class="" name="tradeAmount" value="'.$amount.'" data-callback="input.status"/>';
            $result.='<input type="hidden" class="" name="currency" value="CNY" data-callback="input.status"/>';
            $result.='<input type="hidden" class="" name="notifyUrl" value="'.$notify_url.'" data-callback="input.status"/>';
            $result.='<input type="hidden" class="" name="successCallbackUrl" value="'.$return_url.'" data-callback="input.status"/>';
            $result.='<input type="hidden" class="" name="forPayLayerUrl" value="'.$mm_mall_url.'/ajax.php?action=jd&cmd=close_layer&rnd=1" data-callback="input.status"/>';
            $result.='<input type="hidden" class="" name="ip" value="'.$_SERVER['REMOTE_ADDR'].'" data-callback="input.status"/>';
            $result.='<a href="javascript:;" id="J-next-btn" class="lost_button btn btn-disabled mt15 btn-actived"></a>';
            $result.='</form>';
            $result.='<iframe id="iframeLayer" frameborder="0" name="iframeLayer" class="iframeLayer" allowTransparency="true" style="display:none;position:absolute; z-index:999; width:100%; height:100%; left: 0px; right:0; bottom: 0; background:url(\''.$mm_mall_url.'/include/payment/jd_class/static/images/loading.gif\') center center no-repeat;" src=""></iframe>';
            
            $result.='<script src="'.$mm_mall_url.'/include/payment/jd_class/static/js/wyplus-ctrl.js"></script>';
            $result.='<script>';
            $result.='$(function(){';
            $result.="$('#iframeLayer').css('top',$(document).scrollTop()+'px');";
            $result.="$('#J-next-btn').click(function(e){";
            $result.="e.preventDefault();";
            $result.='WYPLUS.open({';
            $result.="formId: 'payForm',";
            $result.="iframeId: 'iframeLayer'";
            $result.='});';
            $result.="document.getElementById('payForm').submit();";
            $result.='});';
            $result.='});';
            $result.='</script>';
        }
        
        return $result;
    }
    
    public function pay_receive()    //提交返回处理
    {
        global $m_check_uid,$mm_mall_url;
        
        //解出$amount和$sn
	    $MD5_KEY = $this->cfg['md5key'];
        $DES_KEY = $this->cfg['deskey'];
        $w = new WebAsynNotificationCtrl ();
        $arr_trade=$w->execute ($MD5_KEY,$DES_KEY,$_POST['resp'] );
        if(sizeof($arr_trade)!=2) show_msg('发生错误，请联系管理员',$mm_mall_url);
        $amount=round($arr_trade[0]/100,2);
        $sn=$arr_trade[1];
        
        require_once 'include/order.class.php';
        $salt=substr($sn,strlen($sn)-4,4);
        $sn=substr($sn,0,strlen($sn)-4);
        if(order::check_pay_log($sn,$salt,$amount)) add_money($m_check_uid,$amount,'预付款充值','京东支付'.$sn.'充值',$sn);
        order_prepare($sn);
        change_order($sn);
    }//end function pay_receive
    
}


class WebAsynNotificationCtrl
{

	public function xml_to_array($xml)
	{
		$array = ( array ) (simplexml_load_string ( $xml ));
		foreach ( $array as $key => $item ) {
			$array [$key] = $this->struct_to_array ( ( array ) $item );
		}
		return $array;
	}
	
	public function struct_to_array($item)
	{
		if (! is_string ( $item )) {
			$item = ( array ) $item;
			foreach ( $item as $key => $val ) {
				$item [$key] = $this->struct_to_array ( $val );
			}
		}
		return $item;
	}
	
	public function generateSign($data, $md5Key)
	{
		$sb = $data ['VERSION'] [0] . $data ['MERCHANT'] [0] . $data ['TERMINAL'] [0] . $data ['DATA'] [0] . $md5Key;
		
		return md5 ( $sb );
	}
	
	public function execute($md5Key, $desKey,$resp)
	{
	    global $mm_mall_url;
		// 获取通知原始信息
		if (null == $resp) return;
		
		// 解析XML
		$params = $this->xml_to_array ( base64_decode ( $resp ) );
		$ownSign = $this->generateSign ( $params, $md5Key );
		$params_json = json_encode ( $params );
		if ($params ['SIGN'] [0] != $ownSign) show_msg('验签错误，请联系管理员',$mm_mall_url);
		
		// 对Data数据进行解密
		$des = new DesUtils ();
		$decryptArr = $des->decrypt ( $params ['DATA'] [0], $desKey );
		//echo "对<DATA>进行解密得到的数据:" . $decryptArr . "\n";
		
		$oXml=simplexml_load_string($decryptArr);
		//var_export($oXml);
		
		if($oXml->RETURN->CODE!='0000') show_msg('支付失败，错误代码：'.$oXml->RETURN->DESC,$mm_mall_url);
		return array($oXml->TRADE->AMOUNT,$oXml->TRADE->ID);
	}
}