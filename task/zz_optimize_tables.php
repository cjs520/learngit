<?php
$q=$db->query("SHOW TABLES");
while ($rtl=$db->fetch_array($q,MYSQL_NUM))
{
    $db->query("REPAIR TABLE `$rtl[0]`");
    $db->query("OPTIMIZE TABLE `$rtl[0]`");
}
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);

?>