<?php
if((int)$step==1)
{
    $to=trim($to);
    $content=trim($content);
    $title=trim($title);
    $is_broadcast=(int)$is_broadcast;

    if($is_broadcast==1) $to='全体会员';
    $pattern='/<[^>]*>/';
    $title=preg_replace($pattern,'',$title);
    $content=mb_substr($content,0,100,'UTF-8');
    if(strlen($to)<=0 || strlen($content)<=0 || strlen($title)<=0) show_msg('请把信息填写完整');
    $arr_to=explode(',',$to);
    foreach ($arr_to as $val)
    {
        $row=array(
        'from_id'=>$m_check_id,
        'to_id'=>$val,
        'title'=>$title,
        'content'=>$content,
        'is_broadcast'=>$is_broadcast,
        'reg_date'=>$m_now_time
        );
        $db->insert("`{$tablepre}sms`",$row);
        unset($row);
    }
    show_msg('短消息发送成功',"account.php?action=$action&send=$send");
}
if($del=='del')
{
    if(is_array($uid) && sizeof($uid)>0)
    {
        $str_id=implode(',',$uid);
        if($send=='send') $db->query("UPDATE `{$tablepre}sms` SET send_del='1' WHERE uid IN ($str_id) AND from_id='$m_check_id'");
        else if($send=='admin')
        {
            if($mm_adminid==1) $db->query("DELETE FROM `{$tablepre}sms` WHERE uid IN ($str_id) AND is_broadcast='1'");
        }
        else $db->query("UPDATE `{$tablepre}sms` SET to_del='1' WHERE uid IN ($str_id) AND to_id='$m_check_id'");
    }
    else
    {
        $uid=(int)$uid;
        if($send=='send') $db->query("UPDATE `{$tablepre}sms` SET send_del='1' WHERE uid='$uid' AND from_id='$m_check_id'");
        else if($send=='admin')
        {
            if($mm_adminid==1) $db->query("DELETE FROM `{$tablepre}sms` WHERE uid='$uid' AND is_broadcast='1'");
        }
        else $db->query("UPDATE `{$tablepre}sms` SET to_del='1' WHERE uid='$uid' AND to_id='$m_check_id'");
    }
    show_msg('删除成功',"account.php?action=$action&send=$send&page=$page");
}

require_once MVMMALL_ROOT.'include/pager.class.php';
$search_sql = "WHERE is_broadcast='0'";
if($send=='send')    //收件箱
{
    $search_sql.=" AND from_id='$m_check_id' AND send_del='0'";
    $send_class='class="hover"';
}
else if($send=='admin')    //公告
{
    $search_sql = "WHERE is_broadcast='1'";
    $admin_class='class="hover"';
}
else    //发件箱
{
    $search_sql.=" AND to_id='$m_check_id' AND to_del='0'";
    $receive_class='class="hover"';
}
$total_count = $db->counter("`{$tablepre}sms`",$search_sql);
$list_num    = 10;
$rowset      = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$sms_list=array();
$q=$db->query("SELECT uid,from_id,to_id,title,reg_date FROM `{$tablepre}sms` $search_sql ORDER BY uid DESC LIMIT $from_record,$list_num");
while($rtl=$db->fetch_array($q))
{
    $rtl['time']=date('Y-m-d H:i:s',$rtl['reg_date']);
    $sms_list[]=$rtl;
}
$page_list=$rowset->link("account.php?action=$action&send=$send&page=");

//好友名单
$friend_list=$db->get_all("SELECT uid,member_id FROM `{$tablepre}friend` WHERE belong_uid='$m_check_uid'");
$friend_count=sizeof($friend_list);
require 'header.php';
require_once template('member_sms');