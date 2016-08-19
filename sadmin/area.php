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
 * $Id: area.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

if($action=='list')
{
	$uid=(int)$uid;
	$ship = $db->get_one("SELECT uid,name FROM  `{$tablepre}ship_table` WHERE uid = '$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
	if(!$ship) show_msg('找不到指定配送方式');
	
	$area = array();
    $q = $db->query("SELECT uid,ship_uid,area_name,region FROM `{$tablepre}area_table` WHERE ship_uid='$uid' ");
    while ($rtl = $db->fetch_array($q))
    {
        $area[] = $rtl;
    }
    include template('sadmin_area');
}
else if($action=='add')
{
	$ship_uid=(int)$ship_uid;
	$ship = $db->get_one("SELECT uid,class_name FROM `{$tablepre}ship_table` WHERE uid='$ship_uid' AND `supplier_id`='$page_member_id' LIMIT 1");
	if(!$ship) exit('检索不到指定配送方式');
	
	if($_POST && (int)$step==1)
    {
        $area_name = dhtmlchars($area_name);
        $region = implode(',',$region);
        
        require_once "include/shipping/$ship[class_name].class.php";
        $config = array();
        foreach ($shipping[$ship['class_name']]['cfg'] as $key => $val)
        {
            $config[$key]['name'] = $val['name'];
            $config[$key]['value'] = $$val['name'];
            $config[$key]['label'] = $val['label'];
        }
        
        $config = serialize($config);
        $sql = "INSERT INTO `{$tablepre}area_table` SET
                area_name = '$area_name',
                ship_uid = '$ship_uid',
                config = '$config',
                region = '$region',
                supplier_id='$page_member_id'";
        $db->query($sql);
        
        move_page(base64_decode($p_url));
    } 
    require_once "include/shipping/$ship[class_name].class.php";
    $fields = array();
    foreach ($shipping[$ship['class_name']]['cfg'] as $key => $val)
    {
        $fields[$key]['name'] = $val['name'];
        $fields[$key]['value'] = $val['value'];
        $fields[$key]['label'] = $val['label'];
    }

    include template('sadmin_area_add');
	exit;
}
else if($action=='edit')
{
    $ship_uid=(int)$ship_uid;
	$ship = $db->get_one("SELECT uid,class_name FROM `{$tablepre}ship_table` WHERE uid='$ship_uid' AND `supplier_id`='$page_member_id' LIMIT 1");
	!$ship && exit('检索不到指定配送方式');
	$uid=(int)$uid;
		
	if($_POST && (int)$step==1)
    {
        $area_name = dhtmlchars($area_name);
        $region = implode(',',$region);
        
        require_once "include/shipping/$ship[class_name].class.php";
        $config = array();
        foreach ($shipping[$ship['class_name']]['cfg'] as $key => $val)
        {
            $config[$key]['name'] = $val['name'];
            $config[$key]['value'] = $$val['name'];
            $config[$key]['label'] = $val['label'];
        }

        $config = serialize($config);
        $sql = "UPDATE `{$tablepre}area_table` SET
                area_name = '$area_name',
                ship_uid = '$ship_uid',
                config = '$config',
                region = '$region' 
                WHERE uid='$uid'";
        $db->query($sql);
        
        move_page(base64_decode($p_url));

    }
    $area = $db->get_one("SELECT * FROM `{$tablepre}area_table` WHERE uid = '$uid' AND supplier_id='$page_member_id' LIMIT 1");
    if(!$area) exit('检索不到指定的配送地区');
    
    $fields = unserialize($area['config']);
    $area['region'] = explode(',',$area['region']);
    
    include template('sadmin_area_add');
	exit;
}
else if($action=='del')
{
    $uid=(int)$uid;
	$db->query("DELETE FROM `{$tablepre}area_table` WHERE uid='$uid' AND supplier_id='$page_member_id'");
	exit;
}

