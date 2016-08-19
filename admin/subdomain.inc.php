<?php

/**
 * MVM_MALL 网上商店系统 标签管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-11-15 $
 * $Id: subdomain.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
$domain_file='data/malldata/sudomain.dat';
if($action=='list')
{
	if($_POST && (int)$step==1)
	{
		$subdomain=dhtmlchars($subdomain);
		$arr_subdomain=explode('|',$subdomain);
		$arr_subdomain=array_unique($arr_subdomain);
		$subdomain=implode('|',$arr_subdomain);
		file_put_contents($domain_file,$subdomain);
		admin_log("设置保留域名");
		show_msg('设置成功',"admincp.php?module=$module&action=list");
	}
    $preserve_domain='';
    if(file_exists($domain_file)) $preserve_domain=file_get_contents($domain_file); 
    
    require_once template('subdomain');
    footer();
}