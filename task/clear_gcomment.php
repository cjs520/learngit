<?php
$expire=$today_timestamp-3*24*3600;
$db->query("DELETE FROM `{$tablepre}gcomment_table` WHERE approval_date<='$expire'");
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>