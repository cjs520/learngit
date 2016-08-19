<?php
$ps_search=trim($ps_search);
if(!$ps_search) exit;

$arr_rtl=array();
if($ctrl=='g' && (int)$sellshow==1)    //销售商品
{
    $q=$db->query("SELECT goods_name,uid,supplier_id FROM `{$tablepre}goods_search` WHERE goods_name LIKE '$ps_search%' LIMIT 10");
    while ($rtl=$db->fetch_array($q))
    {
        $arr_rtl[]=array($rtl['goods_name'],GetBaseUrl('product',$rtl['uid'],'action',$rtl['supplier_id']));
    }
    $db->free_result();
}
else if ($ctrl=='g' && (int)$sellshow==2)    //展示商品
{
    $q=$db->query("SELECT goods_name,uid,supplier_id FROM `{$tablepre}goods_show_search` WHERE goods_name LIKE '$ps_search%' LIMIT 10");
    while ($rtl=$db->fetch_array($q))
    {
        $arr_rtl[]=array($rtl['goods_name'],GetBaseUrl('product',$rtl['uid'],'action',$rtl['supplier_id']));
    }
    $db->free_result();
}
else if($ctrl=='article')
{
    $q=$db->query("SELECT article_name,uid,ps_name FROM `{$tablepre}article_search` WHERE article_name LIKE '$ps_search%' LIMIT 10");
    while ($rtl=$db->fetch_array($q))
    {
        $arr_rtl[]=array($rtl['article_name'],"article.php?action=$rtl[ps_name]&id=$rtl[uid]");
    }
    $db->free_result();
}
else if($ctrl=='shop')
{
    $q=$db->query("SELECT shop_name,m_uid FROM `{$tablepre}shop_search` WHERE shop_name LIKE '$ps_search%' LIMIT 10");
    while ($rtl=$db->fetch_array($q))
    {
        $arr_rtl[]=array($rtl['shop_name'],GetBaseUrl('index','','',$rtl['m_uid']));
    }
    $db->free_result();
}

echo json_encode($arr_rtl);
exit;