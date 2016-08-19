<?php
$db->query("DELETE FROM `{$tablepre}video_ad` WHERE register_date<=0");
$db->free_result();

$q=$db->query("SELECT pic FROM `{$tablepre}video_ad` WHERE end_date<'$m_now_time'");
while ($rtl=$db->fetch_array($q))
{
    if(substr($rtl['pic'],0,4)!='http') file_unlink(MVMMALL_ROOT.'/'.$rtl['pic']);
    unset($rtl);
}
$db->free_result();
$db->query("DELETE FROM `{$tablepre}video_ad` WHERE end_date<'$m_now_time'");
$db->free_result();


$expire=$today_timestamp-3*24*3600;
$q=$db->query("SELECT pic FROM `{$tablepre}video_ad` WHERE approval=-1 AND register_date<'$expire'");
while ($rtl=$db->fetch_array($q))
{
    if(substr($rtl['pic'],0,4)!='http') file_unlink(MVMMALL_ROOT.'/'.$rtl['pic']);
    unset($rtl);
}
$db->free_result();
$db->query("DELETE FROM `{$tablepre}video_ad` WHERE approval=-1 AND register_date<'$expire'");
$db->free_result();

$expire=$today_timestamp-40*24*3600;
$db->query("DELETE FROM `{$tablepre}video_comment` WHERE register_date<='$expire'");
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>