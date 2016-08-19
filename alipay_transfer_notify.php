<?php
/**
 * MVM_MALL 网上商店系统 首页文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: alipay_transfer_notify.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require_once 'include/payment/alipay_classes/alipay_transfer_notify.php';

$pay = $db->get_one("SELECT cfg FROM `{$tablepre}payment_table` 
	                 WHERE class_name='alipay' AND supplier_id='0' 
	                 LIMIT 1");
if(!$pay) file_put_contents('alipay_log.txt','站点未安装支付宝接口 '.date('Y-m-d H:i:s'));;
$cfg_tmp=unserialize($pay['cfg']);
$cfg=array();
foreach ($cfg_tmp as $key=>$val) $cfg[$val['name']] = $val['value'];

$alipay = new alipay_transfer_notify($cfg['partner'],$cfg['security_code'],'MD5','UTF-8','http');    //构造通知函数信息
$verify_result = $alipay->notify_verify();  //计算得出通知验证结果

if($verify_result)    //验证成功
{
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代
	
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
	$batch_no = $_POST['batch_no'];		//获取批次号
    $pay_account_no	= $_POST['pay_account_no'];	//获取付款账号，对应请求参数email
	$success_details= $_POST['success_details'];//获取批量付款数据中转账成功的详细信息
	//格式：()括号中的信息代表默认值
	//流水号1^收款方帐号1^收款帐号真实姓名^付款金额^成功标识(S)^
	//成功原因(null)^内部流水号^完成时间|流水号2^收款方帐号2^收款帐号真实姓名^付款金额^成功标识(S)^成功原因(null)^内部流水号^完成时间
	$fail_details = $_POST['fail_details'];	//获取批量付款数据中转账失败的详细信息
	//格式：()括号中的信息代表默认值
	//流水号1^收款方帐号1^收款帐号真实姓名^付款金额^成功标识(F)^
	//失败原因^内部流水号^完成时间|流水号2^收款方帐号2^收款帐号真实姓名^付款金额^成功标识(F)^失败原因^内部流水号^完成时间
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if($fail_details) file_put_contents('alipay_log.txt','转账失败 '.$fail_details.' '.date('Y-m-d H:i:s'));
	$money_apply=$db->get_one("SELECT * FROM `{$tablepre}money_apply` WHERE sn='$batch_no' LIMIT 1");
	if(!$money_apply) file_put_contents('alipay_log.txt','检索不到申请记录 '.date('Y-m-d H:i:s'));
	
	$member=$db->get_one("SELECT uid,member_id,member_money,member_money_freeze FROM `{$tablepre}member_table` WHERE uid='$money_apply[supplier_id]' LIMIT 1");
	if(!$member) file_put_contents('alipay_log.txt','检索不到提现商铺 '.date('Y-m-d H:i:s'));
	$db->query("UPDATE `{$tablepre}money_apply` SET status='1' WHERE uid='$money_apply[uid]'");
	
	$sql = "INSERT INTO `{$tablepre}money_table` SET 
    		type='支付宝',
            money_sess = '$money_apply[sn]',
            money_id = '$member[member_id]',
            money_add = '-$money_apply[money]',
            money_reason = '提现申请审核通过',
            money_left = '$member[member_money]',
            modify_ip = '$m_user_ip',
            register_date = '$m_now_time',
            approval_date = '$m_now_time'";
	$db->query($sql);
	
	$member_row['member_money_freeze']-=$money_apply['money_apply'];
	$db->update("`{$tablepre}member_table`",$member_row,"uid='$member[uid]'");
	
	file_put_contents('alipay_log.txt','转账成功 '.date('Y-m-d H:i:s'));
}
else    //验证失败
{
    file_put_contents('alipay_log.txt','验证失败 '.date('Y-m-d H:i:s'));
    //调试用，写文本函数记录程序运行情况是否正常
    //log_result ("这里写入想要调试的代码变量值，或其他运行的结果记录");
}