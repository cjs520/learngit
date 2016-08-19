<?php

/**
 * MVM_MALL 网上商店系统 首页文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: index.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
$shop=$db->get_one("SELECT province,city,county,up_logo,certified_type FROM `{$tablepre}member_shop` WHERE m_uid='$page_member_id' LIMIT 1");
$shop_file=array_merge($shop,$shop_file);
$shop_file['up_logo']=ProcImgPath($shop_file['up_logo'],'logo');
$mm_url['investment']=GetBaseUrl('index','','',$page_member_id);

$shop_expire=date('Y-m-d',$shop_file['shop_expire']);
$sellshow_txt=$shop_file['sellshow']==1?'销售型':'展示型';

$sms=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}sms` WHERE to_id='$m_check_id' AND is_read='0' AND to_del='0' AND is_broadcast='0'");
$sms_new=$sms['cnt'];

$broadcast=$db->get_one("SELECT * FROM `{$tablepre}sms` WHERE is_broadcast='1' LIMIT 1");

$member_statistics=$db->get_one("SELECT comment_data FROM `{$tablepre}member_statistics` WHERE m_uid='$m_check_uid' LIMIT 1");
$member_statistics['comment_data']=unserialize($member_statistics['comment_data']);
$seller_credit=intval($member_statistics['comment_data']['seller']['good']['total'])+intval($member_statistics['comment_data']['seller']['normal']['total']);
$buyer_credit=intval($member_statistics['comment_data']['buyer']['good']['total'])+intval($member_statistics['comment_data']['buyer']['normal']['total']);

$upload_size=intval($allow_upload_size[$shop_file['shop_level']]/1024/1024);
$tpl_num=sizeof($tpl_data[$shop_file['shop_level']]);

//认证专区
$shop_statistics=$db->get_one("SELECT `match`,seller_service,seller_ship,ship_service FROM `{$tablepre}shop_statistics` WHERE m_uid='$page_member_id' LIMIT 1");
$shop_statistics['match']=(int)$shop_statistics['match'];
$shop_statistics['seller_service']=(int)$shop_statistics['seller_service'];
$shop_statistics['seller_ship']=(int)$shop_statistics['seller_ship'];
$shop_statistics['ship_service']=(int)$shop_statistics['ship_service'];

//交易提醒
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE supplier_id='$page_member_id' AND status='1'");
$wait_pay=(int)$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_info` WHERE supplier_id='$page_member_id' AND status='3'");
$wait_send=(int)$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_back` WHERE supplier_id='$page_member_id' AND status IN (1,2,3)");
$wait_back=(int)$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}comment_allow` WHERE from_id='$shop_file[m_id]' AND roll='1'");
$wait_comment=(int)$rtl['cnt'];

//商品提醒
$goods_table=$shop_file['sellshow']==1?"{$tablepre}goods_table":"{$tablepre}goods_show";
$goods_moeule=$shop_file['sellshow']==1?"goods":"showgd";
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `$goods_table` WHERE supplier_id='$page_member_id'");
$goods_onsale=(int)$rtl['cnt'];
if($shop_file['sellshow']==1)
{
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}downgoods_table` WHERE supplier_id='$page_member_id'");
    $downgd_num=(int)$rtl['cnt'];
}
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}ad_table`");
$ad_total=(int)$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}ad_table` WHERE m_uid='0'");
$ad_use=$ad_total=(int)$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}ad_table` WHERE m_uid='$page_member_id'");
$my_ad=(int)$rtl['cnt'];
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}gcomment_table` WHERE supplier_id='$page_member_id' AND reply_time='0'");
$wait_reply=(int)$rtl['cnt'];

include template('sadmin_index');