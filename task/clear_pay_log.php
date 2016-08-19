<?php
$expire=$m_now_time-10*60;
$db->query("DELETE FROM `{$tablepre}pay_log` WHERE register_date<='$expire'");
$db->free_result();

$db->query("DELETE FROM `{$tablepre}order_combine` WHERE tag NOT IN (SELECT pl.tag FROM `{$tablepre}pay_log` pl)");
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);
?>