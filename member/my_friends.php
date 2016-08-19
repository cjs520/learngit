<?php
require_once 'include/pager.class.php';

$arr_member=array();
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}friend` WHERE belong_uid='$m_check_uid'");
$total_count = (int)$rtl['cnt'];
$list_num = 15;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$favorite_list=array();
$q=$db->query("SELECT uid,member_uid,register_date FROM `{$tablepre}friend` 
               WHERE belong_uid='$m_check_uid' 
               ORDER BY register_date DESC 
               LIMIT $from_record,$list_num");
while($rtl=$db->fetch_array($q))
{
    $m=$db->get_one("SELECT member_id,member_image,member_class FROM `{$tablepre}member_table` WHERE uid='$rtl[member_uid]' LIMIT 1");
    $m['member_image']=ProcImgPath($m['member_image'],'face');
    $m['member_class']=$m_class_array[$m['member_class']];
    $rtl=array_merge($rtl,$m);
    
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community` WHERE m_uid='$rtl[member_uid]' AND approval_date>10");
    $rtl['comm_num']=$rtl_tmp['cnt'];
    
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE m_uid='$rtl[member_uid]' AND approval_date>10");
    $rtl['topic_num']=$rtl_tmp['cnt'];
    
    $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE m_uid='$rtl[member_uid]' AND approval_date>10");
    $rtl['comment_num']=$rtl_tmp['cnt'];
    
    $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
    
    $arr_member[]=$rtl;
}
$page_list=$rowset->link("member.php?action=$action&t=$t&page=");
$db->free_result();

require 'header.php';
include_once template('member_my_friends');