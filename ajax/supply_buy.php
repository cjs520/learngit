<?php
$uid=(int)$uid;
$arr_rtl=array('err'=>'');

if($cmd=='supply_msg')
{
    do
    {
        $name=dhtmlchars($name);
        $tel=dhtmlchars($tel);
        $address=dhtmlchars($address);
        $msg=mb_substr(dhtmlchars(strip_tags($msg)),0,150,'UTF-8');
        $code=dhtmlchars($code);
        
        if($m_now_time-(int)$_SESSION['supply_msg_time']<20)
        {
            $arr_rtl['err']='您的留言太频繁了，请稍候';
            break;
        }
        
        if(!$m_check_id)
        {
            $arr_rtl['err']='您还未登录，请先登录';
            break;
        }
        
        if(!$name || !$msg)
        {
            $arr_rtl['err']='请将联系信息填写完整';
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
        
        $rtl=$db->get_one("SELECT uid,m_uid FROM `{$tablepre}want_supply` WHERE uid='$uid' LIMIT 1");
        if(!$rtl)
        {
            $arr_rtl['err']='检索不到指定的供应信息';
            break;
        }
        
        $row=array(
            'm_id'=>$m_check_id,
            'supply_id'=>$rtl['uid'],
            'supply_m_uid'=>$rtl['m_uid'],
            'name'=>$name,
            'tel'=>$tel,
            'address'=>$address,
            'msg'=>$msg,
            'register_date'=>$m_now_time
        );
        $db->insert("`{$tablepre}want_supply_msg`",$row);
        $db->free_result();
        $arr_rtl['info']='留言已提交成功';
        
        $_SESSION['supply_msg_time']=$m_now_time;
    }while (0);
    
}
else if($cmd=='buy_msg')
{
    do
    {
        $name=dhtmlchars($name);
        $tel=dhtmlchars($tel);
        $address=dhtmlchars($address);
        $msg=mb_substr(dhtmlchars(strip_tags($msg)),0,150,'UTF-8');
        $code=dhtmlchars($code);
        
        if($m_now_time-(int)$_SESSION['buy_msg_time']<20)
        {
            $arr_rtl['err']='您的留言太频繁了，请稍候';
            break;
        }
        
        if(!$m_check_id)
        {
            $arr_rtl['err']='您还未登录，请先登录';
            break;
        }
        
        if(!$name || !$msg)
        {
            $arr_rtl['err']='请将联系信息填写完整';
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
        
        $rtl=$db->get_one("SELECT uid,m_uid FROM `{$tablepre}want_buy` WHERE uid='$uid' LIMIT 1");
        if(!$rtl)
        {
            $arr_rtl['err']='检索不到指定的求购信息';
            break;
        }
        
        $row=array(
            'm_id'=>$m_check_id,
            'buy_id'=>$rtl['uid'],
            'buy_m_uid'=>$rtl['m_uid'],
            'name'=>$name,
            'tel'=>$tel,
            'address'=>$address,
            'msg'=>$msg,
            'register_date'=>$m_now_time
        );
        $db->insert("`{$tablepre}want_buy_msg`",$row);
        $db->free_result();
        $arr_rtl['info']='留言已提交成功';
        
        $_SESSION['buy_msg_time']=$m_now_time;
    }while (0);
}

exit(json_encode($arr_rtl));