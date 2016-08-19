<?php
require_once 'include/pager.class.php';

$t=(int)$t;
if(!in_array($t,array(0,1,2))) $t=0;
$filter_sql='';
if($t==0)
{
    $filter_sql=" AND approval_date>10";
}
else if($t==1)
{
    $filter_sql=" AND approval_date=0";
}
else if($t==2)
{
    $filter_sql=" AND approval_date=-1";
}

if($step==1)    //删除
{
    $uid=(int)$uid;
    $db->query("DELETE FROM `{$tablepre}community_member` WHERE uid='$uid' AND m_uid='$m_check_uid'");
    exit;
}

$arr_community=array();
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE m_uid='$m_check_uid' $filter_sql");
$total_count = (int)$rtl['cnt'];
$list_num = 15;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$favorite_list=array();
$q=$db->query("SELECT uid,c_uid,join_reason,register_date,approval_date,back_reason FROM `{$tablepre}community_member` 
               WHERE m_uid='$m_check_uid' $filter_sql 
               ORDER BY approval_date DESC 
               LIMIT $from_record,$list_num");
while($rtl=$db->fetch_array($q))
{
    $comm=$db->get_one("SELECT c_logo,c_name,c_cat,c_hobby FROM `{$tablepre}community` WHERE uid='$rtl[c_uid]' LIMIT 1");
    if(!$comm['c_logo'] || !file_exists($comm['c_logo'])) $comm['c_logo']='images/noimages/noproduct.jpg';
    if($comm) $rtl=array_merge($rtl,$comm);
    
    $cat=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$comm[c_cat]' LIMIT 1");
    if($cat) $rtl=array_merge($rtl,$cat);
    
    $rtl['view_url']=GetBaseUrl('life_detail',$rtl['c_uid']);
    $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
    $arr_community[]=$rtl;
}
$page_list=$rowset->link("member.php?action=$action&page=");
$db->free_result();

require 'header.php';
include_once template('member_my_join_community');