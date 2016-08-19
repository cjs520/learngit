<?php
if($action=='quick_login')
{
    if($m_check_id) show_msg('您已经是登录用户，无需重复登录');
    if(!$login_id || !$password) show_msg('请填写正确的用户名和密码');
    
    
    $login_pass = md5($password);
    $login_id = dhtmlchars($login_id);
    if($from_reg) define('REGISTER',true);
    
    
    require_once 'include/passport.inc.php';
    exit;
}
else if($action=='ajax_login')
{
    if($m_check_id) show_msg('您已经是登录用户，无需重复登录');
    if(!$login_id || !$login_pass) show_msg('请填写正确的用户名和密码');
    
    $login_pass = md5($login_pass);
    $login_id = dhtmlchars($login_id);
    
    $lock=$db_mem_cache->op(0,'lock',create_key('login'),'',db_memory_cache::LOCK);
    if(!$lock) exit('ERR:登录进行中，稍安勿躁');
    
    require_once 'include/passport.inc.php';
    exit;
}
else if($action=='login')
{
    $lost_validate_code=$db_mem_cache->op(0,'code',create_key('lost_valid'),'',db_memory_cache::GET);
}