<?php
if($m_check_uid) move_page('./');
if(!$oauth)
{
    $form_validate_code=$db_mem_cache->op(0,'code',create_key('reg_valid'));
    
    if($_POST && (int)$step==1)
    {
        $mobile=dhtmlchars($mobile);
        $reg_code=dhtmlchars($reg_code);
        $lock=$db_mem_cache->op(0,'lock',create_key('reg_lock'),'',db_memory_cache::LOCK );    //上锁
        if(!$lock) exit('ERR:业务处理中，请稍等');
        
        if($form_validate_code!=$_POST['form_validate_code']) exit('ERR:您的申请已过期，刷新页面重新请求');
        
        $code=$db_mem_cache->op(0,'reg_code',create_key('reg_code'),'',db_memory_cache::FETCH );
        if(!$code) exit('ERR:检索不到指定的验证码，请先获取');
        $arr_code=explode('|',$code);
        if($arr_code[0]!=$reg_code || $arr_code[1]!=$mobile) exit('ERR:验证码或手机号填写错误');
        
        $m=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$mobile' LIMIT 1");
        if($m) exit('ERR:您的手机号已被注册，请进行更换');
        
        $_SESSION['reg_mobile']=$mobile;
        
        $db_mem_cache->op(0,'reg_code',create_key('reg_code'),'',db_memory_cache::DELETE );
        exit('OK:验证成功');
    }
    if($_POST && (int)$step==2)
    {
        if(strlen($password)<6) exit('ERR:密码必须不小于6位');
        
        $lock=$db_mem_cache->op(0,'lock',create_key('reg_lock'),'',db_memory_cache::LOCK );    //上锁
        if(!$lock) exit('ERR:业务处理中，请稍等');
        
        if($form_validate_code!=$_POST['form_validate_code']) exit('ERR:您的申请已过期，刷新页面重新请求');
        $mobile=$_SESSION['reg_mobile'];
        if(!$mobile) exit('ERR:请重新申请并提交验证码');
        $m=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$mobile' LIMIT 1");
        if($m) exit('ERR:您的手机号已被注册，请进行更换');
        
        $rows = array(
	        'member_class' => 1,
	        'member_id' => $mobile,
	        'member_pass' => md5($password),
	        'base_pass' => base64_encode($password),
	        'member_tel1'=>$mobile,
	        'member_tel2'=>$mobile,
	        'register_date' => $m_now_time
	    );
	    $insert_id = $db->insert("{$tablepre}member_table",dhtmlchars($rows));
	    //注册送积分
	    add_score($insert_id,(int)$mm_point_member,'注册赠送','注册赠送积分');
	    add_money($insert_id,(int)$mm_mony_member,'注册赠送','注册赠送预付款');
        
        unset($_SERVER['reg_mobile']);
        $db_mem_cache->op(0,'code',create_key('reg_valid'),'',db_memory_cache::DELETE );
        $url="logging.php?action=quick_login&login_id=$mobile&password=".urlencode($password)."&from_reg=1";
        exit($url);
    }
    
    require 'wap/include/header.inc.php';
    include template('register2');
    footer();
}
