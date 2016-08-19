<?php

/**
 * MVM_MALL 网上商店系统 首页文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: shipping.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if($action=='list')
{
	$list = get_dirinfo('include/shipping','.class.php');
    foreach ($list as $val)
    {
        require_once "include/shipping/$val";
        $name = str_replace('.class.php','',$val);
        $ship = $db->get_one("SELECT uid,class_name FROM `{$tablepre}ship_table` WHERE class_name='$name' AND `supplier_id`='$page_member_id' LIMIT 1");
        
        $shipping[$name]['insert'] = "sadmin.php?module=$module&action=add&name=$name";
        if($ship)
        {
            $shipping[$name]['install'] = 1;
            $shipping[$name]['uid'] = $ship['uid'];
            $shipping[$name]['edit'] = "sadmin.php?module=area&action=list&uid=$ship[uid]";
            $shipping[$name]['del'] = "sadmin.php?module=$module&action=del&uid=$ship[uid]";
        }
        
    }
    include template('sadmin_shipping');
}
else if($action=='add')
{
	if(!isset($name)) show_msg('指定的物流方式错误');
	$name = dhtmlchars($name);
	if(!file_exists("include/shipping/$name.class.php")) show_msg('检索不到指定的物流方式');
	
    require_once "include/shipping/$name.class.php";
    $db->query("REPLACE INTO `{$tablepre}ship_table` (class_name,name,supplier_id) 
                VALUES ('$name','{$shipping[$name]['name']}','$page_member_id')");
    
    $shop_file['shop_step']=$shop_file['shop_step'] | 4;
    $db->query("UPDATE `{$tablepre}member_shop` SET shop_step='$shop_file[shop_step]' WHERE m_uid='$page_member_id'");
    $db->free_result();
    
    move_page("sadmin.php?module=$module&action=list");
}
else if($action=='del')
{
	$uid=(int)$uid;
	if($uid>0)
	{
		$db->query("DELETE FROM `{$tablepre}ship_table` WHERE uid = '$uid' AND supplier_id='$page_member_id'");
        $db->query("DELETE FROM `{$tablepre}area_table` WHERE ship_uid = '$uid' AND supplier_id='$page_member_id'");
        $db->free_result();
	}
	exit('OK');
}