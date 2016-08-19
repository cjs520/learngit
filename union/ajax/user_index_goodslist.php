<?php
$arr_rtl=array();
$arr_cat_uid=explode(',',$cat_uid);
$arr_goods_type=explode(',',$goods_type);
foreach ($arr_cat_uid as $key=>$val)
{
    $val=(int)$val;
    if($val<=0)
    {
        unset($arr_cat_uid[$key]);
        unset($arr_goods_type[$key]);
    }
    else $arr_cat_uid[$key]=$val;
}

foreach ($arr_cat_uid as $key=>$val)
{
    $arr_children_uid=get_category_chidlren($val);
    $str_cat_uid=implode(',',$arr_children_uid);
    $goods_table=goods_table($arr_goods_type[$key]);
    $detail_script=goods_detail_script($arr_goods_type[$key]);
    
    $arr_g=array();
    $price_field='goods_sale_price';
    if($arr_goods_type[$key]==7) $price_field='start_price';
    
    $order_sql="ORDER BY register_date DESC ";
    if($arr_goods_type[$key]==7) $order_sql="ORDER BY start_date DESC";
    
    $filter_sql='';
    if($arr_goods_type[$key]==7) $filter_sql=" AND approval=1";
    
    $q=$db->query("SELECT uid,goods_name,goods_file1,{$price_field} AS goods_sale_price,supplier_id,type 
                   FROM `$goods_table`  
                   WHERE supplier_cat IN ($str_cat_uid) AND supplier_id='$page_member_id' $filter_sql
                   $order_sql 
                   LIMIT 4");
    while($rtl=$db->fetch_array($q))
    {
        if(!$rtl['goods_file1'] || !file_exists($rtl['goods_file1'])) $rtl['goods_file1']='images/noimages/noproduct.jpg';
        $rtl['goods_sale_price']=$rtl['type']==8?'展示商品':currency($rtl['goods_sale_price']);
        $rtl['url']=GetBaseUrl($detail_script,$rtl['uid'],'action',$rtl['supplier_id']);
        $arr_g[]=$rtl;
    }
    $db->free_result();
    $arr_rtl[$val][$arr_goods_type[$key]]=$arr_g;
}


echo json_encode($arr_rtl);
exit;