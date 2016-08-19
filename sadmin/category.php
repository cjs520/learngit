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
 * $Id: wosmj.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

if($action=='list')
{
	$arr_tree = $ucache->get_cache('tree',$page_member_id);
    
	include template('sadmin_category');
}
else if($action=='add')
{
	if($_POST && (int)$step==1)
	{
		if ($_FILES['category_file1']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp','union/images/category/',500*1024);
            $category_file1_text = $f->upload('category_file1');
            $category_file1_text = str_replace('union/','',$category_file1_text);
        }
        $category_id = (int)$category_id;
        
        $sql = "INSERT INTO `{$tablepre}category` SET
                category_id = '$category_id',
                category_name = '$category_name',
                category_key = '$category_key',
                category_desc = '$category_desc',
                category_rank = '$category_rank',
				category_file1 = '$category_file1_text',
                supplier_id = '$page_member_id'";
        $db->query($sql);
        $db->free_result();
        $cache->delete('tree',$page_member_id);
        $cache->delete('right_tree',$page_member_id);
        move_page(base64_decode($p_url));
    } 
    
    $cat_menu = cat_menu('category_id',$category_id,$page_member_id);
    
    $category_file='暂无图标';
    include template('sadmin_category_add');
	exit;
}
else if($action=='edit')
{
	$cat_rt = $db->get_one("SELECT * FROM `{$tablepre}category` WHERE uid='$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
	
    if($_POST && (int)$step==1)
    {
    	$category_id = (int)$category_id;
    	if($uid==$category_id) sadmin_show_msg('不能选自身为上级',$p_url);
    	
    	$category_file1_text = $cat_rt['category_file1'];
        if ($_FILES['category_file1']['name']!='')
        {
            require_once MVMMALL_ROOT.'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp','union/images/category/',500*1024);
            $category_file1_text = $f->upload('category_file1');
            $category_file1_text=str_replace('union/','',$category_file1_text);
        }
        
        $sql = "UPDATE `{$tablepre}category` SET
                category_id = '$category_id',
                category_name = '$category_name',
                category_key = '$category_key',
                category_desc = '$category_desc',
                category_rank = '$category_rank',
				category_file1 = '$category_file1_text'
                WHERE uid = '$uid'";
        $db->query($sql);
        $db->free_result();
        $cache->delete('tree',$page_member_id);
        $cache->delete('right_tree',$page_member_id);
        move_page(base64_decode($p_url));
    }
        
    @extract($cat_rt,EXTR_OVERWRITE);

    $grant_menu = drop_menu($m_class_array,'category_grant',$category_grant);
    $cat_menu = cat_menu('category_id',$category_id,$page_member_id);

    if($category_file1) $category_file='<img src="'.ProcImgPath($category_file1).'" rel="del_img" style="cursor:pointer;" border="0" />';
    include template('sadmin_category_add');
	exit;
}
else if($action=='del')
{
	if ($type=='delimg')
	{
		$uid=(int)$uid;
        $rs = $db->get_one("SELECT category_file1,uid FROM `{$tablepre}category` 
                            WHERE uid='$uid' AND `supplier_id`='$page_member_id' 
                            LIMIT 1");
        if($rs['category_file1'])
        {
            file_unlink(ProcImgPath($rs['category_file1']));
            $db->query("UPDATE `{$tablepre}category` SET category_file1='' WHERE uid='$uid' AND `supplier_id`='$page_member_id'");
        }
    }
    else
    {
    	
        $cat_rt = $db->get_one("SELECT * FROM `{$tablepre}category` WHERE category_id='$uid' LIMIT 1");
        if($cat_rt) exit('该分类下有子分类存在，不能删除');
        
        $rtl=$db->get_one("SELECT uid,category_file1 FROM `{$tablepre}category` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
        if(!$rtl) exit('指定的分类不存在');
        $rtl['category_file1'] && file_unlink(ProcImgPath($rtl['category_file1']));
        
        $db->query("DELETE FROM `{$tablepre}category` WHERE uid = '$uid' AND `supplier_id`='$page_member_id'");
        $db->free_result();
        $cache->delete('tree',$page_member_id);
        $cache->delete('right_tree',$page_member_id);
    }
    exit('删除成功');
}
else if($action=='all_delete')
{
	if(is_array($uid_check) && $uid_check)
	{
		foreach ($uid_check as $val) delete_menu($val);
	}
	$cache->delete('tree',$page_member_id);
	$cache->delete('right_tree',$page_member_id);
	show_msg('删除成功',"sadmin.php?module=$module&action=list");
}
else if($action=='copy')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT * FROM `{$tablepre}category` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
    if(!$rtl) exit('检索不到指定分类');
    $rtl['category_id']=$rtl['uid'];
    unset($rtl['uid']);
    unset($rtl['att_list']);
    $rtl['category_name']="复制 $rtl[category_name]";
    $db->insert("`{$tablepre}category`",$rtl);
    $cache->delete('tree',$page_member_id);
    $cache->delete('right_tree',$page_member_id);
    exit('OK:复制成功');
}

function delete_menu($id=0)
{
	global $db,$tablepre,$page_member_id;
	if((int)$id==0) return;
	$cat_rt = $db->get_one("SELECT uid FROM `{$tablepre}category` WHERE category_id='$id' AND `supplier_id`='$page_member_id'");
	foreach($cat_rt as $val)
	{
		delete_menu($val['uid']);
	}
    $rt = $db->get_one("SELECT * FROM `{$tablepre}category` WHERE uid = '$id' AND `supplier_id`='$page_member_id'");
    if($rt) file_exists(ProcImgPath($rt['category_file1']));
    
    $db->query("DELETE FROM `{$tablepre}category`  WHERE uid = '$id' AND `supplier_id`='$page_member_id'");
    $db->free_result(1);
}
