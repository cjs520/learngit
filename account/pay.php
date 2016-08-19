<?php
if($_POST && (int)$step==1)
{
    require_once 'include/order.class.php';
    
    $pay_money = floatval($pay_money);
    if($pay_money<=0) show_msg('充值金额错误');
    $pay_id = (int)$pay_id;
    $pay = $db->get_one("SELECT id,class_name,name,pay_desc,cfg FROM `{$tablepre}payment_table` WHERE id='$pay_id' LIMIT 1");
    if(!$pay) show_msg('检索不到指定付款方式');;
    if(!file_exists('include/payment/'. $pay['class_name'].'.class.php')) show_msg('指定付款方式不存在');
    
    require_once 'include/payment/'. $pay['class_name'].'.class.php';
    $ordersn = 'PM'.date('Ymdhis');
    $salt=rand(1000,9999);
    
    $o_payment = new  $pay['class_name'](unserialize($pay['cfg']));
    $code_form = $o_payment->pay_send($ordersn.$salt,$pay_money);
    order::create_pay_log($ordersn,$salt,$pay_money);
    $pay_money=currency($pay_money);
    
    require_once 'header.php';
    require_once template('member_pay_money');
}