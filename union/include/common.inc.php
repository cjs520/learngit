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

define('MVMMALL', TRUE);
define('MVMMALL_ROOT', substr(dirname(__FILE__), 0, -7));
define('MVMMALL_CACHE', MVMMALL_ROOT.'data/cache/');
define('OSS_URL','http://img.35wi.com/');
define('IMG_URL','http://pic.35wi.com/');

$MVMMALL_WAPVERSION=1.011;
DIRECTORY_SEPARATOR == '\\'?@ini_set('include_path', '.;' . MVMMALL_ROOT):@ini_set('include_path', '.:' . MVMMALL_ROOT);
date_default_timezone_set('Asia/Shanghai');
session_cache_limiter('private, must-revalidate'); 

require_once dirname(__file__).'/global.func.php';
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
$m_user_ip=$_SERVER['REMOTE_ADDR'];
$m_check_id  = $m_check_uid = $m_check_rank = $mm_adminid = $mm_domain_name = false;
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

//主站配置
$cache->var_stack['isMain']=true;
$main_settings=$cache->get_cache('cfg',0);
$_URL=array($main_settings['mm_mall_url'],$main_settings['mm_domain_name'],$main_settings['mm_cookie_domain']);    //从主站商城设置中读取域名配置
$mm_subdomain=(int)$main_settings['mm_subdomain'];
if($mm_subdomain!=1) unset($_URL[2]);

//执行session类
require_once 'include/session.class.php';
$session_save_dir = 'data/session';
mvm_session::handler();

if($p3p_set_sid=='set')
{
    $sessionID=dhtmlchars($sid);
    header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
    setcookie('sessionID',$sessionID,time()+30*60,'/');
    exit;
}
else if($_COOKIE['sessionID']) $sessionID=$_COOKIE['sessionID'];
else $sessionID=md5($_SERVER['REMOTE_ADDR'].time().rand(1000,9999));
if(!preg_match('/^[a-z0-9]{32}$/',$sessionID)) show_msg('您正在执行罪恶的操作，请现在就停止');

if($_URL[2]) setcookie('sessionID',$sessionID,time()+86400*365,'/',$_URL[2]);
else setcookie('sessionID',$sessionID,time()+86400*365,'/');
session_id($sessionID);
session_start();

$script=str_replace(array('.php','/'),'',strtolower(basename($_SERVER['SCRIPT_FILENAME'])));
do
{
    if(in_array($script,array('ajax'))) break;
    $_SESSION['refer_url']="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}while (0);
$mm_refer_url=$_SESSION['refer_url'];

include 'language/cn/lang_cn.php';
$m_class_array = $cache->get_cache('grade');
$m_order_array = $lang['order_status'];
$m_shop_level = $lang['shop_level'];

if($_SESSION['user']['un_activate']==1) show_msg('您的ID还未激活，正在转向激活页面',"$_URL[0]/activate.php");
if (isset($_SESSION['user']['mvm_sess_id']))
{
	$m_check_id = $_SESSION['user']['mvm_sess_id'];
	$m_check_uid = $_SESSION['user']['mvm_sess_uid'];
	$mm_adminid=$_SESSION['user']['mvm_is_admin'];
	$mvm_rank_list=$_SESSION['user']['mvm_rank_list'];
	
	$mvm_member=$db->get_one("SELECT uid,isSupplier,member_money,member_money_freeze,member_point,member_class,member_image,member_name
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
	    $mvm_shop=$db->get_one("SELECT shop_level,shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$mvm_member[uid]' LIMIT 1");
	    $my_shop_url=GetBaseUrl('index','','','',$m_check_uid);
	}
	
	$m_un_activate = $_SESSION['user']['un_activate'];
	if($m_un_activate==1) $member_level='未激活会员';
	else if($mvm_member['isSupplier']>0) $member_level=$lang['shop_level'][$mvm_shop['shop_level']];
	else $member_level=$m_class_array[$mvm_member['member_class']];
}

//在这里判断读哪个用户的ID
$page_member_id=0;
if($mm_subdomain==1) $page_member_id=GetPMID($_SERVER['HTTP_HOST']);
else 
{
	$supid=(int)$supid;
	if($supid!=0) $page_member_id=$supid;
	else $page_member_id=(int)$_COOKIE['supid'];
	if((int)$page_member_id<=0) show_msg('该商铺不存在',$_URL[0]);
	$rtl=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE uid=$page_member_id AND isSupplier IN (1,2,3) LIMIT 1");
	if(!$rtl) show_msg('该商铺不存在',$_URL[0]);
	setcookie('supid',$page_member_id,time()+86400*365);
}
$shop_file=$db->get_one("SELECT m_uid,m_id,shop_name,video_code,approval_date,up_licence,up_id_card,lat,lng,map_title,map_tip,up_logo,certified_type,supplier_notice,
                                member_homepage,supplier_cat,isSupplier,shop_level,run_product,province,city,county,shop_address,sellshow,xb_money 
                         FROM `{$tablepre}member_shop` 
                         WHERE m_uid='$page_member_id' 
                         LIMIT 1");

//系统配置文件
$settings = $cache->get_cache('cfg',$page_member_id);
@extract($settings,EXTR_OVERWRITE);
unset($settings);
if((int)$mm_close==1 && $m_check_uid!=$page_member_id) show_msg($mm_close_desc?$mm_close_desc:'店主已关闭，没有留下任何信息',$main_settings['mm_mall_url']);

ob_start();
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
$imgpath = "images/$mm_skin_name";
if(!is_dir($imgpath)) $imgpath=$is_mobile?'images/default_wap':'images/default';

if((int)$shop_file['approval_date']<=10) show_msg('商铺已被管理员关闭，请联系管理员开通',$_URL[0]);
if((int)$shop_file['isSupplier']!=3 && ($m_check_uid!=$page_member_id && $m_check_uid!=1)) show_msg('该商铺还未通过审核',$_URL[0]);

//载入banner载置
$banner_config=LoadBannerConfig();

if($is_mobile)    //hook mobile script
{
    $wap_script="wap/$script.php";
    if(file_exists($wap_script))
    {
        require $wap_script;
    }
}

//本页函数定义区
function GetPMID($url)
{
	global $cache,$_URL,$mm_domain_name,$tablepre,$db;
	$member_id=0;
	$url=strtolower($url);
	$arr_url=explode('.',$url);
	
	if($arr_url[1]==$_URL[1])    //域名两部分相等，说明是二级域名
	{
	    $shop=$db->get_one("SELECT m_uid FROM `{$tablepre}member_shop` WHERE member_homepage='$arr_url[0]' LIMIT 1");
	    if(!$shop) show_msg('指定的商铺不存在');
		$member_id=$shop['m_uid'];
	}
	else    //说明是一级域名
	{
	    if(!strstr($url,'www.')) $url='www.'.$url;
	    $url=strtolower($url);
		$tld=$db->get_one("SELECT supplier_id FROM `{$tablepre}tld` WHERE domain_name='$url' AND is_check='1' LIMIT 1");
		if(!$tld) show_msg('无此用户存在!');
	    
		$member_id=$tld['supplier_id'];
	    $mm_domain_name=$url;
	}
	return $member_id;
}