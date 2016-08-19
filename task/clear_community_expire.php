<?php
$expire=$today_timestamp-3*24*3600;

$db->query("SELECT c_logo FROM `{$tablepre}community` WHERE approval_date=-1 AND register_date<='$expire'");
while ($rtl=$db->fetch_array($q))
{
    file_unlink($rtl['c_logo']);
    unset($rtl);
}
$db->free_result();
$db->query("DELETE FROM `{$tablepre}community` WHERE approval_date=-1 AND register_date<='$expire'");
$db->query("DELETE FROM `{$tablepre}community_comment` WHERE approval_date=-1 AND register_date<='$expire'");
$db->free_result();

$db->query("DELETE FROM `{$tablepre}community_member` WHERE approval_date=-1 AND register_date<='$expire'");
$db->query("DELETE FROM `{$tablepre}community_topic` WHERE approval_date=-1 AND register_date<='$expire'");
$db->free_result();



//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>