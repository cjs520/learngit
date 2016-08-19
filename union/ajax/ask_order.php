<?php
if(!$m_check_id) exit('ERR:请先登录');
if($m_now_time-(int)$_SESSION['ask_order_time']<30) exit('ERROR:您提交的次数太频繁，请稍等片刻');

$aname=dhtmlchars($aname);
$atel=dhtmlchars($atel);
$amobile=dhtmlchars($amobile);
$aemail=dhtmlchars($aemail);
$aaddress=dhtmlchars($aaddress);
$ainvoice=dhtmlchars($ainvoice);
$amsg=dhtmlchars($amsg);

if(!$aname || !$aemail || !$ainvoice || !$amsg) exit('ERROR:请将资料填写完整');
if(!$amobile && !$atel) exit('ERROR:手机号码和电话号码至少要填一个');

if($_POST && (int)$step==1)
{
    $_SESSION['ask_order_time']=$m_now_time;
    $row=array(
        'name'=>$aname,
        'tel'=>$atel,
        'mobile'=>$amobile,
        'email'=>$aemail,
        'address'=>$aaddress,
        'invoice'=>$ainvoice,
        'msg'=>mb_substr($amsg,0,150,'UTF-8'),
        'supplier_id'=>$page_member_id,
        'reg_time'=>$m_now_time,
        'ip'=>$m_user_ip
    );
    $db->insert("`{$tablepre}ask_order`",$row);
    $db->free_result();
    exit('OK:您的询单提交成功，店主将第一时间与您联系');
}
