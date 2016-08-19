<?php
if(!$m_check_id) exit('请先登录');
if(trim($m_id)=='') exit('请选择添加的好友会员');

if($m_id==$m_check_id) exit('不能将自己添加为好友');
$rtl=$db->get_one("SELECT uid FROM `{$tablepre}friend` WHERE belong_uid='$m_check_uid' AND member_id='$m_id' LIMIT 1");
if($rtl) exit('该用户已经是您的好友，无需添加');

$rtl=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$m_id' LIMIT 1");
if(!$rtl) exit('检索不到指定的好友用户');

$row=array(
    'member_id'=>$m_id,
    'member_uid'=>$rtl['uid'],
    'belong_uid'=>$m_check_uid
);
$db->insert("`{$tablepre}friend`",$row);
$db->free_result();

echo '用户',$m_id,'已成功添加为您的好友';
exit;