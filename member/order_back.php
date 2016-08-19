<?php
require_once 'include/pic.class.php';
$og_uid=(int)$og_uid;
$order_back=$db->get_one("SELECT uid,status FROM `{$tablepre}order_back` WHERE og_uid='$og_uid' LIMIT 1");
do
{
    if(!$order_back) break;
    if(in_array($order_back['status'],array(2,4)))  move_page("member.php?action=order_back_2&uid=$order_back[uid]");
    else move_page("member.php?action=order_back_show&uid=$order_back[uid]");
}while (0);

$order_goods=$db->get_one("SELECT order_id,goods_name,g_uid,goods_table,module,status,buy_number,buy_point,buy_price,supplier_id,goods_attr 
                           FROM `{$tablepre}order_goods` WHERE uid='$og_uid' LIMIT 1");
if(!$order_goods) show_msg('检索不到指定的订单商品');
if($order_goods['status']!=1) show_msg('指定的订单商品处理未成交状态，无法申请退货');

$order_info=$db->get_one("SELECT ordersn,sh_price FROM `{$tablepre}order_info` WHERE uid='$order_goods[order_id]' AND username='$m_check_id' LIMIT 1");
if(!$order_info) show_msg('检索不到指定订单');

$order_goods['goods_attr']=str_replace('|','<br />',$order_goods['goods_attr']);
$order_goods['total_price']=$order_goods['buy_price']*$order_goods['buy_number']+$order_info['sh_price'];
$order_goods['total_price_txt']=currency($order_goods['total_price']);
$order_goods['buy_price_total']=currency($order_goods['buy_price']*$order_goods['buy_number']);
$order_goods['buy_price']=currency($order_goods['buy_price']);

if($_POST && (int)$step==1)
{
   $reason=mb_substr(trim(strip_tags($reason)),0,50);
   $money=floatval($money);
   $memo=mb_substr(strip_tags($memo),0,200);
   $img='';
   
   if($mvm_member['member_point']<intval($order_goods['total_price'])) show_msg('您的积分不足以索回，无法申请退货');
   if(!$reason) show_msg('请填写退货原因');
   if($money<=0 || $money>$order_goods['total_price']) show_msg('退款金额错误');
   if ($_FILES['img']['name']!='')
    {
        require_once 'include/upfile.class.php';
        $rowset = new upfile('gif,jpg,png,bmp','upload/back_order/');
        $img = $rowset->upload('img');
       // $img=pic::PicZoom($img,350,350);
    }
    
    add_score($m_check_uid,-intval($money),'退货',"$order_info[ordersn]订单退货",$order_info['ordersn'],"退货商品：$order_goods[goods_name]");
    $row=array(
        'reason'=>$reason,
        'money'=>$money,
        'memo'=>$memo,
        'img'=>$img
    );
    $str_info1=serialize($row);
    $row=array(
        'ordersn'=>$order_info['ordersn'],
        'og_uid'=>$og_uid,
        'g_uid'=>$order_goods['g_uid'],
        'goods_table'=>$order_goods['goods_table'],
        'module'=>$order_goods['module'],
        'status'=>1,
        'info1'=>$str_info1,
        'register_date'=>$m_now_time,
        'm_uid'=>$m_check_uid,
        'm_id'=>$m_check_id,
        'supplier_id'=>$order_goods['supplier_id']
    );
    $db->insert("`{$tablepre}order_back`",$row);
    show_msg('申请成功','member.php?action=my_back_order');
   
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
include template('member_order_back');