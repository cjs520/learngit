<?php
require_once 'include/pager.class.php';

$t=(int)$t;
if(!in_array($t,array(0,1,2))) $t=0;
$filter_sql='';
if($t==0) $filter_sql=" AND approval_date>10";
else if($t==1) $filter_sql=" AND approval_date=0";
else if($t==2) $filter_sql=" AND approval_date=-1";

if($step==1)    //删除
{
    $uid=(int)$uid;
    $db->query("DELETE FROM `{$tablepre}community_comment` WHERE uid='$uid' AND m_uid='$m_check_uid'");
    $db->free_result();
    exit('OK:删除成功');
}
else
{
    $p_url=base64_encode($mm_refer_url);
    
    $c_uid=(int)$c_uid;
    if($c_uid<=0) $c_uid=key($arr_comm);

    $arr_member=array();
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE m_uid='$m_check_uid' $filter_sql");
    $total_count = (int)$rtl['cnt'];
    $list_num = 15;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q=$db->query("SELECT uid,title,content,register_date,t_uid,back_reason FROM `{$tablepre}community_comment`
                   WHERE m_uid='$m_check_uid' $filter_sql 
                   ORDER BY approval_date DESC 
                   LIMIT $from_record,$list_num");
    while($rtl=$db->fetch_array($q))
    {
        $topic=$db->get_one("SELECT t_name FROM `{$tablepre}community_topic` WHERE uid='$rtl[t_uid]' AND approval_date>10 LIMIT 1");
        $rtl['t_name']=$topic['t_name'];
        $rtl['topic_url']=GetBaseUrl('life_post',$rtl['t_uid']);
        
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
        $arr_member[]=$rtl;
    }
    $page_list=$rowset->link("member.php?action=$action&t=$t&page=");
    $db->free_result();

    require 'header.php';
    include_once template('member_my_jc_comment');
}

