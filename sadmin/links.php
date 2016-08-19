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
 * $Id: links.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if($action=='list')
{
	require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}forumlinks_table","supplier_id='$page_member_id'");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT * FROM `{$tablepre}forumlinks_table` 
                     WHERE `supplier_id`='$page_member_id' 
                     ORDER BY `displayorder` DESC,id DESC 
                     LIMIT $from_record, $list_num");
    while ($rt = $db->fetch_array($q))
    {
        $rt['edit'] = "sadmin.php?module=$module&action=edit&uid=$rt[id]";
        $rt['del'] = "sadmin.php?module=$module&action=del&per=ajax&uid=$rt[id]";
        $rt['logo'] = $rt['logo']?ProcImgPath($rt['logo']):'images/noimages/noproduct.jpg';
        $rt['logo'] = '<img src="'.$rt['logo'].'" border="0"/>';
        $links_rt[] = $rt;
    }
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&page=");
    $db->free_result();
	include template('sadmin_links');
}
else if($action=='add')
{
	if($_POST && (int)$step==1)
	{
        if ($_FILES['logo_file']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','union/images/links/');
            $logo = $rowset->upload('logo_file');
            $logo = str_replace('union/','',$logo);
        }
        $name = dhtmlchars($name);
        $url = dhtmlchars($_POST['url']);
        $note = dhtmlchars($note);
        $disp = (int)$disp;
        $db->query("INSERT INTO `{$tablepre}forumlinks_table` SET 
                    displayorder = '$disp',
                    name = '$name',
                    url='$url',
                    note='$note',
                    logo='$logo',
                    supplier_id='$page_member_id'");
        $db->free_result();
        $cache->delete('links',$page_member_id);        
        move_page(base64_decode($p_url));
    }
    $url='';
    include template('sadmin_links_add');
    exit;
}
else if($action=='edit')
{
	$links_rt = $db->get_one("SELECT * FROM {$tablepre}forumlinks_table WHERE id='$uid' AND `supplier_id`='$page_member_id' LIMIT 1");

	if($_POST && (int)$step==1)
    {
        if ($_FILES['logo_file']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','union/images/links/');
            $logo = $rowset->upload('logo_file');
            $logo = str_replace('union/','',$logo);
            $links_rt['logo'] && file_unlink('union/'.$links_rt['logo'],'bucket');
        }
        else $logo = $links_rt['logo'];
        
        $name = dhtmlchars($name);
        $url = dhtmlchars($_POST['url']);
        $note = dhtmlchars($note);
        $disp = (int)$disp;
        $uid = (int)$uid;
        $db->query("UPDATE `{$tablepre}forumlinks_table` SET 
                    displayorder = '$disp',
                    name = '$name',
                    url='$url',
                    note='$note',
                    logo='$logo' 
                    WHERE id='$uid' AND `supplier_id`='$page_member_id'");
        $db->free_result();
        $cache->delete('links',$page_member_id);
        move_page(base64_decode($p_url));
    }
    @extract($links_rt,EXTR_OVERWRITE);
    include template('sadmin_links_add');
    exit;
}
else if($action=='del')
{
	if (is_array($uid_check))
	{
		foreach ($uid_check as $key=>$val)
		{
			$val=(int)$val;
			if($val<=0) unset($uid_check[$key]);
		}
		do
		{
			$uid_check=array_unique($uid_check);
			if(!$uid_check) break;
			$str_uid=implode(',',$uid_check);
			$q=$db->query("SELECT id,logo FROM `{$tablepre}forumlinks_table` WHERE id IN ($str_uid) AND supplier_id='$page_member_id'");
			$uids=array();
			while($rtl=$db->fetch_array($q))
			{
				$uids[]=$rtl['id'];
				$rtl['logo'] && file_unlink('union/'.$rtl['logo'],'bucket');
			}
			
			if(!$uids) break;
			$str_uid=implode(',',$uids);
			$db->query("DELETE FROM `{$tablepre}forumlinks_table` WHERE id IN ($str_uid)");
			$db->free_result();
		}while(0);
    }
    else if(is_numeric($uid))
    {
    	do
    	{
    		$links=$db->get_one("SELECT id,logo FROM `{$tablepre}forumlinks_table` WHERE id='$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
    	    if(!$links) break;
    	    $links['logo'] && file_unlink('union/'.$links['logo'],'bucket');
    	    $db->query("DELETE FROM `{$tablepre}forumlinks_table` WHERE id='$links[id]'");
    	    $db->free_result();
    	}while(0);
    }
    $cache->delete('links',$page_member_id);
    $ajax!='ajax' && show_msg('删除成功',"sadmin.php?module=$module&action=list");
    exit;
}