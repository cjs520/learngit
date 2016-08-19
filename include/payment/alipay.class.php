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

$payment['alipay']['name'] = '支付宝支付';    //插件的代码必须和文件名保持一致
$payment['alipay']['desc'] = '支付宝支付';    //描述
//申请地址
$payment['alipay']['reg'] = 'https://www.alipay.com/himalayas/market.htm?type=from_agent_contract&id=C4335359323161414111&p=61F99645EC0DC4380ADE569DD132AD7A';
$payment['alipay']['license'] = '<a href="https://www.alipay.com/himalayas/market.htm?type=from_agent_contract&id=C4335359323161414111&p=61F99645EC0DC4380ADE569DD132AD7A" target="_blank">携手支付宝 免费签约 费率低至1.2%</a>';    //版权信息
//接口需要的参数
$payment['alipay']['cfg'] =array(
    array('name' => 'seller_email', 'value' => '','label'=>'支付宝帐号'),
    array('name' => 'security_code', 'value' => '','label'=>'KEY'),
    array('name' => 'partner', 'value' => '','label'=>'PID'),
    array('name' => 'real_name', 'value' => '', 'label'=>'支付宝真实姓名'),
    array('name' => 'public_key', 'value' => '', 'label'=>'手机接口支付公钥'),
    array('name' => 'private_key', 'value' => '', 'label'=>'手机接口支付密钥'),
    array('name' => 'wap_public_key', 'value' => '', 'label'=>'WAP接口支付公钥'),
    array('name' => 'wap_private_key', 'value' => '', 'label'=>'WAP手机接口支付密钥'),
    array('name' => 'service', 'value' =>'create_partner_trade_by_buyer','label'=>'接口选择<br />
                                          <input type="radio" name="rdo_if" rel="item" id="create_partner_trade_by_buyer" checked> 纯担保交易接口<br />
                                          <input type="radio" name="rdo_if" rel="item" id="trade_create_by_buyer"> 标准实物双接口<br />
                                          <input type="radio" name="rdo_if" rel="item" id="create_direct_pay_by_user"> 即时到账接口')
);

require_once dirname(__FILE__).'/alipay_classes/alipay_transfer_service.php';

class alipay
{
    var $cfg;
    var $alipay_config;
    var $is_mobile;
    function alipay($cfg = array())
    {
        global $is_mobile;
        $this->is_mobile=$is_mobile;
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
        if($this->is_mobile)
        {
            $this->alipay_config=array();
            $this->alipay_config['partner']=$this->cfg['partner'];
            $this->alipay_config['key']='MD5';
            $this->alipay_config['private_key_path']='data/malldata/wap_alipay_private_key.pem';
            $this->alipay_config['ali_public_key_path']='data/malldata/wap_alipay_public_key.pem';
            $this->alipay_config['sign_type'] = '0001';
            $this->alipay_config['input_charset']="utf-8";
            $this->alipay_config['cacert'] = MVMMALL_ROOT.'/data/malldata/cacert.pem';;
            $this->alipay_config['transport']='http';
        }
    }
    
    function pay_send($sn,$amount)    //提交支付请求
    {
        global $mm_mall_url,$sessionID;
        
        $partner = $this->cfg['partner'];    //合作伙伴ID
        $security_code = $this->cfg['security_code'];    //安全检验码
        $seller_email = $this->cfg['seller_email'];    //支付宝帐号
        $_input_charset = "utf-8";    //字符编码格式
        $sign_type = 'MD5';    //加密方式
        $transport= 'http';    //访问模式,你可以根据自己的服务器是否支持ssl访问而选择http以及https访问模式
        
        if($this->is_mobile)
        {
            require_once dirname(__FILE__).'/alipay_classes/lib/alipay_submit.class.php';
            
            $notify_url = "{$mm_mall_url}/wap_respond-alipay-$sessionID.html";    //异步返回地址
            $return_url = "{$mm_mall_url}/wap_respond-alipay-$sessionID.html";    //同步返回地址
            
            if(!file_exists($this->alipay_config['private_key_path'])) return '密钥文件不存在';
            if(!file_exists($this->alipay_config['ali_public_key_path'])) return '公钥文件不存在';
            if(!file_exists($this->alipay_config['cacert'])) return '证书文件不存在';
            
            $format = "xml";
            $v = "2.0";

            $req_id = date('Ymdhis').rand(10,99);
            $notify_url = $notify_url;
            $call_back_url = $return_url;
            $merchant_url = $return_url;
            
            //必填
            $out_trade_no = $sn;
            $subject = $sn;
            $total_fee = $amount;

            //请求业务参数详细
            $req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';
            $para_token = array(
                "service" => "alipay.wap.trade.create.direct",
                "partner" => trim($this->alipay_config['partner']),
                "sec_id" => trim($this->alipay_config['sign_type']),
                "format"	=> $format,
                "v"	=> $v,
                "req_id"	=> $req_id,
                "req_data"	=> $req_data,
                "_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
            );
            
            $alipaySubmit = new AlipaySubmit($this->alipay_config);
            $html_text = $alipaySubmit->buildRequestHttp($para_token);
            $html_text = urldecode($html_text);
            $para_html_text = $alipaySubmit->parseResponse($html_text);
            $request_token = $para_html_text['request_token'];
            //业务详细
            $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
            
            //构造要请求的参数数组，无需改动
            $parameter = array(
                "service" => "alipay.wap.auth.authAndExecute",
                "partner" => trim($this->alipay_config['partner']),
                "sec_id" => trim($this->alipay_config['sign_type']),
                "format" => $format,
                "v"	=> $v,
                "req_id" => $req_id,
                "req_data" => $req_data,
                "_input_charset" => trim(strtolower($this->alipay_config['input_charset']))
            );
            
            //建立请求
            $alipaySubmit = new AlipaySubmit($this->alipay_config);
            $result = $alipaySubmit->buildRequestForm($parameter, 'get', '确认');
        }
        else
        {
            $notify_url = "{$mm_mall_url}/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php');    //异步返回地址
            $return_url = "{$mm_mall_url}/member.php?action=order";
            $parameter = array(
                'service' => $this->cfg['service'],    //交易类型，必填实物交易＝trade_create_by_buyer（需要填写物流） 虚拟物品交易＝create_digital_goods_trade_p 捐赠＝create_donate_trade_p
                'partner' =>$partner,    //合作商户号
                'return_url' =>$return_url,    //同步返回
                'notify_url' =>$notify_url,    //异步返回
                '_input_charset' => $_input_charset,    //字符集，默认为GBK
                'subject' => "$sn",    //商品名称，必填
                'body' => "",    //商品描述，必填
                'out_trade_no' => $sn ,    //商品外部交易号，必填,每次测试都须修改
                'price' => $amount,    //商品单价，必填
                'discount' => '',    //折扣
                'payment_type' => '1',    // 商品支付类型 1 ＝商品购买 2＝服务购买 3＝网络拍卖 4＝捐赠 5＝邮费补偿 6＝奖金
                'quantity' => '1',    //商品数量，必填
                'show_url' => $GLOBALS['mm_mall_url'],    //商品相关网站
                'logistics_type' => 'EXPRESS',    //物流类型：VIRTUAL＝虚拟物品 POST＝平邮 EMS＝EMS EXPRESS＝其他快递公司
                'logistics_fee' => '0',    //物流费用
                'logistics_payment' => 'SELLER_PAY',    //物流支付类型: SELLER_PAY=商家支付 BUYER_PAY=买家支付 BUYER_PAY_AFTER_RECEIVE=货到付款
                'seller_email' => $seller_email,    //支付宝帐号，必填
            );
            //file_put_contents('1.txt',var_export($parameter,true).PHP_EOL.$security_code);
            $alipay = new alipay_service($parameter,$security_code,$sign_type);
            $link=$alipay->create_url();
            $result='<a href='.$link.' rel="pay_a" target="_blank" ><img src="images/pay/alipay.gif"></a>';
        }
        
        return $result;
    }
    
    function pay_receive()    //提交返回处理
    {
        global $m_check_uid;
        
        $order_status='';
        if($this->is_mobile)
        {
            require_once dirname(__FILE__).'/alipay_classes/lib/alipay_notify.class.php';
            $alipayNotify = new AlipayNotify($this->alipay_config);
            //$verify_result = $alipayNotify->verifyNotify();
            $doc = new DOMDocument();
            $doc->loadXML($alipayNotify->decrypt($_POST['notify_data']));
            if(empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) exit('fail');
            
            $billno = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
            $amount = $doc->getElementsByTagName( "price" )->item(0)->nodeValue;
            $order_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
        }
        else
        {
            $billno = trim($_REQUEST['out_trade_no']);    //订单
            $amount = trim($_REQUEST['total_fee']);    //金额
            $security_code = $this->cfg['security_code'];    //安全检验码
            $order_status=$_REQUEST['trade_status'];
        }
        
        if (!in_array($order_status,array('WAIT_SELLER_SEND_GOODS', 'TRADE_SUCCESS','TRADE_FINISHED'))) show_msg('订单状态错误','./');
        $sn = strip_tags($billno);

        require_once 'include/order.class.php';
        $salt=substr($sn,strlen($sn)-4,4);
        $sn=substr($sn,0,strlen($sn)-4);
        if(order::check_pay_log($sn,$salt,$amount)) add_money($m_check_uid,$amount,'预付款充值','支付宝支付'.$sn.'充值',$sn);
        order_prepare($sn);
        change_order($sn);
    }//end function pay_receive
    
    function transfer_link($money_apply,$member)
    {
        $link='';
        
        //流水号1^收款方帐号1^收款帐号1真实姓名^付款金额1^备注说明1
        $detail_data="$money_apply[sn]^$money_apply[account]^$money_apply[member_name]^$money_apply[real_money]^提现申请：$money_apply[sn]";
        $parameter = array(
            "service" => "batch_trans_notify",	//接口名称，不需要修改
            //获取配置文件(alipay_config.php)中的值
            "partner" => $this->cfg['partner'],
            "email" => $this->cfg['seller_email'],
		    "account_name" => $this->cfg['real_name'],
            "notify_url" => 'http://'.$_SERVER['HTTP_HOST'].'/alipay_transfer_notify.php',
            "_input_charset" => 'utf-8',
            //必填参数
            "pay_date" => date('Ymd'),
            "batch_no" => $money_apply['sn'],
            "batch_num" => 1,
            "batch_fee" => $money_apply['real_money'],
            "detail_data" => $detail_data
        );
        
        $o = new alipay_transfer_service($parameter,$this->cfg['security_code'],'MD5');
        $link = $o->build_form();
        
        return $link;
    }//end function transfer_link
}

//支付宝外部服务接口控制
class alipay_service
{
	var $gateway = 'https://mapi.alipay.com/gateway.do?';    //支付接口
	var $parameter;    //全部需要传递的参数
	var $security_code;    //安全校验码
	var $mysign;    //签名
	//构造支付宝外部服务接口控制
	function alipay_service($parameter,$security_code,$sign_type = 'MD5',$transport= 'https')
	{
		$this->parameter = $this->para_filter($parameter);
		$this->security_code = $security_code;
		$this->sign_type = $sign_type;
		$this->mysign = '';
		$this->transport = $transport;
		if($parameter['_input_charset'] == '') $this->parameter['_input_charset'] = 'GBK';
		$this->gateway = "https://mapi.alipay.com/gateway.do?";
		$sort_array = array();
		$arg = '';
		$sort_array = $this->arg_sort($this->parameter);
		while (list ($key, $val) = each ($sort_array)) $arg.=$key."=".$this->charset_encode($val,$this->parameter['_input_charset'])."&";
		$prestr = substr($arg,0,count($arg)-2);  //去掉最后一个问号
		$this->mysign = $this->sign($prestr.$this->security_code);
	}

	function create_url()
	{
		$url = $this->gateway;
		$sort_array = array();
		$arg = '';
		$sort_array = $this->arg_sort($this->parameter);
		while (list ($key, $val) = each ($sort_array)) $arg.=$key."=".urlencode($this->charset_encode($val,$this->parameter['_input_charset']))."&";
		$url.= $arg."sign=" .$this->mysign ."&sign_type=".$this->sign_type;
		return $url;

	}

	function arg_sort($array)
	{
		ksort($array);
		reset($array);
		return $array;
	}

	function sign($prestr)
	{
		$mysign = '';
		if($this->sign_type == 'MD5') $mysign = md5($prestr);
		elseif($this->sign_type =='DSA') exit("DSA 签名方法待后续开发，请先使用MD5签名方式");
		else exit("支付宝暂不支持".$this->sign_type."类型的签名方式");
		return $mysign;

	}
	
	function para_filter($parameter)    //除去数组中的空值和签名模式
	{
		$para = array();
		while (list ($key, $val) = each ($parameter))
		{
			if($key == 'sign' || $key == 'sign_type' || $val == '') continue;
			else $para[$key] = $parameter[$key];
		}
		return $para;
	}
	
	function charset_encode($input,$_output_charset ,$_input_charset ='GBK' )    //实现多种字符编码方式
	{
		$output = '';
		if(!isset($_output_charset) )$_output_charset  = $this->parameter['_input_charset '];
		if($_input_charset == $_output_charset || !$input) $output = $input;
		elseif (function_exists("mb_convert_encoding")) $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		elseif(function_exists("iconv")) $output = iconv($_input_charset,$_output_charset,$input);
		else die("sorry, you have no libs support for charset change.");
		return $output;
	}
}