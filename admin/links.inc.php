<?php

/**
 * MVM_MALL 网上商店系统  友情连接管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: links.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}forumlinks_table"," `supplier_id`='0'");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT * FROM `{$tablepre}forumlinks_table` 
                     WHERE `supplier_id`='0' 
                     ORDER BY `displayorder` 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['edit'] = "admincp.php?module=$module&action=edit&uid=$rtl[id]";
        $rtl['logo'] && $rtl['logo']= "<img src=\"$rtl[logo]\" border=\"0\"/>";
        $links_rt[]  = $rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
    require_once template('links');
    footer();
}
else if ($action=='add')
{
    if($_POST && (int)$step==1)
    {
        if ($_FILES['logo_file']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','images/links/');
            $logo = $rowset->upload('logo_file');
        }
        $name = dhtmlchars($name);
        $url = dhtmlchars($url);
        $note = dhtmlchars($note);
        $disp = (int)$disp;
        $db->query("INSERT INTO `{$tablepre}forumlinks_table` SET 
                    displayorder = '$disp',
                    name = '$name',
                    url='$url',
                    note='$note',
                    logo='$logo',
                    supplier_id='0'");
        admin_log("添加友情链接：$name");
        $cache->delete('links',0);
        move_page(base64_decode($p_url));
    }
    require_once template('links_add');
    exit;
}
else if ($action=='edit' && is_numeric($uid))
{
	$uid = (int)$uid;
    $links_rt = $db->get_one("SELECT * FROM {$tablepre}forumlinks_table WHERE id='$uid' AND `supplier_id`='0' LIMIT 1");
    if($_POST && (int)$step==1)
    {
    	$logo =  $links_rt['logo'];
        if ($_FILES['logo_file']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','images/links/');
            $logo = $rowset->upload('logo_file');
            $links_rt['logo'] && file_unlink($links_rt['logo'],'buctket');
        }
        $name = dhtmlchars($name);
        $url = dhtmlchars($url);
        $note = dhtmlchars($note);
        $disp = (int)$disp;
        
        $db->query("UPDATE `{$tablepre}forumlinks_table` SET 
                    displayorder = '$disp',
                    name = '$name',
                    url='$url',
                    note='$note',
                    logo='$logo' 
                    WHERE id='$uid' AND `supplier_id`='0'");
        admin_log("编辑友情链接：$name");
        $cache->delete('links',0);
        move_page(base64_decode($p_url));
    }
    @extract($links_rt,EXTR_OVERWRITE);
    require_once template('links_add');
    exit;
}
else if ($action=='del')
{
    if(is_array($uid_check))
    {
        for($i=0;$i<count($uid_check);$i++)
        {
            $id = (int)$uid_check[$i];
            $rt_links = $db->get_one("SELECT id,logo,name FROM `{$tablepre}forumlinks_table` 
                                      WHERE id='$id' AND `supplier_id`='0' 
                                      LIMIT 1");
            $rt_links['logo']!='' && file_unlink($rt_links['logo'],'buctket');
            admin_log("删除友情链接：$rt_links[name]");
            $db->query("DELETE FROM `{$tablepre}forumlinks_table` WHERE id='$id' AND `supplier_id`='0'");
            show_msg('删除成功',"admincp.php?module=$module&action=list");
        }
    }
    else
    {
    	$uid=(int)$uid;
    	$rtl=$db->get_one("SELECT logo,name FROM `{$tablepre}forumlinks_table` WHERE id='$uid' AND supplier_id='0'");
    	file_unlink($rtl['logo'],'buctket');
    	admin_log("删除友情链接：$rtl[name]");
        $db->query("DELETE FROM `{$tablepre}forumlinks_table` WHERE id='$uid' AND `supplier_id`='0' ");
    }
    $cache->delete('links',0);
    exit;
} 
else if($action=='ajax')
{
	$id=(int)$id;
	$id==0 && exit;
	$db->query("UPDATE `{$tablepre}forumlinks_table` SET $field='$v' WHERE id='$id' AND `supplier_id`='0'");
}
else show_msg('pass_worng');

