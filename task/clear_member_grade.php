<?php
$arr_admin_grade=array();
$q=$db->query("SELECT group_id FROM `{$tablepre}grade_table` WHERE is_admin='1'");
while ($rtl=$db->fetch_array($q)) $arr_admin_grade[]=$rtl['group_id'];
$db->free_result();
$str_admin_grade=implode(',',$arr_admin_grade);
unset($arr_admin_grade);

$q=$db->query("SELECT group_id,min_points,max_points FROM `{$tablepre}grade_table` WHERE is_admin='0' ORDER BY degree");
while ($rtl=$db->fetch_array($q))
{
    $db->query("UPDATE `{$tablepre}member_table` SET 
                member_class='$rtl[group_id]' 
                WHERE member_point_acc BETWEEN '$rtl[min_points]' AND '$rtl[max_points]' AND uid>1 AND (NOT member_class IN ($str_admin_grade))");
    $db->free_result(1);
    unset($rtl);
}
$db->free_result();
unset($str_admin_grade);

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>