<?php
if(!$m_check_uid) exit('请先登录');
if($mvm_member['isSupplier']<1) exit('请先申请商铺，再进行短信申请');
$money=(int)$num;
$var='mm_sms_apply_num_'.strval($money);
$n=(int)$$var;
if($n<=0) exit('您选择充值的数量不对，请重新选择');
if($mvm_member['member_money']<$money) exit('您的预付款不够，请先充值');

//扣除预付款
$money_left=$mvm_member['member_money']-$money;
$db->query("UPDATE `{$tablepre}member_table` SET member_money='$money_left' WHERE uid='$m_check_uid'");

$sql = "INSERT INTO `{$tablepre}money_table` SET
	    type='预付款',
        money_sess = '',
        money_id = '$m_check_id',
        money_add = '-$money',
        money_reason = '短信充值',
        money_left='$money_left',
        modify_ip = '$m_user_ip',
        approval_date = '$m_now_time',
        register_date = '$m_now_time'";
$db->query($sql);

//加上短信数量
$rtl=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE cf_name='mm_shop_sms' AND supplier_id='$m_check_uid'");
if($rtl)
{
    $n+=(int)$rtl['cf_value'];
    $db->query("DELETE FROM `{$tablepre}config` WHERE cf_name='mm_shop_sms' AND supplier_id='$m_check_uid'");
}
$db->query("INSERT INTO `{$tablepre}config` (cf_name,cf_value,supplier_id) VALUES ('mm_shop_sms','$n','$m_check_uid')");


exit('OK');