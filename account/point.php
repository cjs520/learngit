<?php
if($_POST && (int)$step==2)    //积分购买
{
    require_once 'include/order.class.php';
    $buy_point=(int)$buy_point;
    $pay_id=(int)$pay_id;
    $advance=(int)$advance;
    $pay_pass=dhtmlchars($pay_pass);

    $mm_buy_point=intval($mm_buy_point);
    if($mm_buy_point<=0) exit('ERROR:积分购买功能暂时关闭，请联系管理员');

    if($buy_point<=0) exit('ERROR:请填写一个正确的购买积分数量');
    if($pay_id<=0) exit('ERROR:请选择正确的支付方式');
    if($advance)
    {
        $m=$db->get_one("SELECT pay_pass FROM `{$tablepre}member_table` WHERE uid='$m_check_uid' LIMIT 1");
        if(md5($pay_pass)!=$m['pay_pass']) exit('ERROR:支付密码错误');
    }
    
    $pay = $db->get_one("SELECT * FROM `{$tablepre}payment_table` WHERE id='$pay_id' AND supplier_id='0'");
    if(!$pay) exit('ERROR:支付方式错误，请重新选择');
    if(!file_exists('include/payment/'. $pay['class_name'].'.class.php')) exit('ERROR:指定的支付方式不存在');

    $total_money=$total_price=round($buy_point/$mm_buy_point,2);
    $ordersn='BP'.date('YmdHis').strval(rand(10,99));
    $salt=rand(1000,9999);
    
    $code_form='';
    require_once 'include/payment/'. $pay['class_name'].'.class.php';
    $o_payment = new  $pay['class_name'](unserialize($pay['cfg']));
    if($advance==1)
    {
        if($mvm_member['member_money']<$total_price)
        {
            $total_price-=$mvm_member['member_money'];
            $pay_form=$o_payment->pay_send($ordersn.$salt,$total_price);
        }
        else
        {
            $pay_form="<a href='respond.php?sn=$ordersn'><img src='images/pay/yufu.gif' /></a>";
        }
    }
    else $pay_form=$o_payment->pay_send($ordersn.$salt,$total_price);
    order::create_pay_log($ordersn,$salt,$total_price,$total_money);
    
    $pay_form .= "<br />您当前需支付".currency($total_money);
    exit($pay_form);
}

$total_count = $db->counter("{$tablepre}point_table"," `point_id` = '$m_check_id'");
require_once 'include/pager.class.php';
$page = $page ? (int)$page:1;
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q = $db->query("SELECT register_date,point_sess,point_add,approval_date,point_reason,type,point_id,point_left,other_info 
                 FROM `{$tablepre}point_table`
                 WHERE `point_id` = '$m_check_id' 
                 ORDER BY register_date DESC 
                 LIMIT $from_record, $list_num");
while($rtl = $db->fetch_array($q))
{
    $rtl['reg_date'] = date('Y-m-d',$rtl['register_date']);
    if(!$rtl['point_sess']) $rtl['point_sess']=$rtl['register_date'];
    if(!$rtl['other_info']) $rtl['other_info']='无附加信息';
    
    $rtl['point_add']<0?$rtl['minus']=$rtl['point_add']:$rtl['add']=$rtl['point_add'];
    $rtl['point_left']=round($rtl['point_left'],0);

    if($rtl['approval_date']>0) $rtl['status']='成功';
    else if($rtl['approval_date']==0) $rtl['status']='待审核';
    else if($rtl['approval_date']==-1) $rtl['status']='已撤销';
    else $rtl['status']='未知';
    $point[] = $rtl;
}
$page_list = $rowset->link('account.php?action=point&page=');

$not_allow_list=array('integral','COD');
$payment_list= $cache->get_cache('payment');
foreach ($payment_list as $key=>$val)
if (in_array($val['class_name'],$not_allow_list)) unset($payment_list[$key]);

require 'header.php';
require_once template('member_point');