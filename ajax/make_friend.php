<?php
if(!$m_check_id) exit('ERR:您还未登录，请先登录');
if($m_now_time-(int)$_SESSION['make_friend']<20) exit('ERR:您的交友速度太快了，请先等等');

$m_uid=(int)$m_uid;
if($m_uid<=0) exit('ERR:您选择的好友错误');
if($m_uid==$m_check_uid) exit('ERR:您不能把自己添加为好友');

$m=cur_member_info($m_uid);
if(!$m) exit('ERR:检索不到您指定的好友');

if($cmd=='confirm')
{
    $rtl=$db->get_one("SELECT uid FROM `{$tablepre}friend` WHERE belong_uid='$m_check_uid' AND member_id='$m[member_id]' LIMIT 1");
    if($rtl) exit("ERR:$m[member_id]已经是您的好友，无需重复添加");
    exit("INFO:您将添加$m[member_id]为好友？");
}
else if($cmd=='make')
{
    $row=array(
        'member_id'=>$m['member_id'],
        'member_uid'=>$m['uid'],
        'belong_uid'=>$m_check_uid,
        'register_date'=>$m_now_time
    );
    $db->replace("`{$tablepre}friend`",$row);
    $db->free_result();
    $_SESSION['make_friend']=$m_now_time;
    exit("OK:已将会员$m[member_id]成功添加为您的好友");

}

