<?php
/**
 * MVM_MALL 网上商店系统 共同引导文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-03-01 $
 * $Id: common.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
error_reporting(E_ERROR | E_PARSE);
set_time_limit(0);
set_magic_quotes_runtime(0);
//$_URL=array('http://www.shoemall.cn','shoemall','.shoemall.cn');    //这个变量用于匹配二级域名
define('MVMMALL', TRUE);
define('MVMMALL_ROOT', substr(dirname(__FILE__), 0, -7));
define('MVMMALL_CACHE', MVMMALL_ROOT.'data/cache/');
$img_url = $_SERVER[HTTP_HOST]=='www.35wi.com'?'http://pic.35wi.com/':'http://testpic.35wi.com/';
define('IMG_URL',$img_url);
$MVMMALL_WAPVERSION=1.0038;
DIRECTORY_SEPARATOR == '\\'?@ini_set('include_path', '.;' . MVMMALL_ROOT):@ini_set('include_path', '.:' . MVMMALL_ROOT);
date_default_timezone_set('Asia/Shanghai');
session_cache_limiter('private, must-revalidate'); 
require_once 'include/global.func.php';
require_once 'data/malldata/config.inc.php';
is_install();    //判断是否正常安装，如果没有，则转向安装页面 by dxd
if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) exit('Access Error');
register_shutdown_function('page_shutdown');

daddslashes($_POST);
extract($_POST,EXTR_OVERWRITE);

daddslashes($_GET);
$arr_js_words=array('script','window','alert','"','\'','?','&','./');
foreach ($_GET as $key=>$val)
{
    $_GET[$key]=urldecode($val);
    foreach ($arr_js_words as $v)
    {
        if(strstr(strtolower($_GET[$key]),$v)) unset($_GET[$key]);
    }
}
extract($_GET,EXTR_OVERWRITE);

$m_now_time = time();
$today_timestamp=strtotime(date('Y-m-d').' 00:00:00');
$m_user_ip=$_SERVER['REMOTE_ADDR'];
$m_check_id  = $m_check_uid = $m_check_rank = $mm_adminid = false;
$mvm_lock=array();

//用户配置文件处理
require_once 'config/config_db.php';
require_once 'include/mysql_class.php';
$db = new dbmysql($con_db_host,$con_db_id,$con_db_pass,$con_db_name,$db_charset);
unset($con_db_id);
unset($con_db_pass);
unset($db_settings);

//缓存类
include 'data/malldata/cache_settings.config.php';
require_once 'include/cache.class.php';
$cache = new cache($db,$tablepre);
require_once 'include/db_memory_cache.class.php';
$db_mem_cache=new db_memory_cache();
//系统配置文件
$settings  = $cache->get_cache('cfg');
@extract($settings,EXTR_OVERWRITE);

$_URL=array($mm_mall_url,$mm_domain_name,$mm_cookie_domain);    //从商城设置中读取域名配置
$mm_subdomain=(int)$mm_subdomain;    //二级域名开关，0为不支持二级域名，1则支持二级域名
if($mm_subdomain!=1) unset($_URL[2]);
unset($settings);
$mm_lang='cn';

ob_start();
$m_class_array = $cache->get_cache('grade');
$is_mobile=isMobile();
$is_wx=false;
$phone_type='';
if($is_mobile)
{
    $mm_skin_name.='_wap';
    $is_wx=strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger');
    if(strstr($_SERVER['HTTP_USER_AGENT'],'iPhone')) $phone_type='iphone';
    else if(strstr($_SERVER['HTTP_USER_AGENT'],'Android')) $phone_type='android';
}
$imgpath  = "images/$mm_skin_name";
if(!is_dir($imgpath)) $imgpath=$is_mobile?'images/default_wap':'images/default';

//执行session类
require_once 'include/session.class.php';
$session_save_dir = 'union/data/session';
$session_lifetime=10000;
mvm_session::handler();

if($taobao=='upload') $sessionID=$_GET['sessionID'];
else if($_REQUEST['sessionID']) $sessionID=$_REQUEST['sessionID'];
else if($_COOKIE['sessionID']) $sessionID=$_COOKIE['sessionID'];
else $sessionID=md5($_SERVER['REMOTE_ADDR'].time().rand(1000,9999));
if(!preg_match('/^[a-z0-9]{32}$/',$sessionID)) show_msg('您正在执行罪恶的操作，请现在就停止');

if($_URL[2]) setcookie('sessionID',$sessionID,time()+86400*365,'/',$_URL[2]);
else setcookie('sessionID',$sessionID,time()+86400*365,'/');

include 'language/cn/lang_cn.php';

session_id($sessionID);
session_start();

if (isset($_SESSION['user']['mvm_sess_id']))
{
	$m_check_id = $_SESSION['user']['mvm_sess_id'];
	$m_check_uid = $_SESSION['user']['mvm_sess_uid'];
	$mm_adminid=$_SESSION['user']['mvm_is_admin'];
	$mvm_rank_list=$_SESSION['user']['mvm_rank_list'];
	
	$mvm_member=$db->get_one("SELECT uid,isSupplier,member_money,member_money_freeze,member_point,member_class,member_image,member_name,member_email 
	                          FROM `{$tablepre}member_table` 
	                          WHERE uid='$m_check_uid' 
	                          LIMIT 1");
	if(!$mvm_member)
	{
	    session_destroy();
	    show_msg('您的资料异常，请联系管理员','./');
	}
	if($mvm_member['isSupplier']>0)
	{
	    $mvm_shop=$db->get_one("SELECT shop_level,shop_name,certified_type 
	                            FROM `{$tablepre}member_shop` 
	                            WHERE m_uid='$mvm_member[uid]' 
	                            LIMIT 1");
	}
	
	if($mvm_member['isSupplier']>0) $member_level=$lang['shop_level'][$mvm_shop['shop_level']];
	else $member_level=$m_class_array[$mvm_member['member_class']];
}

$script=str_replace(array('.php','/'),'',strtolower(basename($_SERVER['SCRIPT_FILENAME'])));
do
{
    if(in_array($script,array('register','logging','register_end','ajax','lostpass','map','respond','oauth_back'))) break;
    if($script=='cart' && !in_array($action,array('list','buy'))) break;
    $_SESSION['refer_url']="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}while (0);
$mm_refer_url=$_SESSION['refer_url'];
$script_param=implode('|',$_GET);

do
{
    if($script=='ajax' && $action=='code') break;
    if((int)$mm_close!=0 || $m_check_uid==1 || $login=='login') break;
    require_once 'header.php';
    $action='login';
    include template('login');
    footer();
}while (0);

$m_order_array  = $lang['order_status'];

/////////////////////多店特有的配置////////////////////////
$mm_comment_level=array(1=>'好评',0=>'中评',-1=>'差评');    //评价的等级
$arr_status=array(-1=>'拒绝',0=>'审核中',1=>'通过审核');

include 'include/ad_config.inc.php';    //引入广告配置
require_once 'include/ad.class.php';    //广告类
$AD=new cad($db,$tablepre);

if($is_mobile)    //hook mobile script
{
    $wap_script="wap/$script.php";
    if(file_exists($wap_script))
    {
        require $wap_script;
    }
}


