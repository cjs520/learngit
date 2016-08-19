<?php
require_once 'include/pager.class.php';
require_once 'include/order.class.php';

$arr_rtl=array('succ'=>0,'err'=>'');

$sn=dhtmlchars($sn);
$order_info=$db->get_one("SELECT uid,ordersn,addtime,address,consignee,zipcode,mobile,goods_amount,goods_rest_amount,status,goods_point,
                                 discount,sh_price,sh_uid,supplier_id 
                          FROM `{$tablepre}order_info` 
                          WHERE ordersn='$sn' AND username='$m_check_id' 
                          LIMIT 1");
if(!$order_info)
{
    $arr_rtl['err']='检索不到指定订单'.$sn;
    exit(json_encode($arr_rtl));
}
if($order_info['status']!=1 && !($order_info['status']==3 && $order_info['goods_rest_amount']>0))
{
    $arr_rtl['err']='订单'.$sn.'当前状态无法付款';
    exit(json_encode($arr_rtl));
}

if($cmd=='refresh')
{
    $arr_rtl['succ']=1;
    $arr_rtl['order_amount']=currency($order_info['goods_amount']+$order_info['sh_price']-$order_info['discount']);
    $arr_rtl['sh_price']=currency($order_info['sh_price']);
    $arr_rtl['discount']=$order_info['discount']==0?'无':currency($order_info['discount']);
}
else if($cmd=='pay')
{
    $advance=(int)$advance;
    $pay_pass=dhtmlchars($pay_pass);
    $pay_id=(int)$pay_id;
    $rest=dhtmlchars($rest);
    do
    {
        if($advance)
        {
            if(!$pay_pass)
            {
                $arr_rtl['err']='请输入支付密码';
                break;
            }
            $m=$db->get_one("SELECT pay_pass FROM `{$tablepre}member_table` WHERE uid='$m_check_uid' LIMIT 1");
            if($m['pay_pass']!=md5($pay_pass))
            {
                $arr_rtl['err']='支付密码错误';
                break;
            }
        }
        
        if($pay_id<=0)
        {
            $arr_rtl['err']='请选择付款方式';
            break;
        }
        $payment=$db->get_one("SELECT id,class_name,name,cfg FROM `{$tablepre}payment_table` WHERE id='$pay_id' AND supplier_id='0' LIMIT 1");
        if(!$payment)
        {
            $arr_rtl['err']='检索不到指定的付款方式';
            break;
        }
        if(!file_exists("include/payment/$payment[class_name].class.php"))
        {
            $arr_rtl['err']='指定的付款方式不存在';
            break;
        }
        $db->query("UPDATE `{$tablepre}order_info` SET pay_id='$payment[id]',pay_name='$payment[name]' WHERE uid='$order_info[uid]'");
        $db->free_result();
        require_once "include/payment/$payment[class_name].class.php";
        $o_payment=new $payment['class_name'](unserialize($payment['cfg']));

        $salt=rand(1000,9999);
        
        if($order_info['status']==3 && $order_info['goods_rest_amount']>0)
        {
            $total_price=$order_info['goods_rest_amount'];
            $total_point=0;
        }
        else
        {
            $total_price=$order_info['goods_amount']+$order_info['sh_price']-$order_info['discount'];
            $total_point=$order_info['goods_point'];
            if($mvm_member['member_point']<$total_point)    //不足以支付积分
	        {
	            $total_price+=($total_point-$mvm_member['member_point'])/intval($mm_buy_point);
	            $total_point=$mvm_member['member_point'];
	        }
        }
        
	    
	    if($advance)
	    {
	        if($mvm_member['member_money']<$total_price)
	        {
	            $total_price-=$mvm_member['member_money'];
	            $pay_form=$o_payment->pay_send($order_info['ordersn'].$salt,$total_price);
	        }
	        else
	        {
	            $pay_form="<a href='respond.php?sn=$order_info[ordersn]'><img src='images/pay/yufu.gif' /></a>";
	        }
	    }
	    else $pay_form=$o_payment->pay_send($order_info['ordersn'].$salt,$total_price);
	    order::create_pay_log($order_info['ordersn'],$salt,$total_price);
	    
	    $arr_rtl['form_code']='<div class="fct pay_way"><p>支付方式：'.$payment['name'].'</p>'.
                              '<p>结算金额：<strong class="red f14">'.currency($total_price).' + '.$total_point.' 积分</strong></p>'.
                              '<p class="mt10">'.$pay_form.'</p>'.
                              '</div>';
        $arr_rtl['succ']=1;
    }while (0);
}
exit(json_encode($arr_rtl));