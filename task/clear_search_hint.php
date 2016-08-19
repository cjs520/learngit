<?php
$db->query("TRUNCATE `{$tablepre}goods_search`");
$db->free_result();
$db->query("INSERT INTO `{$tablepre}goods_search` (goods_name,uid,supplier_id) SELECT goods_name,uid,supplier_id FROM `{$tablepre}goods_table`");
$db->free_result();

$db->query("TRUNCATE `{$tablepre}goods_show_search`");
$db->free_result();
$db->query("INSERT INTO `{$tablepre}goods_show_search` (goods_name,uid,supplier_id) SELECT goods_name,uid,supplier_id FROM `{$tablepre}goods_show`");
$db->free_result();

$db->query("TRUNCATE `{$tablepre}article_search`");
$db->free_result();
$db->query("INSERT INTO `{$tablepre}article_search` (article_name,uid,ps_name) SELECT board_subject,uid,ps_name FROM `{$tablepre}bmain` WHERE supplier_id='0'");
$db->free_result();

$db->query("TRUNCATE `{$tablepre}shop_search`");
$db->free_result();
$db->query("INSERT INTO `{$tablepre}shop_search` (shop_name,m_uid) SELECT shop_name,m_uid FROM `{$tablepre}member_shop` WHERE shop_name<>''");
$db->free_result();


//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);

if($module)
{
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_search`");
    $rtl_show=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_show_search`");
    $rtl['cnt']+=$rtl_show['cnt'];
    $rtl2=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}article_search`");
    $rtl3=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}shop_search`");
    $today=date('Y-m-d',$today_timestamp);
    exit("上次更新时间：{$today} &nbsp; 当前商品提示数据量：$rtl[cnt] 条 文章提示数据量：$rtl2[cnt] 条 商铺提示数据量：$rtl3[cnt] 条");
}
?>