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
    $db->query("DELETE FROM `{$tablepre}community_topic` WHERE uid='$uid' AND m_uid='$m_check_uid'");
    $db->free_result();
    exit('OK:删除成功');
}
else if($step==4)    //修改
{
    $uid=(int)$uid;
    $topic=$db->get_one("SELECT uid,t_name,content,tags FROM `{$tablepre}community_topic` WHERE uid='$uid' AND m_uid='$m_check_uid' LIMIT 1");
    if(!$topic) show_msg('检索不到指定的生活圈主题');
    
    if($_POST)
    {
        $detail=preg_replace('/<script.*?script>/i','',$detail);
        $title=daddslashes(dhtmlchars($title));
        $tags=dhtmlchars(trim($tags));
        
        if(!$detail) show_msg('请填写主题内容');
        if(!$title) show_msg('请填写标题内容');

        if($cover && strstr($cover,'union'))
        {
            $start_pos=strpos($cover,'union');
            $cover=substr($cover,$start_pos);
        }

        $arr_tmp=explode(' ',$tags);
        $arr_tmp2=array();
        foreach ($arr_tmp as $key=>$val)
        {
            if($key>=5) break;
            $arr_tmp2[]=$val;
        }
        $tags=implode(' ',$arr_tmp2);

        $row=array(
            't_name'=>$title,
            'content'=>$detail,
            'cover'=>$cover,
            'tags'=>$tags,
            'approval_date'=>0,
            'back_reason'=>''
        );
        $db->update("`{$tablepre}community_topic`",$row," uid='$topic[uid]' ");
        $db->free_result();

        show_msg('主题修改成功',base64_decode($p_url));
    }
    
    require 'header.php';
    include_once template('member_my_jc_topic_add');
}
else
{
    $p_url=base64_encode($mm_refer_url);
    
    $c_uid=(int)$c_uid;
    if($c_uid<=0) $c_uid=key($arr_comm);

    $arr_member=array();
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE m_uid='$m_check_uid' $filter_sql");
    $total_count = (int)$rtl['cnt'];
    $list_num = 15;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q=$db->query("SELECT uid,t_name,tags,c_uid,hits,back_reason,register_date FROM `{$tablepre}community_topic`
                   WHERE m_uid='$m_check_uid' $filter_sql 
                   ORDER BY approval_date DESC 
                   LIMIT $from_record,$list_num");
    while($rtl=$db->fetch_array($q))
    {
        $comm=$db->get_one("SELECT c_name FROM `{$tablepre}community` WHERE uid='$rtl[c_uid]' AND approval_date>10 LIMIT 1");
        if($comm) $rtl=array_merge($rtl,$comm);
        $rtl['comm_url']=GetBaseUrl('life_detail',$rtl['c_uid']);
        
        $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
        $rtl['url']=GetBaseUrl('life_post',$rtl['uid']);
        $arr_member[]=$rtl;
    }
    $page_list=$rowset->link("member.php?action=$action&t=$t&page=");
    $db->free_result();

    require 'header.php';
    include_once template('member_my_jc_topic');
}

