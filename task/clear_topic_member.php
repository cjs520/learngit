<?php
$q=$db->query("SELECT m_uid,COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE approval_date>10 GROUP BY m_uid");
while ($rtl=$db->fetch_array($q))
{
    $rtl_tmp=$db->get_one("SELECT m_uid FROM `{$tablepre}member_statistics` WHERE m_uid='$rtl[m_uid]' LIMIT 1");
    if($rtl_tmp)
    {
        $db->query("UPDATE `{$tablepre}member_statistics` SET topic_num='$rtl[cnt]' WHERE m_uid='$rtl[m_uid]'");
        $db->free_result(1);
    }
    else
    {
        $m=cur_member_info($rtl['m_uid']);
        if(!$m) continue;
        $db->query("INSERT INTO `{$tablepre}member_statistics` (m_uid,m_id,topic_num) 
                    VALUES ('$rtl[m_uid]','$m[member_id]',$rtl[cnt])");
        $db->free_result(1);
    }
    unset($rtl_tmp);
    unset($rtl);
}
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);
?>