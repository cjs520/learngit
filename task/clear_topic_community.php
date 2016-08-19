<?php

$q=$db->query("SELECT c_uid,COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE approval_date>10 GROUP BY c_uid");
while ($rtl=$db->fetch_array($q))
{
    $db->query("UPDATE `{$tablepre}community` SET topic_num='$rtl[cnt]' WHERE uid='$rtl[c_uid]'");
    $db->free_result(1);
    unset($rtl);
}
$db->free_result();


//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>