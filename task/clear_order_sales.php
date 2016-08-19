<?php
$arr_goods_statistics=array();
$arr_shop_statistics=array();
$arr_consume_statistics=array();

$week_expire=$today_timestamp-7*24*3600;
$month_expire=$today_timestamp-30*24*3600;

//商品销量统计
$q=$db->query("SELECT uid,username FROM `{$tablepre}order_info` WHERE status IN (3,4,5)");
while ($rtl=$db->fetch_array($q))
{
    $q_tmp=$db->query("SELECT g_uid,goods_table,module,goods_name,buy_price,buy_number,supplier_id,register_date 
                       FROM `{$tablepre}order_goods` 
                       WHERE order_id='$rtl[uid]'");
    while ($rtl_tmp=$db->fetch_array($q_tmp))
    {
        $idx=$rtl_tmp['goods_table'].'|'.$rtl_tmp['g_uid'];
        $arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_money']=floatval($arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_money'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
        $arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_num']=intval($arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_num'])+$rtl_tmp['buy_number'];
        
        $arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['consume']=floatval($arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['consume'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
        $arr_consume_statistics[0][$rtl['username']]['consume']=floatval($arr_consume_statistics[0][$rtl['username']]['consume'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
        $arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['buy_num']=intval($arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['buy_num'])+$rtl_tmp['buy_number'];
        $arr_consume_statistics[0][$rtl['username']]['buy_num']=intval($arr_consume_statistics[0][$rtl['username']]['buy_num'])+$rtl_tmp['buy_number'];
        
        $arr_goods_statistics[$idx]['total_sale']=intval($arr_goods_statistics[$idx]['total_sale'])+$rtl_tmp['buy_number'];
        $arr_goods_statistics[$idx]['total_sale_money']=floatval($arr_goods_statistics[$idx]['total_sale_money'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
        $arr_goods_statistics[$idx]['supplier_id']=$rtl_tmp['supplier_id'];
        $arr_goods_statistics[$idx]['goods_name']=$rtl_tmp['goods_name'];
        $arr_goods_statistics[$idx]['module']=$rtl_tmp['module'];
        
        if($rtl_tmp['register_date']>=$week_expire)
        {
            $arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_money_7']=floatval($arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_money_7'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
            $arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_num_7']=intval($arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_num_7'])+$rtl_tmp['buy_number'];
            
            $arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['consume_7']=floatval($arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['consume_7'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
            $arr_consume_statistics[0][$rtl['username']]['consume_7']=floatval($arr_consume_statistics[0][$rtl['username']]['consume_7'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
            $arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['buy_num_7']=intval($arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['buy_num_7'])+$rtl_tmp['buy_number'];
            $arr_consume_statistics[0][$rtl['username']]['buy_num_7']=intval($arr_consume_statistics[0][$rtl['username']]['buy_num_7'])+$rtl_tmp['buy_number'];
        
            $arr_goods_statistics[$idx]['total_sale_7']=intval($arr_goods_statistics[$idx]['total_sale_7'])+$rtl_tmp['buy_number'];
            $arr_goods_statistics[$idx]['total_sale_money_7']=floatval($arr_goods_statistics[$idx]['total_sale_money_7'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
        }
        if($rtl_tmp['register_date']>=$month_expire)
        {
            $arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_money_30']=floatval($arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_money_30'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
            $arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_num_30']=intval($arr_shop_statistics[$rtl_tmp['supplier_id']]['sale_num_30'])+$rtl_tmp['buy_number'];
            
            $arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['consume_30']=floatval($arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['consume_30'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
            $arr_consume_statistics[0][$rtl['username']]['consume_30']=floatval($arr_consume_statistics[0][$rtl['username']]['consume_30'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
            $arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['buy_num_30']=intval($arr_consume_statistics[$rtl_tmp['supplier_id']][$rtl['username']]['buy_num_30'])+$rtl_tmp['buy_number'];
            $arr_consume_statistics[0][$rtl['username']]['buy_num_30']=intval($arr_consume_statistics[0][$rtl['username']]['buy_num_30'])+$rtl_tmp['buy_number'];
            
            $arr_goods_statistics[$idx]['total_sale_30']=intval($arr_goods_statistics[$idx]['total_sale_30'])+$rtl_tmp['buy_number'];
            $arr_goods_statistics[$idx]['total_sale_money_30']=floatval($arr_goods_statistics[$idx]['total_sale_money_30'])+$rtl_tmp['buy_price']*$rtl_tmp['buy_number'];
        }
        
        
        unset($rtl_tmp);
    }
    $db->free_result(1);
    unset($rtl);
}
$db->free_result();

//商铺商品数量统计
$q=$db->query("SELECT COUNT(*) AS cnt,supplier_id FROM `{$tablepre}goods_table` GROUP BY supplier_id");
while ($rtl=$db->fetch_array($q))
{
    $arr_shop_statistics[$rtl['supplier_id']]['goods_num']=intval($arr_shop_statistics[$rtl['supplier_id']]['goods_num'])+$rtl['cnt'];
    unset($rtl);
}
$db->free_result();
$q=$db->query("SELECT COUNT(*) AS cnt,supplier_id FROM `{$tablepre}goods_show` GROUP BY supplier_id");
while ($rtl=$db->fetch_array($q))
{
    $arr_shop_statistics[$rtl['supplier_id']]['goods_num']=intval($arr_shop_statistics[$rtl['supplier_id']]['goods_num'])+$rtl['cnt'];
    unset($rtl);
}
$db->free_result();
$q=$db->query("SELECT COUNT(*) AS cnt,supplier_id FROM `{$tablepre}goods_auction` GROUP BY supplier_id");
while ($rtl=$db->fetch_array($q))
{
    $arr_shop_statistics[$rtl['supplier_id']]['goods_num']=intval($arr_shop_statistics[$rtl['supplier_id']]['goods_num'])+$rtl['cnt'];
    unset($rtl);
}
$db->free_result();
$q=$db->query("SELECT COUNT(*) AS cnt,supplier_id FROM `{$tablepre}goods_onsale` GROUP BY supplier_id");
while ($rtl=$db->fetch_array($q))
{
    $arr_shop_statistics[$rtl['supplier_id']]['goods_num']=intval($arr_shop_statistics[$rtl['supplier_id']]['goods_num'])+$rtl['cnt'];
    unset($rtl);
}
$db->free_result();
$q=$db->query("SELECT COUNT(*) AS cnt,supplier_id FROM `{$tablepre}goods_group` GROUP BY supplier_id");
while ($rtl=$db->fetch_array($q))
{
    $arr_shop_statistics[$rtl['supplier_id']]['goods_num']=intval($arr_shop_statistics[$rtl['supplier_id']]['goods_num'])+$rtl['cnt'];
    unset($rtl);
}
$db->free_result();
$q=$db->query("SELECT COUNT(*) AS cnt,supplier_id FROM `{$tablepre}goods_change` GROUP BY supplier_id");
while ($rtl=$db->fetch_array($q))
{
    $arr_shop_statistics[$rtl['supplier_id']]['goods_num']=intval($arr_shop_statistics[$rtl['supplier_id']]['goods_num'])+$rtl['cnt'];
    unset($rtl);
}
$db->free_result();

//退货统计
$q=$db->query("SELECT register_date,supplier_id,g_uid,goods_table,module,supplier_id FROM `{$tablepre}order_back`");
while ($rtl=$db->fetch_array($q))
{
    $g=$db->get_one("SELECT goods_name FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
    if(!$g) continue;
    
    $idx=$rtl['goods_table'].'|'.$rtl['g_uid'];
    $arr_shop_statistics[$rtl['supplier_id']]['back_num']=intval($arr_shop_statistics[$rtl['supplier_id']]['back_num'])+1;
    
    $arr_goods_statistics[$idx]['back_num']=intval($arr_goods_statistics[$idx]['back_num'])+1;
    $arr_goods_statistics[$idx]['supplier_id']=$rtl['supplier_id'];
    $arr_goods_statistics[$idx]['module']=$rtl['module'];
    $arr_goods_statistics[$idx]['goods_name']=$g['goods_name'];
    
    if($rtl['register_date']>=$week_expire)
    {
        $arr_shop_statistics[$rtl['supplier_id']]['back_num_7']=intval($arr_shop_statistics[$rtl['supplier_id']]['back_num_7'])+1;
        $arr_goods_statistics[$idx]['back_num_7']=intval($arr_goods_statistics[$idx]['back_num_7'])+1;
    }
    if($rtl['register_date']>=$month_expire)
    {
        $arr_shop_statistics[$rtl['supplier_id']]['back_num_30']=intval($arr_shop_statistics[$rtl['supplier_id']]['back_num_30'])+1;
        $arr_goods_statistics[$idx]['back_num_30']=intval($arr_goods_statistics[$idx]['back_num_30'])+1;
    }
    unset($g);
    unset($rtl);
}
$db->free_result();

//点击量统计
$q=$db->query("SELECT uid,supplier_id,goods_name,goods_hit FROM `{$tablepre}goods_table`");
while ($rtl=$db->fetch_array($q))
{
    $idx="{$tablepre}goods_table|$rtl[uid]";
    $arr_goods_statistics[$idx]['goods_hit']=$rtl['goods_hit'];
    $arr_goods_statistics[$idx]['supplier_id']=$rtl['supplier_id'];
    $arr_goods_statistics[$idx]['module']='product';
    $arr_goods_statistics[$idx]['goods_name']=$rtl['goods_name'];
    
    unset($rtl);
}
$db->free_result();

$q=$db->query("SELECT uid,supplier_id,goods_name,goods_hit FROM `{$tablepre}goods_show`");
while ($rtl=$db->fetch_array($q))
{
    $idx="{$tablepre}goods_show|$rtl[uid]";
    $arr_goods_statistics[$idx]['goods_hit']=$rtl['goods_hit'];
    $arr_goods_statistics[$idx]['supplier_id']=$rtl['supplier_id'];
    $arr_goods_statistics[$idx]['module']='product';
    $arr_goods_statistics[$idx]['goods_name']=$rtl['goods_name'];
    
    unset($rtl);
}
$db->free_result();

$q=$db->query("SELECT uid,supplier_id,goods_name,goods_hit FROM `{$tablepre}goods_auction`");
while ($rtl=$db->fetch_array($q))
{
    $idx="{$tablepre}goods_auction|$rtl[uid]";
    $arr_goods_statistics[$idx]['goods_hit']=$rtl['goods_hit'];
    $arr_goods_statistics[$idx]['supplier_id']=$rtl['supplier_id'];
    $arr_goods_statistics[$idx]['module']='auction_detail';
    $arr_goods_statistics[$idx]['goods_name']=$rtl['goods_name'];
    
    unset($rtl);
}
$db->free_result();

$q=$db->query("SELECT uid,supplier_id,goods_name,goods_hit FROM `{$tablepre}goods_change`");
while ($rtl=$db->fetch_array($q))
{
    $idx="{$tablepre}goods_change|$rtl[uid]";
    $arr_goods_statistics[$idx]['goods_hit']=$rtl['goods_hit'];
    $arr_goods_statistics[$idx]['supplier_id']=$rtl['supplier_id'];
    $arr_goods_statistics[$idx]['module']='changegd_detail';
    $arr_goods_statistics[$idx]['goods_name']=$rtl['goods_name'];
    
    unset($rtl);
}
$db->free_result();

$q=$db->query("SELECT uid,supplier_id,goods_name,goods_hit FROM `{$tablepre}goods_group`");
while ($rtl=$db->fetch_array($q))
{
    $idx="{$tablepre}goods_group|$rtl[uid]";
    $arr_goods_statistics[$idx]['goods_hit']=$rtl['goods_hit'];
    $arr_goods_statistics[$idx]['supplier_id']=$rtl['supplier_id'];
    $arr_goods_statistics[$idx]['module']='group_detail';
    $arr_goods_statistics[$idx]['goods_name']=$rtl['goods_name'];
    
    unset($rtl);
}
$db->free_result();

$q=$db->query("SELECT uid,supplier_id,goods_name,goods_hit FROM `{$tablepre}goods_onsale`");
while ($rtl=$db->fetch_array($q))
{
    $idx="{$tablepre}goods_onsale|$rtl[uid]";
    $arr_goods_statistics[$idx]['goods_hit']=$rtl['goods_hit'];
    $arr_goods_statistics[$idx]['supplier_id']=$rtl['supplier_id'];
    $arr_goods_statistics[$idx]['module']='salegd_detail';
    $arr_goods_statistics[$idx]['goods_name']=$rtl['goods_name'];
    
    unset($rtl);
}
$db->free_result();

//商家服务态度统计
$arr_service=array();
$q=$db->query("SELECT to_id,comment FROM `{$tablepre}order_user_comment` WHERE roll='0'");
while ($rtl=$db->fetch_array($q))
{
    if(isset($arr_service[$rtl['to_id']])) $arr_service[$rtl['to_id']]=array(0,0,0,0,'times'=>0);
    $arr_tmp=explode('|',$rtl['comment']);
    if(sizeof($arr_tmp)==4)
    {
        $arr_service[$rtl['to_id']]['times']++;
        $arr_service[$rtl['to_id']][0]+=(int)$arr_tmp[0];
        $arr_service[$rtl['to_id']][1]+=(int)$arr_tmp[1];
        $arr_service[$rtl['to_id']][2]+=(int)$arr_tmp[2];
        $arr_service[$rtl['to_id']][3]+=(int)$arr_tmp[3];
    }
    unset($arr_tmp);
    unset($rtl);
}
$db->free_result();

foreach ($arr_service as $key=>$val)
{
    $shop=$db->get_one("SELECT m_uid FROM `{$tablepre}member_shop` WHERE m_id='$key' LIMIT 1");
    if($shop)
    {
        $arr_shop_statistics[$shop['m_uid']]['match']+=intval($val[0]/$val['times']);
        $arr_shop_statistics[$shop['m_uid']]['seller_service']+=intval($val[1]/$val['times']);
        $arr_shop_statistics[$shop['m_uid']]['seller_ship']+=intval($val[2]/$val['times']);
        $arr_shop_statistics[$shop['m_uid']]['ship_service']+=intval($val[3]/$val['times']);
    }
    unset($shop);
}
unset($arr_service);

//写回数据表
$db->query("TRUNCATE TABLE `{$tablepre}goods_statistics`");
$db->query("TRUNCATE TABLE `{$tablepre}shop_statistics`");
$db->query("TRUNCATE TABLE `{$tablepre}member_consume_statistics`");
$db->free_result();

foreach ($arr_goods_statistics as $key=>$val)
{
    $arr_tmp=explode('|',$key);
    $val['goods_table']=$arr_tmp[0];
    $val['g_uid']=$arr_tmp[1];
    $val['goods_name']=addslashes($val['goods_name']);
    $db->insert("`{$tablepre}goods_statistics`",$val);
    unset($arr_tmp);
}
$db->free_result();
unset($arr_goods_statistics);

foreach ($arr_shop_statistics as $key=>$val)
{
    $val['m_uid']=$key;
    $db->insert("`{$tablepre}shop_statistics`",$val);
}
$db->free_result();
unset($arr_shop_statistics);

foreach ($arr_consume_statistics as $supplier_id=>$val)
{
    foreach ($val as $m_id=>$val1)
    {
        $val1['supplier_id']=$supplier_id;
        $val1['m_id']=$m_id;
        $db->insert("`{$tablepre}member_consume_statistics`",$val1);
    }
}
$db->free_result();
unset($arr_consume_statistics);

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);
?>