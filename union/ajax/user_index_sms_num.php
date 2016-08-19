<?php
if(!$m_check_uid) exit('0');
$expire=$m_now_time-7*24*3600;
if($cmd=='unread')
{
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}sms` WHERE to_id='$m_check_id' AND reg_date>='$expire' AND is_read='0'");
    exit($rtl['cnt']);
}