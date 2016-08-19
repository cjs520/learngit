<?php
$uid=(int)$uid;
if($uid<=0) exit('参数错误');

$sid=(int)$sid;
if(!($m_check_uid==1 || ($m_check_supplier==2 && $m_check_uid==$sid))) exit('您无权回复该评论');

$msg=trim($msg);
if($msg=='') exit('请填写回复内容');

$msg=htmlspecialchars($msg);
$db->query("UPDATE `{$tablepre}ss_comment` SET reply='$msg' WHERE uid='$uid' AND sid='$sid'");
exit('ok');