<?php
/**
 * MVM_MALL 网上商店系统  管理员行动管�?
 * ============================================================================
 * 版权所�?(C) 2007-2010 www.mvmmall.cn，并保留所有权利�?
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布�?
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: man_shop.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/
if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
	$uid=(int)$uid;
	if($m_check_uid!=1) show_msg('您不是系统管理员，无权管理下级商铺','close');
	$_SESSION['user']['man_shop']=$uid;
	move_page('sadmin.php?module=index');
}
?>