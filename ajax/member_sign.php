<?php
$mm_sign_point=intval($mm_sign_point);
if($mm_sign_point<=0) exit('ERR:签到功能未开通，请联系管理员');

if(!$m_check_id) exit('ERR:您还没有登录，请先登录');
$rtl=$db->get_one("SELECT m_uid FROM `{$tablepre}member_sign` WHERE m_uid='$m_check_uid' AND register_date='$today_timestamp' LIMIT 1");
if($rtl) exit('ERR:今天已签到');
$row=array(
    'm_uid'=>$m_check_uid,
    'point_add'=>$mm_sign_point,
    'register_date'=>$today_timestamp
);
$db->replace("`{$tablepre}member_sign`",$row);

add_score($m_check_uid,$mm_sign_point,'签到','签到获得');
exit('OK:签到完成，您今天签到获得'.strval($mm_sign_point).'积分');