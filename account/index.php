<?php
include 'data/malldata/config.inc.php';
$mvm_member['member_money']=currency($mvm_member['member_money']);


$member_statistics=$db->get_one("SELECT comment_data FROM `{$tablepre}member_statistics` WHERE m_uid='$m_check_uid' LIMIT 1");
$member_statistics['comment_data']=unserialize($member_statistics['comment_data']);
$seller_credit=intval($member_statistics['comment_data']['seller']['good']['total'])+intval($member_statistics['comment_data']['seller']['normal']['total']);
$buyer_credit=intval($member_statistics['comment_data']['buyer']['good']['total'])+intval($member_statistics['comment_data']['buyer']['normal']['total']);

$member_check='未认证';
if($mvm_shop['certified_type'] & 1) $member_check='实名认证';
if($mvm_shop['certified_type'] & 2) $member_check='实体认证';

$shop_level='未开通商铺';
$mvm_shop['shop_level']=(int)$mvm_shop['shop_level'];
if($mvm_member['isSupplier']>0)
{
    $shop_level=$lang['shop_level'][$mvm_shop['shop_level']];
}
$shop_config=array(
    'goods_num'=>(int)$allow_goods_items[$mvm_shop['shop_level']],
    'pic_space'=>intval($allow_upload_size[$mvm_shop['shop_level']]/1024/1024),
    'tpl_num'=>(int)sizeof($tpl_data[$mvm_shop['shop_level']]),
    'page_num'=>(int)$allow_page_items[$mvm_shop['shop_level']]
);

$expire=$m_now_time-90*24*3600;
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` FORCE INDEX (`username`) WHERE username='$m_check_id' AND addtime>='$expire'");
$order_in_three_month=(int)$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}comment_allow` WHERE from_id='$m_check_id' AND roll='0'");
$order_comment=(int)$rtl['cnt'];

if($mvm_member['isSupplier']>0)
{
    $shop_order=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE supplier_id='$m_check_uid' AND status=1");
    $shop_order_1=(int)$shop_order['cnt'];
    $shop_order=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE supplier_id='$m_check_uid' AND status=3");
    $shop_order_3=(int)$shop_order['cnt'];
}

require 'header.php';
require_once template('member_account');