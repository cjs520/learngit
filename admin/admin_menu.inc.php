<?php

/**
 * MVM_MALL 网上商店系统 后台管理菜单
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-11-12 $
 * $Id: admin_menu.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if ($action=='list')
{
	$arr_tree = menu_tree();
	require_once template('admin_menu');
	footer();
}
else if($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $menu_id = (int)$menu_id;
        $menu_order = (int)$menu_order;
        $menu_name = dhtmlchars($menu_name);
        $menu_url = dhtmlchars($menu_url);
        $m_module=dhtmlchars($m_module);
        $m_action=dhtmlchars($m_action);
        $m_type=dhtmlchars($m_type);

        $sql = " INSERT INTO `{$tablepre}admin_menu` SET
                 menu_id = '$menu_id',
                 menu_name = '$menu_name',
                 menu_order = '$menu_order',
                 menu_url = '$menu_url',
                 m_module='$m_module',
                 m_action='$m_action',
                 m_type='$m_type'";
        $db->query($sql);
        $db->free_result();
        admin_log("添加后台菜单：$menu_name");
        move_page(base64_decode($p_url));
    }
    $admin_menu = admin_menu('menu_id',$menu_id);
    require_once template('admin_menu_add');
    exit;
   
}
else if($action=='edit')
{
    $uid=(int)$uid;
    $menu_rt = $db->get_one("SELECT * FROM `{$tablepre}admin_menu` WHERE uid='$uid' LIMIT 1");
    if($_POST && (int)$step==1)
    {
        $menu_id = (int)$menu_id;
        $menu_order = (int)$menu_order;
        $menu_name = dhtmlchars($menu_name);
        $menu_url = dhtmlchars($menu_url);
        $m_module=dhtmlchars($m_module);
        $m_action=dhtmlchars($m_action);
        $m_type=dhtmlchars($m_type);

        $sql = " UPDATE `{$tablepre}admin_menu` SET
                 menu_id = '$menu_id',
                 menu_name = '$menu_name',
                 menu_order = '$menu_order',
                 menu_url = '$menu_url',
                 m_module='$m_module',
                 m_action='$m_action',
                 m_type='$m_type' 
                 WHERE uid = '$uid'";
        $db->query($sql);
        $db->free_result();
        admin_log("修改后台菜单：$menu_rt[menu_name]");
        move_page(base64_decode($p_url));
    }
    @extract($menu_rt,EXTR_OVERWRITE);
    $admin_menu = admin_menu('menu_id',$menu_id);
    require_once template('admin_menu_add');
    exit;
   
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,menu_name FROM `{$tablepre}admin_menu` WHERE menu_id='$uid' LIMIT 1");
    if($rtl) exit('ERROR:指定的菜单项存在下级菜单，无法删除');
    
    $rtl=$db->get_one("SELECT menu_name FROM `{$tablepre}admin_menu` WHERE uid='$uid' LIMIT 1");
    if($rtl)
    {
        admin_log("删除后台菜单：$rtl[menu_name]");
	    $db->query("DELETE FROM `{$tablepre}admin_menu` WHERE uid='$uid'");
	    $db->free_result();
    }
    
	exit('OK:删除成功');
}
else if($action=='bat_view')
{
	$db->query("UPDATE `{$tablepre}admin_menu` SET 
	            shop_level0='0',
	            shop_level1='0',
	            shop_level2='0',
	            shop_level3='0',
	            shop_level4='0',
	            main_show='0'");
	
	if(is_array($shop_level0) && sizeof($shop_level0)>0)
	{
		$filter=implode(',',$shop_level0);
		$db->query("UPDATE `{$tablepre}admin_menu` SET shop_level0='1' WHERE `uid` IN ($filter)");
	}
	if(is_array($shop_level1) && sizeof($shop_level1)>0)
	{
		$filter=implode(',',$shop_level1);
		$db->query("UPDATE `{$tablepre}admin_menu` SET shop_level1='1' WHERE `uid` IN ($filter)");
	}
	if(is_array($shop_level2) && sizeof($shop_level2)>0)
	{
		$filter=implode(',',$shop_level2);
		$db->query("UPDATE `{$tablepre}admin_menu` SET shop_level2='1' WHERE `uid` IN ($filter)");
	}
	if(is_array($shop_level3) && sizeof($shop_level3)>0)
	{
		$filter=implode(',',$shop_level3);
		$db->query("UPDATE `{$tablepre}admin_menu` SET shop_level3='1' WHERE `uid` IN ($filter)");
	}
	if(is_array($shop_level4) && sizeof($shop_level4)>0)
	{
		$filter=implode(',',$shop_level4);
		$db->query("UPDATE `{$tablepre}admin_menu` SET shop_level4='1' WHERE `uid` IN ($filter)");
	}
	if(is_array($main_show) && sizeof($main_show)>0)
	{
		$filter=implode(',',$main_show);
		$db->query("UPDATE `{$tablepre}admin_menu` SET main_show='1' WHERE `uid` IN ($filter)");
	}
	
	admin_log("设置后台菜单商铺查看权限");
	show_msg('设置成功','admincp.php?module=admin_menu&action=list');
}
else if($action=='ajax')
{
    $field=dhtmlchars($field);
    $uid=$uid;
    $v=dhtmlchars($_POST['v']);
    $db->query("UPDATE `{$tablepre}admin_menu` SET `$field`='$v' WHERE uid='$uid'");
    $db->free_result();
    exit;
}
else show_msg('pass_worng');


//分类下拉菜单
function admin_menu($name,$select=0,$js='')
{
	
	global $cache;
    $all_str  = "<select name=\"$name\" id=\"$name\"   $js><option selected value=0>----</option>";
    foreach (menu_tree() as $key=>$val)
    {
        $all_str.= " <option value=".$key.' ';
        $key==$select && $all_str.= '  selected';
        $all_str.= ">" .str_repeat( "---", $val[2]) . " " . $val[0] . "</option>\n";
    }
    $all_str .= '</select> ';
    return $all_str;
}

function menu_tree()
{
    global $db,$tablepre;
    require_once 'include/category_tree.class.php';
    $tree = new tree();
    $q = $db->query("SELECT * FROM `{$tablepre}admin_menu` ORDER BY menu_id,menu_order ASC");
    while ($rt = $db->fetch_array($q))
    {
        $tree->new_node($rt['uid'],$rt['menu_name'],$rt['menu_id'],$rt['use_home'],$rt['menu_order'],array($rt['supplier_view'],$rt['shop_level0'],$rt['shop_level1'],$rt['shop_level2'],$rt['shop_level3'],$rt['shop_level4'],$rt['main_show']));
    }
    $db->free_result();
    return $tree->get_childs() ;
}
?>
