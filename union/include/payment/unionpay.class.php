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
    array('name' => 'security_key', 'value' => '','label'=>'商户密钥')
);

require_once dirname(__FILE__).'/unionpay_classes/quickpay_service.php';

class unionpay
{
    var $cfg;
    function unionpay($cfg = array())
    {
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    
    function pay_send($sn,$amount)    //提交支付请求
    {
        global $main_settings,$sessionID;
        $parameter['transType']         = '01';
        $parameter['origQid']           = '';
        $parameter['commodityUrl']      = '';    //鸡肋参数
        $parameter['commodityName']     = "支付订单$sn";
        $parameter['commodityUnitPrice']= 0;
        $parameter['commodityQuantity'] = 0;
        $parameter['commodityDiscount'] = 0;
        $parameter['transferFee']       = 0;
        $parameter['orderNumber']       = $sn;
        $parameter['orderAmount']       = $amount*100;
        $parameter['orderCurrency']     = '156';
        $parameter['orderTime']         = date('YmdHis');
        $parameter['customerIp']        = $_SERVER['REMOTE_ADDR'];
        $parameter['customerName']      = 'anoymous';
        $parameter['defaultPayType']    = '';
        $parameter['defaultBankNumber'] = '';
        $parameter['transTimeout']      = '300000';
        $parameter['merReserved']       = '';
        $parameter['frontEndUrl']       = "http://$_SERVER[HTTP_HOST]/member.php?action=order";
        $parameter['backEndUrl']        = "$main_settings[mm_mall_url]/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php');

        quickpay_conf::$ARR_SP_CONF['merId']=$this->cfg['mer_id'];
        quickpay_conf::$SECURITY_KEY=$this->cfg['security_key'];
        
        $pay_service = new quickpay_service($parameter);
        $result = $pay_service->create_html();
        return $result;
    }
    
    function pay_receive()    //提交返回处理
    {
        quickpay_conf::$ARR_SP_CONF['merId']=$this->cfg['mer_id'];
        quickpay_conf::$SECURITY_KEY=$this->cfg['security_key'];
        
        file_put_contents('unionpay.txt','step 1 '.date('Y-m-d H:i:s'));
    	if ($_SERVER["REQUEST_METHOD"] != 'POST')
    	{
    	    file_put_contents('unionpay.txt','not post '.date('Y-m-d H:i:s'));
            return false;
        }
        
        file_put_contents('unionpay.txt','step 2 '.date('Y-m-d H:i:s'));
        $quickpay_service = new quickpay_service($_POST, QUICKPAY_NOTIFY_SEVICE);
        /*
        if (!$quickpay_service->verify_notify())
        {
            file_put_contents('unionpay.txt','not valid '.date('Y-m-d H:i:s'));
            return false;
        }
        */
        
        file_put_contents('unionpay.txt','step 3 '.date('Y-m-d H:i:s'));
        //商户网站逻辑处理#
        $amount=floatval($_POST['orderAmount']);
        $orderid=dhtmlchars($_POST['orderNumber']);
        $list = order_info($orderid);
        if (round($list['order_amount'],2) == round($amount/100,2))
        {
            change_order($orderid);
            return true;
        }
        file_put_contents('unionpay.txt','money not valid '.date('Y-m-d H:i:s'));
        return false;
    }
}