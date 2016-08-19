<?php
$g_uid=(int)$g_uid;
$goods_table=dhtmlchars($gt);
$html='';

$q=$db->query("SELECT order_id,buy_price,buy_number,register_date,goods_attr 
               FROM `{$tablepre}order_goods` 
               WHERE g_uid='$g_uid' AND goods_table='$goods_table' AND status=1 
               ORDER BY register_date DESC 
               LIMIT 10");
while ($rtl=$db->fetch_array($q))
{
    $rtl['register_date']=date('Y-m-d H:i:s',$rtl['register_date']);
    $rtl['buy_price']=currency($rtl['buy_price']);
    $order_info=$db->get_one("SELECT username FROM `{$tablepre}order_info` WHERE uid='$rtl[order_id]' LIMIT 1");
    
    $html.='<tr>';
    $html.='<td><a href="#" rel="friend" m_id="'.$order_info['username'].'" title="添加为好友">'.$order_info['username'].'</a></td>';
    $html.='<td class="orange fd">'.$rtl['buy_price'].'</td>';
    $html.='<td>'.$rtl[buy_number].'</td>';
    $html.='<td class="disb">'.$rtl['register_date'].'</td>';
    $html.='<td class="gray">'.$rtl['goods_attr'].'</td>';
    $html.='</tr>';
}
$db->free_result();

echo $html;
exit;