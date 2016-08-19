<?php

/**
 * MVM_MALL 网上商店系统  后台管理引导
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-12 $
 * $Id: admincp.php www.mvmmall.cn$
 * ---------------------------------------------
*/
define('IN_ADMINCP', TRUE);
require_once 'include/common.inc.php';
$mm_skin_name = 'admincp';
$module = dhtmlchars($module);
$action = dhtmlchars($action);

if (file_exists('language/cn/admin/lang_'.$module.'.php'))
{
    include 'language/cn/admin/lang_'.$module.'.php';
    include 'language/cn/lang_cn.php';
}

if(!$m_check_id)
{
    require_once 'admin/login.inc.php';
    exit;
}

//权限判断
if($m_check_uid==1) $admin_check_rank=1;
else
{
    $admin_check_rank = 0;
    if($mm_adminid != 1) show_msg('admin_acess');
    $admincp_rank = explode(',',$mvm_rank_list);
    if(in_array($module,$admincp_rank)) $admin_check_rank=1;
}
if(in_array($module,array('editor'))) $admin_check_rank=1;

$prev_url="admincp.php?".$_SERVER['QUERY_STRING'];
foreach ($_POST as $key=>$val) $prev_url.="&$key=".urlencode($val);
$prev_url=base64_encode($prev_url);

if($_SESSION['page_error'])
{
	$page_error=$_SESSION['page_error'];
	unset($_SESSION['page_error']);
}
if($admin_check_rank != 1 && $m_check_uid != 1) show_msg('admin_acess');
$cpscript =  'admin/'.$module.'.inc.php';
if(!file_exists($cpscript)) show_msg('文件不存在');
require_once $cpscript;
 
function tpl_array($dir_path='./templates',$file='',$exclude_file='')
{
    $m_mall_skin = get_dirinfo($dir_path,$file,$exclude_file);
    $size_skin = sizeof($m_mall_skin);
    $arr = array();
    for($i = 0 ; $i < $size_skin; $i++)
    {
        if(strstr($m_mall_skin[$i],'_wap')) continue;
        $arr[$m_mall_skin[$i]] = $m_mall_skin[$i];    	
    }
    return $arr;
}

function sadmin_show_msg($msg,$p_url)
{
	$_SESSION['page_error']=$msg;
	
	$p_url=base64_decode($p_url);
	move_page($p_url);
	exit;
}

function admin_log($log_cnt)
{
    global $db,$tablepre,$m_check_id,$m_now_time;
    
    $row=array(
        'm_id'=>$m_check_id,
        'register_date'=>$m_now_time,
        'cnt'=>daddslashes($log_cnt)
    );
    $db->insert("`{$tablepre}admin_log`",$row);
}

function iframe_callback($msg,$other_info='')
{
    require_once template('callback');
	$output = str_replace(array('<!--<!---->','<!---->',"\r"),'',ob_get_contents());
	ob_end_clean();
	exit($output);
}