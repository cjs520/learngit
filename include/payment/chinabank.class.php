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

$payment['chinabank']['name'] = '网银在线';    //插件的代码必须和文件名保持一致
$payment['chinabank']['desc'] = '网银在线（网关支付）';    //描述
$payment['chinabank']['reg'] = 'http://merchant3.chinabank.com.cn/register.do';    //申请地址
$payment['chinabank']['license'] = '<a href="http://merchant3.chinabank.com.cn/register.do" target="_blank">免费签约</a>';    //版权信息
/*接口需要的参数 */
$payment['chinabank']['cfg'] =array(
    array('name' => 'v_mid', 'value' => '','label'=>'商户编号'),
    array('name' => 'v_key', 'value' => '','label'=>'MD5 密钥'),
);


class chinabank
{
    var $cfg;
    function chinabank($cfg = array())
    {
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    
    function pay_send($sn,$amount)    //提交支付请求
	{
	    global $mm_mall_url,$sessionID;
	    $v_moneytype= 'CNY';
	    $v_url = "{$mm_mall_url}/respond.php?sessionID=$sessionID&code=".basename(__FILE__, '.class.php');
		$text = $amount.$v_moneytype.$sn.$this->cfg[v_mid].$v_url.$this->cfg[v_key];
		$v_md5info=strtoupper(trim(md5($text)));	
		$result="<form  name=re METHOD=post ACTION='https://pay.chinabank.com.cn/select_bank' target='_blank'>
				 <input type='hidden' name='v_md5info' value='$v_md5info'>
				 <input type='hidden' name='v_mid' value='{$this->cfg[v_mid]}'>
				 <input type='hidden' name='v_oid' value='$sn'>
				 <input type='hidden' name='v_amount' value='$amount'>
				 <input type='hidden' name='v_moneytype'  value='$v_moneytype'>
				 <input type='hidden' name='v_url' value='$v_url'>
				 <input type='image' name='PayBtnUrl' src='images/pay/chinabank.gif'>
				 </form>";
		return $result;
	}
	
    function pay_receive()    //提交返回处理
    {
        global $m_check_uid,$mm_mall_url;
        $v_oid = trim($_POST['v_oid']);
        $v_pmode = trim($_POST['v_pmode']);
        $v_pstatus = trim($_POST['v_pstatus']);
        $v_pstring = trim($_POST['v_pstring']);
        $amount = trim($_POST['v_amount']);
        $v_moneytype = trim($_POST['v_moneytype']);
        $remark1 = trim($_POST['remark1']);
        $remark2 = trim($_POST['remark2']);
        $v_md5str = trim($_POST['v_md5str']);
        $v_key = $this->cfg['v_key'];
		$text = $v_oid.$v_pstatus.$amount.$v_moneytype.$v_key;
        $v_md5info = strtoupper(trim(md5($text)));
        
        if(!$_POST) move_page("$mm_mall_url/member.php?action=order");
        if ($v_md5str != $v_md5info) show_msg('支付验证失败','./');
        if ($v_pstatus != '20') show_msg('支付状态错误','./');
 
        $sn= strip_tags($v_oid);
        require_once 'include/order.class.php';
        $salt=substr($sn,strlen($sn)-4,4);
        $sn=substr($sn,0,strlen($sn)-4);
        if(order::check_pay_log($sn,$salt,$amount)) add_money($m_check_uid,$amount,'预付款充值','网银在线支付'.$sn.'充值',$sn);
        
        order_prepare($sn);
        change_order($sn);
    }
}