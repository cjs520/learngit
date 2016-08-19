<?php
$arr_rtl=array('err'=>'');

do
{
    $uid=(int)$uid;
    if(!$m_check_uid)
    {
        $arr_rtl['err']='您还未登录，请先登录';
        break;
    }
    
    $join_reason=dhtmlchars(strip_tags(trim($join_reason)));
    $code=dhtmlchars($code);
    $agree=(int)$agree;
    
    if($agree!=1)
    {
        $arr_rtl['err']='请认真阅读并同意《圈子指导原则》';
        break;
    }
    
    if($mm_code_use==1)
    {
        require_once 'include/captcha.class.php';
        $Captcha = new Captcha();
        if(!$Captcha->CheckCode($code))
        {
            $arr_rtl['err']='验证码错误';
            break;
        }
    }
    
    $join_reason=mb_substr($join_reason,0,80,'UTF-8');
    $comm=$db->get_one("SELECT uid,c_name,m_uid,join_check FROM `{$tablepre}community` WHERE uid='$uid' AND approval_date>10 LIMIT 1");
    if(!$comm)
    {
        $arr_rtl['err']='检索不到指定的生活圈';
        break;
    }
    if($comm['m_uid']==$m_check_uid)
    {
        $arr_rtl['err']='您是圈主，无法成为普通圈子成员';
        break;
    }
    
    $join_rtl=$db->get_one("SELECT uid,approval_date FROM `{$tablepre}community_member` WHERE m_uid='$m_check_uid' AND c_uid='$comm[uid]' LIMIT 1");
    if($join_rtl && $join_rtl['approval_date']>10)
    {
        $arr_rtl['err']="您已经是$comm[c_name]生活圈的成员，无需重复申请";
        break;
    }
    
    $mm_join_community=(int)$mm_join_community;
    if($mm_join_community>0)
    {
        $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE c_uid='$comm[uid]' AND approval_date>10");
        if($rtl['cnt']>=$mm_join_community)
        {
            $arr_rtl['err']="$comm[c_name]生活群成员已达到上限，无法申请加入";
            break;
        }
    }
    
    $row=array(
        'c_uid'=>$comm['uid'],
        'c_m_uid'=>$comm['m_uid'],
        'm_uid'=>$m_check_uid,
        'join_reason'=>$join_reason,
        'register_date'=>$m_now_time,
        'approval_date'=>$comm['join_check']==2?$m_now_time:0
    );
    $db->replace("`{$tablepre}community_member`",$row);
    $db->free_result();
    
    $arr_rtl['info']=$comm['join_check']==2?"恭喜，您已经成功加入$comm[c_name]生活圈":'您的申请已发送，圈主将会尽快审核您的申请';
}while (0);

exit(json_encode($arr_rtl));