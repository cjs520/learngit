<?php
$uid=(int)$uid;
if($uid<=0) $arr=array('error'=>'参数错误');
else
{
    $rtl=$db->get_one("SELECT title,content,from_id,to_id FROM `{$tablepre}sms`
		                   WHERE uid='$uid' AND ((from_id='$m_check_id' OR to_id='$m_check_id') OR is_broadcast='1') 
		                   LIMIT 1");
    if($rtl)
    {
        $arr=array(
        'title'=>$rtl['title'],
        'content'=>$rtl['content'],
        'from'=>$rtl['from_id'],
        'to'=>$rtl['to_id']
        );
        $db->query("UPDATE `{$tablepre}sms` SET is_read='1' WHERE uid='$uid'");
    }
    else $arr=array( 'error'=>'找不到该邮件信息');
}

echo json_encode($arr);
exit;