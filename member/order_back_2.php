<?php
require_once 'include/pic.class.php';
$uid=(int)$uid;
$order_back=$db->get_one("SELECT uid,status,og_uid,back_address,consignee 
                          FROM `{$tablepre}order_back` 
                          WHERE uid='$uid' AND m_uid='$m_check_uid' 
                          LIMIT 1");
if(!$order_back) show_msg('检索不到指定的退货申请');
if($order_back['status']!=2)  move_page("member.php?action=order_back_show&uid=$order_back[uid]");


$order_goods=$db->get_one("SELECT order_id,goods_name,g_uid,goods_table,module,status,buy_number,buy_point,buy_price,supplier_id,goods_attr 
                           FROM `{$tablepre}order_goods` WHERE uid='$order_back[og_uid]' LIMIT 1");
if(!$order_goods) show_msg('检索不到指定的订单商品');
if($order_goods['status']!=1) show_msg('指定的订单商品处理未成交状态，无法申请退货');
$order_goods['goods_attr']=str_replace('|','<br />',$order_goods['goods_attr']);
$order_goods['total_price']=$order_goods['buy_price']*$order_goods['buy_number'];
$order_goods['total_price_txt']=currency($order_goods['total_price']);
$order_goods['buy_price']=currency($order_goods['buy_price']);

$order_info=$db->get_one("SELECT ordersn FROM `{$tablepre}order_info` WHERE uid='$order_goods[order_id]' AND username='$m_check_id' LIMIT 1");
if(!$order_info) show_msg('检索不到指定订单');

if($_POST && (int)$step==1)
{
    $company=dhtmlchars($company);
    $delivery_code=dhtmlchars($delivery_code);
    $img='';
    if(!$company || !$delivery_code) show_msg('请将退货信息填写完整');
   
   if ($_FILES['img']['name']!='')
    {
        require_once 'include/upfile.class.php';
        $rowset = new upfile('gif,jpg,png,bmp','upload/back_order/');
        $img = $rowset->upload('img');
        $img=pic::PicZoom($img,350,350);
    }
    
    $row=array(
        'company'=>mb_substr($company,0,15),
        'delivery_code'=>mb_substr($delivery_code,0,30),
        'img'=>$img
    );
    $str_info2=serialize($row);
    $row=array(
        'status'=>3,
        'info2'=>$str_info2,
        'register_date'=>$m_now_time
    );
    $db->update("`{$tablepre}order_back`",$row," uid='$order_back[uid]' ");
    
    show_msg('退货信息提交成功，请等待卖家审核','member.php?action=my_back_order');
}

$product=$db->get_one("SELECT goods_name,goods_file1 FROM `$order_goods[goods_table]` WHERE uid='$order_goods[g_uid]' LIMIT 1");
if($product)
{
    $product['url']=GetBaseUrl($order_goods['module'],$order_goods['g_uid'],'action',$order_goods['supplier_id']);
    $product['goods_file1']=ProcImgPath($product['goods_file1']);
}

$shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$order_goods[supplier_id]' LIMIT 1");
if($shop)
{
    $shop['url']=GetBaseUrl('index','','',$order_goods['supplier_id']);
    $cfg=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$order_goods[supplier_id]' AND cf_name='mm_client_qq1' LIMIT 1");
    $shop['qq']=$cfg['cf_value'];
}

require 'header.php';
include template('member_order_back_2');