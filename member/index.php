<?php
$sms=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}sms` WHERE to_id='$m_check_id' AND is_read='0' AND to_del='0'");
$sms_new=$sms['cnt'];

$broadcast_expire=$m_now_time-2*24*3600;
$broadcast=$db->get_one("SELECT * FROM `{$tablepre}sms` WHERE is_broadcast='1' LIMIT 1");

$mvm_member['member_image']=ProcImgPath($mvm_member['member_image'],'face');
$mvm_member['member_class']=$m_class_array[$mvm_member['member_class']];

$fav_shop=array();
$q=$db->query("SELECT f_uid FROM `{$tablepre}favorite` WHERE m_uid='$m_check_uid' AND t='0' ORDER BY f_uid DESC LIMIT 5");
while ($rtl=$db->fetch_array($q))
{
    $shop=$db->get_one("SELECT shop_name,up_logo FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[f_uid]' LIMIT 1");
    if(!$shop) continue;
    $fav_shop[]=array(
        'shop_name'=>$shop['shop_name'],
        'up_logo'=>ProcImgPath($shop['up_logo'],'logo'),
        'url'=>GetBaseUrl('index','','',$rtl['f_uid'])
    );
}
$db->free_result();

$fav_goods=array();
$q=$db->query("SELECT f_uid,module,goods_table FROM `{$tablepre}favorite` WHERE m_uid='$m_check_uid' AND t='1' ORDER BY f_uid DESC LIMIT 5");
while ($rtl=$db->fetch_array($q))
{
    $product=$db->get_one("SELECT goods_name,goods_file1,supplier_id FROM `$rtl[goods_table]` WHERE uid='$rtl[f_uid]' LIMIT 1");
    if(!$product) continue;
    $fav_goods[]=array(
        'goods_name'=>$product['goods_name'],
        'goods_file1'=>ProcImgPath($product['goods_file1'],'logo'),
        'url'=>GetBaseUrl($rtl['module'],$rtl['f_uid'],'action',$product['supplier_id'])
    );
}
$db->free_result();

$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` FORCE INDEX (`username_2`) WHERE username='$m_check_id' AND status=1");
$order_status_1=(int)$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` FORCE INDEX (`username_2`) WHERE username='$m_check_id' AND status=3");
$order_status_3=(int)$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` FORCE INDEX (`username_2`) WHERE username='$m_check_id' AND status=4");
$order_status_4=(int)$rtl['cnt'];
$expire=$m_now_time-90*24*3600;
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` FORCE INDEX (`username`) WHERE username='$m_check_id' AND addtime>='$expire'");
$order_in_three_month=(int)$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}comment_allow` WHERE from_id='$m_check_id' AND roll='0'");
$order_comment=(int)$rtl['cnt'];

$member_statistics=$db->get_one("SELECT comment_data FROM `{$tablepre}member_statistics` WHERE m_uid='$m_check_uid' LIMIT 1");
$member_statistics['comment_data']=unserialize($member_statistics['comment_data']);
$seller_credit=intval($member_statistics['comment_data']['seller']['good']['total'])+intval($member_statistics['comment_data']['seller']['normal']['total']);
$buyer_credit=intval($member_statistics['comment_data']['buyer']['good']['total'])+intval($member_statistics['comment_data']['buyer']['normal']['total']);

require 'header.php';
require_once template('member_index');