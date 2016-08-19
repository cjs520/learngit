<?php
$arr_rtl=array('err'=>'');

$title=dhtmlchars(daddslashes(strip_tags($title)));
$content=dhtmlchars(daddslashes(strip_tags($content)));
$code=dhtmlchars($code);
$t_uid=(int)$t_uid;

do
{
    if(!$m_check_uid)
    {
        $arr_rtl['err']='您还未登录，请先登录';
        break;
    }
    
    if(!$title || !$content)
    {
        $arr_rtl['err']='请将回复的标题和内容填写完整';
        break;
    }
    
    if($m_now_time-(int)$_SESSION['life_topic_reply']<20)
    {
        $arr_rtl['err']='您的回复速度太快了，请先等等';
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
    
    if($t_uid<=0)
    {
        $arr_rtl['err']='指定的话题错误';
        break;
    }
    
    $topic=$db->get_one("SELECT uid,c_uid,c_m_uid FROM `{$tablepre}community_topic` WHERE uid='$t_uid' LIMIT 1");
    if(!$topic)
    {
        $arr_rtl['err']='检索不到指定的话题';
        break;
    }
    
    $content=mb_substr($content,0,255,'UTF-8');
    $title=mb_substr($title,0,35,'UTF-8');
    $row=array(
        't_uid'=>$topic['uid'],
        'c_uid'=>$topic['c_uid'],
        'c_m_uid'=>$topic['c_m_uid'],
        'm_uid'=>$m_check_uid,
        'title'=>$title,
        'content'=>$content,
        'register_date'=>$m_now_time,
        'approval_date'=>0
    );
    
    $db->insert("`{$tablepre}community_comment`",$row);
    $db->free_result();
    $arr_rtl['info']='您的评论已成功提交，请等待管理员审核';
    
    $_SESSION['life_topic_reply']=$m_now_time;
}while (0);

exit(json_encode($arr_rtl));