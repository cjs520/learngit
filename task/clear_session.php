<?php
DeleteDir(MVMMALL_ROOT.'/union/data/session/');

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>