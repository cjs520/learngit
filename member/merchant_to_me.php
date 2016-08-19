<?php
require_once 'include/pager.class.php';

$t=(int)$t==1?1:0;
if($t==0)
{
    $table="{$tablepre}want_supply_msg";
    $filter_sql=" supply_m_uid='$m_check_uid' ";
}
else
{
    $table="{$tablepre}want_buy_msg";
    $filter_sql=" buy_m_uid='$m_check_uid' ";
}

if($step==1)    //删除
{
    $uid=(int)$uid;
    $db->query("DELETE FROM `$table` WHERE uid='$uid' AND $filter_sql");
    exit('OK:删除成功');
}
else if($step==2)    //采纳
{
    $uid=(int)$uid;
    $db->query("UPDATE `$table` SET approval_date='$m_now_time' WHERE uid='$uid' AND $filter_sql");
    exit('OK:留言已采纳');
}

$arr_msg=array();
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `$table` WHERE $filter_sql");
$total_count = (int)$rtl['cnt'];
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$favorite_list=array();
$q=$db->query("SELECT * FROM `$table` 
               WHERE $filter_sql 
               ORDER BY register_date DESC 
               LIMIT $from_record,$list_num");
while($rtl=$db->fetch_array($q))
{
    if($t==0)
    {
        $supply=$db->get_one("SELECT goods_name FROM `{$tablepre}want_supply` WHERE uid='$rtl[supply_id]' LIMIT 1");
        $rtl['goods_name']=$supply['goods_name'];
        $rtl['url']="infor_supply_detail.php?action=$rtl[supply_id]";
    }
    else
    {
        $buy=$db->get_one("SELECT goods_name FROM `{$tablepre}want_buy` WHERE uid='$rtl[buy_id]' LIMIT 1");
        $rtl['goods_name']=$buy['goods_name'];
        $rtl['url']="infor_buy_detail.php?action=$rtl[buy_id]";
    }
    
    $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
    $rtl['status']=$rtl['approval_date']>10?'已采纳':'未采纳';
    $arr_msg[]=$rtl;
}
$page_list=$rowset->link("member.php?action=$action&t=$t&page=");
$db->free_result();

require 'header.php';
include_once template('member_merchant_to_me');