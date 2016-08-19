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
    $db->query("DELETE FROM `{$tablepre}community_comment` WHERE uid='$uid' AND c_m_uid='$m_check_uid'");
    $db->free_result();
    exit('OK:删除成功');
}
else if($step==2)    //审核
{
    $uid=(int)$uid;
    $db->query("UPDATE `{$tablepre}community_comment` SET approval_date='$m_now_time' WHERE uid='$uid' AND c_m_uid='$m_check_uid'");
    $db->free_result();
    exit;
}
else if($step==3)    //拒绝
{
    $uid=(int)$uid;
    $back_reason=dhtmlchars(trim($back_reason));
    if(!$back_reason) exit('ERR:请填写驳回理由');
    $db->query("UPDATE `{$tablepre}community_comment` SET 
                approval_date='-1',
                back_reason='$back_reason' 
                WHERE uid='$uid' AND c_m_uid='$m_check_uid'");
    $db->free_result();
    exit;
}
else
{
    $p_url=base64_encode($mm_refer_url);
    $arr_comm=array();
    $q=$db->query("SELECT uid,c_name FROM `{$tablepre}community` WHERE m_uid='$m_check_uid' AND approval_date>10");
    while ($rtl=$db->fetch_array($q)) $arr_comm[$rtl['uid']]=$rtl;
    if(!$arr_comm) show_msg('您还没有开通生活圈，先去开通一个吧','member.php?action=my_community');

    $c_uid=(int)$c_uid;
    if($c_uid<=0) $c_uid=key($arr_comm);

    $arr_comment=array();
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE c_uid='$c_uid' $filter_sql");
    $total_count = (int)$rtl['cnt'];
    $list_num = 15;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $favorite_list=array();
    $q=$db->query("SELECT uid,title,content,back_reason,t_uid,m_uid,register_date FROM `{$tablepre}community_comment`
                   WHERE c_uid='$c_uid' $filter_sql 
                   ORDER BY approval_date DESC 
                   LIMIT $from_record,$list_num");
    while($rtl=$db->fetch_array($q))
    {
        $topic=$db->get_one("SELECT t_name FROM `{$tablepre}community_topic` WHERE uid='$rtl[t_uid]' LIMIT 1");
        $rtl['t_name']=$topic['t_name'];
        $rtl['topic_url']=GetBaseUrl('life_post',$rtl['t_uid']);
        
        $m=$db->get_one("SELECT member_id FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
        $rtl['member_id']=$m['member_id'];
        
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
        
        $arr_comment[]=$rtl;
    }
    $page_list=$rowset->link("member.php?action=$action&t=$t&page=");
    $db->free_result();

    require 'header.php';
    include_once template('member_my_c_comment');
}

