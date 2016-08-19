<?php
$expire=$today_timestamp-3*24*3600;
$expire_end=$today_timestamp-7*24*3600;
$db->query("UPDATE `{$tablepre}order_info` 
            SET status=2 
            WHERE addtime BETWEEN '$expire_end' AND '$expire' AND status='1'");
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>