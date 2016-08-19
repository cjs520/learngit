<?php
require_once 'include/pager.class.php';

if($step==1)    //删除
{
    $uid=(int)$uid;
    $db->query("DELETE FROM `{$tablepre}coupon` WHERE uid='$uid' AND m_uid='$m_check_uid'");
    exit('OK:删除成功');
}

$arr_coupon=array();
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}coupon` WHERE m_uid='$m_check_uid'");
$total_count = (int)$rtl['cnt'];
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$favorite_list=array();
$q=$db->query("SELECT uid,supplier_id,name,start_date,end_date,discount,price_lbound,register_date 
               FROM `{$tablepre}coupon` 
               WHERE m_uid='$m_check_uid' 
               LIMIT $from_record,$list_num");
while($rtl=$db->fetch_array($q))
{
    $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
    $rtl['shop_name']=$shop['shop_name'];
    $rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);
    $rtl['start_date']=date('Y-m-d',$rtl['start_date']);
    $rtl['end_date']=date('Y-m-d',$rtl['end_date']);
    $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
    $rtl['discount']=currency($rtl['discount']);
    $rtl['condition']=$rtl['price_lbound']<=0?'无':"单笔订单满$rtl[price_lbound]元";
    $arr_coupon[]=$rtl;
}
$page_list=$rowset->link("member.php?action=$action&page=");
$db->free_result();

require 'header.php';
include_once template('member_coupon');