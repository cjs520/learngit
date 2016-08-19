<?php
if(!$m_check_uid) exit('ERR:您还未登录，请先登录');

$uid=(int)$uid;
$price=floatval($price);
if($price<=0) exit('ERR:竞拍金额错误');

$product=$db->get_one("SELECT uid,assure,goods_name,start_price,end_price,start_date,end_date,is_complete,bid_add,type 
                       FROM `{$tablepre}goods_auction` 
                       WHERE uid='$uid' AND supplier_id='$page_member_id' AND approval=1 
                       LIMIT 1");
if(!$product) exit('ERR:检索不到指定的竞拍商品');
if($m_now_time<$product['start_date']) exit('ERR:指定竞拍还未开始，请耐心等待');
if($product['is_complete'] || $product['end_date']<=$m_now_time) exit('ERR:指定竞拍活动已结束');
if($price<$product['start_price']) exit('ERR:竞拍金额不得低于起拍价');

$assure=$db->get_one("SELECT money FROM `{$tablepre}goods_auction_assure` WHERE m_uid='$m_check_uid' AND g_uid='$product[uid]' LIMIT 1");
if(!$assure) exit('ERR:您还未缴交保证金，无法参与竞拍');

$join=$db->get_one("SELECT price FROM `{$tablepre}goods_auction_join` WHERE g_uid='$product[uid]' ORDER BY register_date DESC LIMIT 1");
$target_price=$join?$join['price']+$product['bid_add']:$product['start_price']+$product['bid_add'];
if($price<$target_price) exit("ERR:本次出价不得低于".currency($target_price));

$db->query("INSERT INTO `{$tablepre}goods_auction_join` (g_uid,m_uid,m_id,price,register_date) 
            VALUES ('$product[uid]','$m_check_uid','$m_check_id','$price','$m_now_time')");
$db->free_result();

if($price>=$product['end_price'])    //达到一口价
{
    $db->query("UPDATE `{$tablepre}goods_auction` SET 
                is_complete=1, 
                end_date='$m_now_time' 
                WHERE uid='$product[uid]'");
    $ordersn='AU'.strval($m_now_time).rand(10,99);
    $info_row=array(
        'ordersn'=>$ordersn,
        'supplier_id'=>$page_member_id,
        'username'=>$m_check_id,
        'addtime'=>$m_now_time,
        'status'=>1,
        'goods_amount'=>$price
    );
    $insert_id=$db->insert("`{$tablepre}order_info`",$info_row);
    
    $og_row=array(
        'order_id'=>$insert_id,
        'g_uid'=>$product['uid'],
        'goods_name'=>$product['goods_name'],
        'buy_number'=>1,
        'buy_price'=>$price,
        'g_type'=>$product['type'],
        'goods_table'=>"{$tablepre}goods_auction",
        'module'=>'auction_detail',
        'register_date'=>$m_now_time,
        'status'=>0,
        'supplier_id'=>$page_member_id
    );
    $db->insert("`{$tablepre}order_goods`",$og_row);
    $db->free_result();
    
    $db->query("UPDATE `{$tablepre}goods_auction_assure` SET success='1' WHERE m_uid='$m_check_uid' AND g_uid='$product[uid]'");
    $db->free_result();
}

echo "OK:出价成功";
exit;