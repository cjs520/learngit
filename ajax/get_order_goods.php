<?php
$order_goods=array();
$order_id=(int)$order_id;
$uid=(int)$uid;
$comment_allow=$db->get_one("SELECT uid FROM `{$tablepre}comment_allow` WHERE uid='$uid' AND roll='1' AND `from`='$m_check_uid' LIMIT 1");
if(!$comment_allow) exit(json_encode(array('error'=>'您不能对商家进行评价')));

$order=$db->get_one("SELECT uid,ordersn,supplier_id,username FROM `{$tablepre}order_info` WHERE uid='$order_id' LIMIT 1");
if(!$order || $order['username']!=$m_check_id) exit(json_encode(array('error'=>'检索不到指定订单')));

$q=$db->query("SELECT og.goods_name,og.goods_id,gt.goods_file1 FROM `{$tablepre}order_goods` og
               LEFT JOIN `{$tablepre}goods_table` gt 
               ON og.goods_id=gt.uid 
               WHERE og.order_id='$order[uid]'");
while($rtl=$db->fetch_array($q))
{
    if($rtl['goods_file1'] && file_exists(ProcImgPath($rtl['goods_file1']))) $rtl['goods_file1']=ProcImgPath($rtl['goods_file1']);
    else $rtl['goods_file1']='images/noimages/noproduct.jpg';

    $rtl['url']=GetBaseUrl('product',$rtl['goods_id'],'action',$order['supplier_id']);
    $order_goods[]=$rtl;
}

exit(json_encode($order_goods));