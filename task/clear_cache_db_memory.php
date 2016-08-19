<?php
$db->query("DELETE FROM `{$tablepre}memory_cache` WHERE expire<='$m_now_time'");
$db->free_result(1);

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);
?>