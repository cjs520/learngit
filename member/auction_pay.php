<?php
require_once 'include/pager.class.php';

$sn=dhtmlchars($sn);
if(strtolower(substr($sn,0,2))=='od') move_page("member.php?action=od_pay&sn=$sn");

$order_info=$db->get_one("SELECT uid,ordersn,addtime,address,consignee,zipcode,mobile,goods_amount,discount,sh_price,sh_uid,supplier_id,status 
                          FROM `{$tablepre}order_info` 
                          WHERE ordersn='$sn' AND username='$m_check_id' 
                          LIMIT 1");
if($_POST && (int)$step==1)
{
    require_once 'include/order.class.php';
    $arr_rtl=array('err'=>'','form_code'=>'');
    do
    {
        if(!$order_info)
        {
            $arr_rtl['err']='检索不到指定订单';
            break;
        }
        if($order_info['status']!=1)
        {
            $arr_rtl['err']='指定订单无法支付';
            break;
        }
        
        $province=dhtmlchars($province);
        $city=dhtmlchars($city);
        $county=dhtmlchars($county);
        $address=dhtmlchars($address);
        $consignee=dhtmlchars($consignee);
        $mobile=dhtmlchars($mobile);
        $zipcode=dhtmlchars($zipcode);
        $ship_uid=(int)$ship_uid;
        $ship_price=floatval($ship_price);
        $pay_id=(int)$pay_id;
        $pay_pass=dhtmlchars($pay_pass);
        $advance=(int)$advance;
        
        if(!$consignee || !$address || !$mobile || !$province)
        {
            $arr_rtl['err']='请填写收件人、收件地址、联系手机填写完整';
            break;
        }
        if($pay_id<=0)
        {
            $arr_rtl['err']='请选择正确的付款方式';
            break;
        }
        if($advance==1)
        {
            $m=$db->get_one("SELECT pay_pass FROM `{$tablepre}member_table` WHERE uid='$m_check_uid' LIMIT 1");
            if(md5($pay_pass)!=$m['pay_pass'])
            {
                $arr_rtl['err']='支付密码填写错误';
                break;
            }
        }
        $payment=$db->get_one("SELECT * FROM `{$tablepre}payment_table` WHERE id='$pay_id' LIMIT 1");
        if(!$payment)
        {
            $arr_rtl['err']='检索不到指定的付款方式';
            break;
        }
        $cls_file="include/payment/$payment[class_name].class.php";
        if(!$cls_file)
        {
            $arr_rtl['err']='指定的付款方式不存在';
            break;
        }
        require_once $cls_file;
        $o_payment=new $payment['class_name']($payment['cfg']);
        
        
        $row=array(
            'consignee'=>$consignee,
            'address'=>"$province $city $county $address",
            'mobile'=>$mobile,
            'zipcode'=>$zipcode,
            'sh_uid'=>$ship_uid,
            'sh_price'=>$ship_price,
            'pay_id'=>$pay_id,
            'pay_name'=>$payment['name']
        );
        $db->update("`{$tablepre}order_info`",$row," uid='$order_info[uid]' ");
        $db->free_result();
        
        $total_price=$order_info['goods_amount']+$ship_price;
        $salt=rand(1000,9999);
        $ordersn=$order_info['ordersn'];
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
	    order::create_pay_log($ordersn,$salt,$total_price);
	    
	    $arr_rtl['form_code']='<div class="fct pay_way"><p>支付方式：'.$payment['name'].'</p>'.
                              '<p>结算金额：<strong class="red f14">'.currency($total_price).'</strong></p>'.
                              '<p class="mt10">'.$pay_form.'</p>'.
                              '</div>';
    }while (0);
    
    exit(json_encode($arr_rtl));
}

if(!$order_info) show_msg('检索不到指定订单');
if($order_info['status']!=1) show_msg('指定订单无法支付');
$price=$order_info['goods_amount'];
$order_info['addtime']=date('Y-m-d H:i:s',$order_info['addtime']);
$order_info['goods_amount']=currency($order_info['goods_amount']);

$order_goods=$db->get_one("SELECT g_uid,goods_name FROM `{$tablepre}order_goods` WHERE order_id='$order_info[uid]' LIMIT 1");
if(!$order_goods) show_msg('检索不到订单商品，请联系管理员');

$product=$db->get_one("SELECT uid,goods_name,start_price,end_price,bid_add,assure,goods_status 
                       FROM `{$tablepre}goods_auction` 
                       WHERE uid='$order_goods[g_uid]' 
                       LIMIT 1");
$product['start_price']=currency($product['start_price']);
$product['url']=GetBaseUrl('auction_detail',$product['uid'],'action',$order_info['supplier_id']);
$product['end_price']=currency($product['end_price']);
$product['bid_add']=currency($product['bid_add']);
$product['assure']=currency($product['assure']);
if($product['goods_status'] & 4) $product['goods_kg']=0;
else
{
    $detail=$db->get_one("SELECT goods_kg FROM `{$tablepre}goods_auction_detail` WHERE g_uid='$product[uid]' LIMIT 1");
    $product['goods_kg']=intval($detail['goods_kg']);
}


$shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$order_info[supplier_id]' LIMIT 1");
$shop['url']=GetBaseUrl('index','','',$order_info['supplier_id']);

$cfg=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE cf_name='mm_client_qq1' AND supplier_id='$order_info[supplier_id]' LIMIT 1");
$shop['qq']=$cfg['cf_value'];


require 'header.php';
include template('member_auction_pay');