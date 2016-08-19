<?php
$q=$db->query("SELECT uid,assure,goods_name,start_price,end_price,start_date,end_date,is_complete,bid_add,type,supplier_id 
               FROM `{$tablepre}goods_auction` 
               WHERE end_date<='$m_now_time' AND is_complete='0'");
while ($rtl=$db->fetch_array($q))
{
    $join=$db->get_one("SELECT m_uid,m_id,price FROM `{$tablepre}goods_auction_join` WHERE g_uid='$rtl[uid]' ORDER BY register_date DESC LIMIT 1");
    if($join)    //有参与人
    {
        $ordersn='AU'.strval($m_now_time).rand(10,99);
        $info_row=array(
            'ordersn'=>$ordersn,
            'supplier_id'=>$rtl['supplier_id'],
            'username'=>$join['m_id'],
            'addtime'=>$m_now_time,
            'status'=>1,
            'goods_amount'=>$join['price']
        );
        $insert_id=$db->insert("`{$tablepre}order_info`",$info_row);

        $og_row=array(
            'order_id'=>$insert_id,
            'g_uid'=>$rtl['uid'],
            'goods_name'=>$rtl['goods_name'],
            'buy_number'=>1,
            'buy_price'=>$join['price'],
            'g_type'=>$rtl['type'],
            'goods_table'=>"{$tablepre}goods_auction",
            'module'=>'auction_detail',
            'register_date'=>$m_now_time,
            'status'=>0,
            'supplier_id'=>$rtl['supplier_id']
        );
        $db->insert("`{$tablepre}order_goods`",$og_row);

        $db->query("UPDATE `{$tablepre}goods_auction_assure` SET success='1' WHERE m_uid='$join[m_uid]' AND g_uid='$rtl[uid]'");
        $db->free_result(1);
    }
    
    $db->query("UPDATE `{$tablepre}goods_auction` SET 
                is_complete='1',
                end_date='$m_now_time' 
                WHERE uid='$rtl[uid]'");
    $db->free_result(1);
    unset($rtl);
    unset($join);
}
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>