<?php
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';

$arr_cat_count=array();
$arr_brand_count=array();

$q=$db->query("SELECT uid FROM `{$tablepre}category` WHERE supplier_id=0");
while ($rtl=$db->fetch_array($q))
{
    $arr_cat_count[$rtl['uid']]=array(
        'cat_uid'=>$rtl['uid'],
        'goods_num'=>0,
        'show_num'=>0,
        'group_num'=>0,
        'onsale_num'=>0,
        'change_num'=>0,
        'brand_uids'=>array()
    );
    unset($rtl);
}
$db->free_result();

$q=$db->query("SELECT id FROM `{$tablepre}brand_table` WHERE isCheck=1");
while ($rtl=$db->fetch_array($q))
{
    $arr_brand_count[$rtl['id']]=array(
        'brand_uid'=>$rtl['id'],
        'goods_num'=>0,
        'show_num'=>0,
        'group_num'=>0,
        'onsale_num'=>0,
        'change_num'=>0
    );
    unset($rtl);
}
$db->free_result();

$db->query("TRUNCATE TABLE `{$tablepre}cat_statistics`");
$db->query("TRUNCATE TABLE `{$tablepre}brand_statistics`");
$db->free_result();

//统计销售商品 和 预订商品
$q=$db->query("SELECT gt.goods_category,gt.goods_brand,gt.type FROM `{$tablepre}goods_table` gt 
               LEFT JOIN `{$tablepre}member_shop` ms 
               ON gt.supplier_id=ms.m_uid 
               WHERE ms.isSupplier=3");
while ($rtl=$db->fetch_array($q))
{
    if($arr_brand_count[$rtl['goods_brand']])
    {
        $arr_brand_count[$rtl['goods_brand']]['goods_num']++;
        if($rtl['type']==9) $arr_brand_count[$rtl['goods_brand']]['preorder_num']++;
    }
    
    $arr_parent=get_parents($rtl['goods_category'],$uid_2_pid);
    foreach ($arr_parent as $val)
    {
        if(!$arr_cat_count[$val]) continue;
        $arr_cat_count[$val]['goods_num']++;
        if($rtl['type']==9) $arr_cat_count[$val]['preorder_num']++;
        if($rtl['goods_brand']>0 && !in_array($rtl['goods_brand'],$arr_cat_count[$val]['brand_uids'])) $arr_cat_count[$val]['brand_uids'][]=$rtl['goods_brand'];
    }
    unset($rtl);
}
$db->free_result();

//统计展示商品
$q=$db->query("SELECT gs.goods_category,gs.goods_brand FROM `{$tablepre}goods_show` gs 
               LEFT JOIN `{$tablepre}member_shop` ms 
               ON gs.supplier_id=ms.m_uid 
               WHERE ms.isSupplier=3");
while ($rtl=$db->fetch_array($q))
{
    if($arr_brand_count[$rtl['goods_brand']]) $arr_brand_count[$rtl['goods_brand']]['show_num']++;
    
    $arr_parent=get_parents($rtl['goods_category'],$uid_2_pid);
    foreach ($arr_parent as $val)
    {
        if(!$arr_cat_count[$val]) continue;
        $arr_cat_count[$val]['show_num']++;
        if($rtl['goods_brand']>0 && !in_array($rtl['goods_brand'],$arr_cat_count[$val]['brand_uids'])) $arr_cat_count[$val]['brand_uids'][]=$rtl['goods_brand'];
    }
    unset($rtl);
}
$db->free_result();

//统计团购
$q=$db->query("SELECT gg.goods_category,gg.goods_brand FROM `{$tablepre}goods_group` gg 
               LEFT JOIN `{$tablepre}member_shop` ms 
               ON gg.supplier_id=ms.m_uid 
               WHERE gg.approval=1 AND ms.isSupplier=3");
while ($rtl=$db->fetch_array($q))
{
    if($arr_brand_count[$rtl['goods_brand']]) $arr_brand_count[$rtl['goods_brand']]['group_num']++;
    
    $arr_parent=get_parents($rtl['goods_category'],$uid_2_pid);
    foreach ($arr_parent as $val)
    {
        if($arr_cat_count[$val]) $arr_cat_count[$val]['group_num']++;
    }
    unset($rtl);
}
$db->free_result();

//统计促销
$q=$db->query("SELECT go.goods_category,go.goods_brand FROM `{$tablepre}goods_onsale` go 
               LEFT JOIN `{$tablepre}member_shop` ms 
               ON go.supplier_id=ms.m_uid 
               WHERE ms.isSupplier=3");
while ($rtl=$db->fetch_array($q))
{
    if($arr_brand_count[$rtl['goods_brand']]) $arr_brand_count[$rtl['goods_brand']]['onsale_num']++;
    
    $arr_parent=get_parents($rtl['goods_category'],$uid_2_pid);
    foreach ($arr_parent as $val)
    {
        if($arr_cat_count[$val]) $arr_cat_count[$val]['onsale_num']++;
    }
    unset($rtl);
}
$db->free_result();

//统计积分换购
$q=$db->query("SELECT gc.goods_category,gc.goods_brand FROM `{$tablepre}goods_change` gc 
               LEFT JOIN `{$tablepre}member_shop` ms 
               ON gc.supplier_id=ms.m_uid 
               WHERE gc.approval=1 AND ms.isSupplier=3");
while ($rtl=$db->fetch_array($q))
{
    if($arr_brand_count[$rtl['goods_brand']]) $arr_brand_count[$rtl['goods_brand']]['change_num']++;
    
    $arr_parent=get_parents($rtl['goods_category'],$uid_2_pid);
    foreach ($arr_parent as $val)
    {
        if($arr_cat_count[$val]) $arr_cat_count[$val]['change_num']++;
    }
    unset($rtl);
}
$db->free_result();

//统计拍卖商品
$q=$db->query("SELECT ga.goods_category,ga.goods_brand FROM `{$tablepre}goods_auction` ga 
               LEFT JOIN `{$tablepre}member_shop` ms 
               ON ga.supplier_id=ms.m_uid 
               WHERE ga.approval=1 AND ms.isSupplier=3");
while ($rtl=$db->fetch_array($q))
{
    if($arr_brand_count[$rtl['goods_brand']]) $arr_brand_count[$rtl['goods_brand']]['auction_num']++;
    
    $arr_parent=get_parents($rtl['goods_category'],$uid_2_pid);
    foreach ($arr_parent as $val)
    {
        if($arr_cat_count[$val]) $arr_cat_count[$val]['auction_num']++;
    }
    unset($rtl);
}
$db->free_result();

//入库
foreach ($arr_brand_count as $val) $db->insert("`{$tablepre}brand_statistics`",$val);
foreach ($arr_cat_count as $val)
{
    $val['brand_uids']=implode(',',$val['brand_uids']);
    $db->insert("`{$tablepre}cat_statistics`",$val);
}
$db->free_result();
unset($arr_brand_count);
unset($arr_cat_count);

unset($cat);
unset($uid_2_pid);
unset($arr_category);

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);

?>