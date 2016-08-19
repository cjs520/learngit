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

$payment['cnpay']['name'] = '云网在线';    //插件的代码必须和文件名保持一致
$payment['cnpay']['desc'] = '云网在线';    //描述
$payment['cnpay']['reg'] = 'http://www.cncard.net/';    //申请地址
$payment['cnpay']['license']  = '<a href="http://www.cncard.net/" target="_blank">免费签约</a>';    //版权信息
/*接口需要的参数*/
$payment['cnpay']['cfg'] =array(
    array('name' => 'v_mid', 'value' => '','label'=>'商户编号'),
    array('name' => 'v_key', 'value' => '','label'=>'MD5 密钥'),
);

class cnpay
{
    var $cfg;
    
    function cnpay($cfg = array())
    {
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    /*提交支付请求*/
    function pay_send($sn,$amount)	
	{
	    global $main_settings,$sessionID;
	    $c_mid	= $this->cfg['v_mid'];//商户编号
	    $c_name= '';    //商户订单中的收货人姓名
	    $c_address = '';    //商户订单中的收货人地址
	    $c_tel = '';    //商户订单中的收货人电话
	    $c_post = '';    //商户订单中的收货人邮编
	    $c_email = '';    //商户订单中的收货人Email
	    //$c_orderamount = "0.01";    //商户订单总金额
	    $c_ymd = date('Ymd');    //商户订单的产生日期，格式为"yyyymmdd"，如20050102
	    $c_moneytype= '0';    //支付币种，0为人民币
	    $c_retflag = '1';    //商户订单支付成功后是否需要返回商户指定的文件，0：不用返回 1：需要返回
	    $c_paygate = '';    //如果在商户网站选择银行则设置该值，具体值可参见《云网支付@网技术接口手册》附录一；如果来云网支付@网选择银行此项为空值。
	    $c_returl = "$main_settings[mm_mall_url]/respond.php?sessionID=$sessionID";    //为空表示云网的;
	    $c_memo1 = '';    //备注1
	    $c_memo2 = '';    //备注2
	    $c_pass = $this->cfg['v_key'];    //支付密钥，请登录商户管理后台，在帐户信息-基本信息-安全信息中的支付密钥项
	    $notifytype	= '0';    //0普通通知方式/1服务器通知方式，空值为普通通知方式
	    $c_language	= '1';    //对启用了国际卡支付时，可使用该值定义消费者在银行支付时的页面语种，值为：0银行页面显示为中文/1银行页面显示为英文
	    $srcStr = $c_mid . $sn . $amount . $c_ymd . $c_moneytype . $c_retflag . $c_returl . $c_paygate . $c_memo1 . $c_memo2 . $notifytype . $c_language . $c_pass;
	    //说明：如果您想指定支付方式(c_paygate)的值时，需要先让用户选择支付方式，然后再根据用户选择的结果在这里进行MD5加密，也就是说，此时，本页面应该拆分为两个页面，分为两个步骤完成。	
        //--对订单信息进行MD5加密
	    //商户对订单信息进行MD5签名后的字符串
	    $c_signstr = md5($srcStr);
		$result="<form name='payForm1' action='https://www.cncard.net/purchase/getorder.asp' method='POST' target='_blank'>
		         <input type='hidden' name='c_mid' value='$c_mid'>
			     <input type='hidden' name='c_order' value='$sn'>
			     <input type='hidden' name='c_name' value='$c_name'>
			     <input type='hidden' name='c_address' value='$c_address'>
			     <input type='hidden' name='c_tel' value='$c_tel'>
			     <input type='hidden' name='c_post' value='$c_post'>
			     <input type='hidden' name='c_email' value='$c_email'>
			     <input type='hidden' name='c_orderamount' value='$amount'>
			     <input type='hidden' name='c_ymd' value='$c_ymd'>
			     <input type='hidden' name='c_moneytype' value='$c_moneytype'>
			     <input type='hidden' name='c_retflag' value='$c_retflag'>
			     <input type='hidden' name='c_paygate' value='$c_paygate'>
			     <input type='hidden' name='c_returl' value='$c_returl'>
			     <input type='hidden' name='c_memo1' value='$c_memo1'>
			     <input type='hidden' name='c_memo2' value='$c_memo2'>
			     <input type='hidden' name='c_language' value='$c_language'>
			     <input type='hidden' name='notifytype' value='$notifytype'>
			     <input type='hidden' name='c_signstr' value='$c_signstr'>
			     <input type='submit' name='submit' value='点击 -> 云网支付@网'>
                 </form>";
		return $result;
	}
    
    function pay_receive()    //提交返回处理
    {
	    $c_mid = $_REQUEST['c_mid'];    //商户编号，在申请商户成功后即可获得，可以在申请商户成功的邮件中获取该编号
	    $c_order = $_REQUEST['c_order'];    //商户提供的订单号
	    $c_orderamount = $_REQUEST['c_orderamount'];    //商户提供的订单总金额，以元为单位，小数点后保留两位，如：13.05
	    $c_ymd = $_REQUEST['c_ymd'];    //商户传输过来的订单产生日期，格式为"yyyymmdd"，如20050102
	    $c_transnum = $_REQUEST['c_transnum'];    //云网支付网关提供的该笔订单的交易流水号，供日后查询、核对使用；
	    $c_succmark = $_REQUEST['c_succmark'];    //交易成功标志，Y-成功 N-失败			
	    $c_moneytype = $_REQUEST['c_moneytype'];    //支付币种，0为人民币
	    $c_cause = $_REQUEST['c_cause'];    //如果订单支付失败，则该值代表失败原因		
	    $c_memo1 = $_REQUEST['c_memo1'];    //商户提供的需要在支付结果通知中转发的商户参数一
	    $c_memo2 = $_REQUEST['c_memo2'];    //商户提供的需要在支付结果通知中转发的商户参数二
	    $c_signstr = $_REQUEST['c_signstr'];    //云网支付网关对已上信息进行MD5加密后的字符串

	    //--校验信息完整性---
		if($c_mid=="" || $c_order=="" || $c_orderamount=="" || $c_ymd=="" || $c_moneytype=="" || $c_transnum=="" || $c_succmark=="" || $c_signstr=="") return false;
		
		//--将获得的通知信息拼成字符串，作为准备进行MD5加密的源串，需要注意的是，在拼串时，先后顺序不能改变
		//商户的支付密钥，登录商户管理后台(https://www.cncard.net/admin/)，在管理首页可找到该值
		$c_pass =  $this->cfg['v_key'];		
		$srcStr = $c_mid . $c_order . $c_orderamount . $c_ymd . $c_transnum . $c_succmark . $c_moneytype . $c_memo1 . $c_memo2 . $c_pass;

	    //--对支付通知信息进行MD5加密
		$r_signstr	= md5($srcStr);
	    //--校验商户网站对通知信息的MD5加密的结果和云网支付网关提供的MD5加密结果是否一致
		if($r_signstr!=$c_signstr) return false;
		
		//--校验返回的支付结果的格式是否正确
		if($c_succmark == 'Y')
		{
		    $c_order = dhtmlchars($c_order);
		    $list = order_info($c_order);
		    if ($list['order_amount'] == $c_orderamount)
		    {
		        change_order($c_order);
		        return true;
		    }
		}
        return false;
    }
}