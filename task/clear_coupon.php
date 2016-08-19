<?php
$expire=$today_timestamp;
$db->query("DELETE FROM `{$tablepre}coupon` WHERE end_date<='$expire'");
$q=$db->query("SELECT coupon_img FROM `{$tablepre}coupon_cat` WHERE end_date<='$expire'");
while ($rtl=$db->fetch_array($q))
{
    if(!$rtl['coupon_img']) continue;
    file_unlink(ProcImgPath($rtl['coupon_img']));
    unset($rtl);
}
$db->free_result();
$db->query("DELETE FROM `{$tablepre}coupon_cat` WHERE end_date<='$expire'");
$db->free_result();

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>