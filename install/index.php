<?php

/**
 * MVM_MALL 网上商店系统  安装文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-03-02 $
 * $Id: index.php www.mvmmall.cn$
 * ---------------------------------------------
*/

header("Content-type: text/html;charset=utf-8");
error_reporting(E_ERROR | E_PARSE);
@set_time_limit(0);
@ini_set('memory_limit','64M');
set_magic_quotes_runtime(0);
define('VERSION','5.5.0');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
isset($_REQUEST['GLOBALS']) && exit('Access Error');
foreach(array('_COOKIE', '_POST', '_GET') as $_request)
{
	foreach($$_request as $_key => $_value) 
		$_key{0} != '_' && $$_key = daddslashes($_value);
}
$install_url="http://$_SERVER[HTTP_HOST]/";
if(file_exists('../config/install.lock')){
	exit('对不起，该程序已经安装过了。<br/>
	      如您要重新安置，请手动删除商铺config/install.lock下文件。');
}

switch ($action)
{
	case 'license':
	{
		require('templates/license.htm');
		break;
	}
	case 'inspect':
	{
		if($_POST['license_consent']!='agree')
		{
		    $url = $_SERVER['HTTP_REFERER'];
            header("Location: $url");
            break;
		}
		if((int)ini_get('session.auto_start')!=0) $session_tip="请联系服务器提供商将SESSION自动启动关闭 ( 设置session.auto_start=Off或者=0状态 )";
		$mysql_support = (function_exists( 'mysql_connect')) ? ON : OFF;
		
		$mysql_support  = 'OFF';
		$mysql_ver_class ='WARN';
		if(function_exists('mysql_connect'))
		{
			$mysql_support  = 'ON';
			$mysql_ver_class ='OK';
		}
		
		$ver_class = 'OK';
		$check=1;
		if(PHP_VERSION<'4.1.0')
		{
			$ver_class = 'WARN';
			$errormsg['version']='php 版本过低';
		}
		$w_check=array(
		    '../data/db',
		    '../images/banner',
		    '../images/brand',
		    '../images/category',
		    '../images/links',
		    '../union',
		    '../union/data/cache',
		    '../union/config',
		    '../union/images',
		    '../union/data/session',
		    '../union/shopimg',
		);
		$class_chcek=array();
		$check_msg = array();
		$count=count($w_check);
		for($i=0; $i<$count; $i++)
		{
			if(!file_exists($w_check[$i]))
			{
				$check_msg[$i].= '文件或文件夹不存在请上传';$check=0;
				$class_chcek[$i] = 'WARN';
			}
			elseif(is_writable($w_check[$i]))
			{
				$check_msg[$i].= '通 过';
				$class_chcek[$i] = 'OK';
				$check=1;
			}
			else
			{
				$check_msg[$i].='777属性检测不通过';
				$check=0;
				$class_chcek[$i] = 'WARN';
			}
		}
		if($check!=1) $disabled = 'disabled';
		
		require('templates/inspect.htm');
		break;
	}
	case 'db_setup':
	{
		if((int)$setup==1)
		{
			$db_prefix = trim(strip_tags($db_prefix));
			$db_host = trim(strip_tags($db_host));
			$db_username = trim(strip_tags($db_username));
			$db_pass = trim(strip_tags($db_pass));
			$db_name = trim(strip_tags($db_name));
			$config="<?php
                   \$con_db_host = '$db_host';
                   \$con_db_id   = '$db_username';
                   \$con_db_pass = '$db_pass';
                   \$con_db_name = '$db_name';
                   \$tablepre    =  '$db_prefix';
                   \$db_charset  =  'utf8';";

			$fp=fopen("../config/config_db.php",'w+');
			fputs($fp,$config);
			fclose($fp);
			//为商铺添加配置
			if(file_exists('../union/config/config_db.php'))
			{
			    @chmod('../union/config/config_db.php',0777);
			    @unlink('../union/config/config_db.php');
			}
			copy('../config/config_db.php','../union/config/config_db.php');
			//删除主站缓存
            $cache_list =get_dirinfo('../data/cache');
            foreach ($cache_list as  $val)
            {
            	@chmod("../data/cache/$val",0777);
                @unlink("../data/cache/$val");
            }
            //更新商铺共用缓存
            $cache_list =get_dirinfo('../union/data/cache');
            foreach ($cache_list as  $val)
            {
                if(!is_dir("../union/data/cache/$val"))
                {
                	@chmod("../union/data/cache/$val",0777);
                	@unlink("../union/data/cache/$val");
                }
            }
			
			$db = mysql_connect($db_host,$db_username,$db_pass) or die('连接数据库失败: ' . mysql_error());
			if(!@mysql_select_db($db_name)) mysql_query("CREATE DATABASE $db_name DEFAULT CHARACTER SET utf8") or die('创建数据库失败'.mysql_error());
			
			mysql_select_db($db_name);
			if(mysql_get_server_info()>='4.1') mysql_query("set names utf8"); 
			else echo "<SCRIPT language=JavaScript>alert('您的mysql版本过低，虽然能完全安装不影响使用,但官方建议您升级到mysql4.1.0以上');</SCRIPT>";  

			$content=readover("install.sql");
			
			$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
			require('templates/db_setup.htm');
			exit;
		}
		else require('templates/databasesetup.htm');
		break;
	}
	case 'adminsetup':
	{
		if((int)$setup==1)
		{
			$regname = trim(strip_tags($regname));
			$base_pass = base64_encode(trim(strip_tags($regpwd)));
			$regpwd = md5(trim(strip_tags($regpwd)));
			$email = trim(strip_tags($email));
		    $m_now_time = time();
			require_once '../config/config_db.php';
			
			$link = mysql_connect($con_db_host,$con_db_id,$con_db_pass) or die('连接数据库失败: ' . mysql_error());
			mysql_select_db($con_db_name);
			if(mysql_get_server_info()>4.1) mysql_query("set names utf8"); 
			if(mysql_get_server_info()>'5.0.1') mysql_query("SET sql_mode=''",$link);
			
			$q=mysql_query("SELECT COUNT(*) AS cnt FROM `{$tablepre}member_table` WHERE uid='1' LIMIT 1",$link);
			$tmp_arr=mysql_fetch_array($q);
			if($tmp_arr['cnt']==0)
			{
			    $sql = " INSERT INTO `{$tablepre}member_table` set
                         member_class = '3',
                         member_id = '$regname',
                         member_pass = '$regpwd',
                         base_pass = '$base_pass',
                         member_name = '管理员',
                         adminid ='1',
                         member_email = '$email',
                         register_date = '$m_now_time'";
			    mysql_query("INSERT INTO `{$tablepre}address` (`id`, `add_name`, `is_buy`, `consignee`, `address`, `zipcode`, `tel`, `mobile`, `email`, `province`, `city`, `member_id`) VALUES (1, 'mvmmall', 1, '蔡先生', '芗城区天下广场', '363000', '0596-2921706 ', '12345678', 'mvmmall@mvmmall.cn', '福建', '漳州', 1);");
			}
			else 
			{
				$sql="UPDATE `{$tablepre}member_table` SET 
				      member_id='$regname', 
				      member_pass='$regpwd',
				      base_pass='$base_pass' 
				      WHERE uid='1'";
			}
			mysql_query($sql) or die('写入数据库失败: ' . mysql_error());
			//@chmod('../config/config_db.php',0554);
			$mall_url='http://'.$_SERVER['HTTP_HOST'].str_replace(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),'',str_replace('\\','/',dirname(dirname(__FILE__))));
			mysql_query("UPDATE `{$tablepre}config` SET 
			             cf_value='$mall_url' 
			             WHERE cf_name='mm_mall_url' AND supplier_id='0'");
			$fp = fopen('../config/install.lock', 'w');
			fwrite($fp,$config);
			fclose($fp);
			require('templates/finished.htm');
		}
		else require('templates/adminsetup.htm');
		break;
	}
	default:
	{
		require('templates/index.htm');
	}
}

function creat_table($content)
{
	global $installinfo,$db_prefix,$db_setup;
	$sql=explode("\n",$content);
	$query='';
	foreach($sql as $key => $value)
	{
		$value=trim($value);
		if(!$value || $value[0]=='#') continue;
		if(eregi("\;$",$value))
		{
			$query.=$value;
			if(eregi("^CREATE",$query))
			{
				$name=substr($query,13,strpos($query,'(')-13);
				$c_name=str_replace('mvm_',$db_prefix,$name);
				$i++;
			    $query = str_replace('TYPE=MyISAM',"ENGINE=MyISAM DEFAULT CHARSET=utf8",$query);
			}
			$query = str_replace('mvm_',$db_prefix,$query);;
			if(!mysql_query($query))
			{
				$db_setup=0;
				echo '<li class="WARN">出错：'.mysql_error().'<br/>sql:'.$query.'</li>';
			}
			else
			{
				if(eregi("^CREATE",$query))
				{
					$installinfo='<li class="OK"><font color="#0000EE">建立数据表'.$i.'</font>'.$c_name.' ... <font color="#0000EE">完成</font></li>';
					echo $installinfo;
				}
				$db_setup=1;
			}
			$query='';
		}
		else $query.=$value;
	}
}
function readover($filename,$method="rb")
{
	if($handle=@fopen($filename,$method))
	{
		flock($handle,LOCK_SH);
		$filedata=@fread($handle,filesize($filename));
		fclose($handle);
	}
	return $filedata;
}
function daddslashes($string, $force = 0)
{
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force)
	{
		if(is_array($string))
		{
			foreach($string as $key => $val) $string[$key] = daddslashes($val, $force);
		}
		else $string = addslashes($string);
		
	}
	return $string;
}
// 求指定目录(文件夹)中的信息
function get_dirinfo($dir_path,$file='')
{
	$area_lord = @opendir($dir_path);
	while($dir_info = @readdir($area_lord))
	{
	    if($dir_info != '.' && $dir_info != '..' && $dir_info != '.svn' && $dir_info != 'index.htm')
	    {
	        if ($file!='')
	        {
	            if(eregi($file,$dir_info)) $dir_file_name[] = $dir_info;
	        }
	        else $dir_file_name[] = $dir_info;
	    }
	}
	closedir($area_lord);
	return $dir_file_name;
}