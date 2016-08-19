<?php
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';

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
    $children_uids=get_chidldren_uids($val,$uid_2_pid,$cat);
    array_push($children_uids,$val);
    $str_cat_uid=implode(',',$children_uids);
    $goods_table=goods_table($arr_goods_type[$key]);
    $detail_script=goods_detail_script($arr_goods_type[$key]);
    
    $arr_g=array();
    $price_field='goods_sale_price';
    if($arr_goods_type[$key]==7) $price_field='start_price';
    $q=$db->query("SELECT gt.uid,gt.goods_name,gt.goods_file1,gt.{$price_field} AS goods_sale_price,gt.supplier_id,gt.type 
                   FROM `$goods_table` gt 
                   LEFT JOIN `{$tablepre}member_shop` ms 
                   ON gt.supplier_id=ms.m_uid 
                   WHERE goods_category IN ($str_cat_uid) AND ms.isSupplier=3 
                   ORDER BY gt.uid DESC 
                   LIMIT 4");
    while($rtl=$db->fetch_array($q))
    {
        $rtl['goods_file1']=ProcImgPath($rtl['goods_file1']);
        $rtl['goods_sale_price']=$rtl['type']==8?'展示商品':currency($rtl['goods_sale_price']);
        $rtl['url']=GetBaseUrl($detail_script,$rtl['uid'],'action',$rtl['supplier_id']);
        $arr_g[]=$rtl;
    }
    $db->free_result();
    $arr_rtl[$val][$arr_goods_type[$key]]=$arr_g;
}


echo json_encode($arr_rtl);
exit;