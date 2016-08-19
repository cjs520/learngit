<?php

/**
 * MVM_MALL 网上商店系统  团购活动管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: group.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    require_once 'include/pager.class.php';
    
    $arr_img=glob("union/shopimg/user_img/$m_check_uid/image/*.*");
    $imgs=array();
    $total_count = sizeof($arr_img);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    
    for($i=$from_record;$i<$from_record+$list_num;$i++)
    {
    	if(!isset($arr_img[$i])) break;
    	$imgs[]=$arr_img[$i];
    }
    
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
    require_once template('pics');
    footer();
}
else if($action=='del')
{
	$path=dhtmlchars($path);
	file_unlink($path);
	admin_log("删除编辑器图片：$path");
	exit('ok');
}
else if($action=='delete')
{
	if(is_array($path))
	{
		foreach ($path as $val)
		{
		    file_unlink($val);
		    admin_log("删除编辑器图片：$val");
		}
	}
	show_msg('删除成功',"admincp.php?module=$module&action=list");
}
else show_msg('pass_worng');
