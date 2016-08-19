<?php
$uid=(int)$uid;
if($uid<=0) exit('参数错误');

$sid=(int)$sid;
if(!($m_check_uid==1 || ($m_check_supplier==2 && $m_check_uid==$sid))) exit('您无权删除该评论');

$db->query("DELETE FROM `{$tablepre}ss_comment` WHERE uid='$uid' AND sid='$sid'");
exit('ok');