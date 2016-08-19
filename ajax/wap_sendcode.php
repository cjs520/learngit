<?php
$last_send_time=intval($db_mem_cache->op(0,'',create_key('sendcodetime'),'',db_memory_cache::FETCH ));
if($m_now_time-$last_send_time<60) exit('ERR:发送太频繁，请稍等');

$lock=$db_mem_cache->op(0,'lock',create_key('wap_sms_lock'),'',db_memory_cache::LOCK );    //上锁
if(!$lock) exit('ERR:业务处理中，请稍等');
        
$total_send=intval($db_mem_cache->op(0,'',$m_user_ip.'_total_send','',db_memory_cache::FETCH ));
if($total_send>=15) exit('ERR:您在该时段可申请的短信验证已用尽，请耐心等待');
$db_mem_cache->op(0,'',$m_user_ip.'_total_send',++$total_send,db_memory_cache::SET );


require_once 'include/net.class.php';
switch ($cmd)
{
    case 'reg_code':
        if($m_check_id) exit('ERR:您当前已是登录用户，无法发送');
        $valid_code=$db_mem_cache->op(0,'code',create_key('reg_valid'));
        if($form_validate_code!=$valid_code) exit('ERR:您的申请已过期，刷新页面重新请求');
        
        $mobile=dhtmlchars($mobile);
        if(!check_mobile($mobile)) exit('ERR:请输入正确的手机号码!');
        $m=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$mobile' LIMIT 1");
        if($m) exit('ERR:您的手机号已被注册，请进行更换');
        
        $db_mem_cache->op(0,'',create_key('sendcodetime'),$m_now_time,db_memory_cache::SET );
        $reg_code=rand(100000,999999);
        $db_mem_cache->op(0,'reg_code',create_key('reg_code'),"{$reg_code}|$mobile",db_memory_cache::SET );
        sms_send($mobile,"您的注册序列号是：$reg_code");
        
        break;
    case 'lost_code':
        if($m_check_id) exit('ERR:您当前已是登录用户，无法发送');
        $valid_code=$db_mem_cache->op(0,'code',create_key('lost_valid'));
        if($lost_validate_code!=$valid_code) exit('ERR:您的申请已过期，刷新页面重新请求');
        
        $login_id=dhtmlchars($login_id);
        $m=$db->get_one("SELECT uid,member_tel2 FROM `{$tablepre}member_table` WHERE member_id='$login_id' LIMIT 1");
        if(!$m) exit('ERR:检索不到指定用户');
        if(!check_mobile($m['member_tel2'])) exit('ERR:您的手机号码格式错误，请联系管理员');
        $db_mem_cache->op(0,'',create_key('sendcodetime'),$m_now_time,db_memory_cache::SET );
        
        $password=rand(100000,999999);
        $row=array(
            'member_pass'=>md5($password),
            'base_pass'=>base64_encode($password)
        );
        $db->update("`{$tablepre}member_table`",$row," uid='$m[uid]' ");
        sms_send($m['member_tel2'],"你的登录密码已被重置为：{$password},请尽快登录并进行修改");
        
        break;
}

exit('OK:校验码已发送，请查收');


function check_mobile($mobile)
{
    if(!preg_match('/^\d{11}$/',$mobile)) return false;
    
    //$rtl=net::get_data("http://v.showji.com/Locating/showji.com20150416273007.aspx?m=$mobile&output=json&timestamp=1");
    //$json_rtl=json_decode($rtl);
    //if(strtolower($json_rtl->QueryResult)!=='true') return false;
    return true;
}