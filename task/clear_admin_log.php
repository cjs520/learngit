<?php
$expire=$today_timestamp-7*24*3600;
$db->query("DELETE FROM `{$tablepre}admin_log` WHERE register_date<='$expire'");
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>