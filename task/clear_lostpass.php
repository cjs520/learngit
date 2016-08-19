<?php
$db->query("TRUNCATE TABLE `{$tablepre}lostpass`");
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>