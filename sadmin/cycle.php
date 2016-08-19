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
 * $Id: cycle.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

if($action=='list')
{
    $q = $db->query("SELECT uid,img,link,title,cat_tag 
                     FROM `{$tablepre}cycle` 
                     WHERE `supplier_id`='$page_member_id' 
                     ORDER BY cat_tag DESC");
    while ($rtl = $db->fetch_array($q))
    {
    	$rtl['img'] = ProcImgPath($rtl['img']);
        $photo[] = $rtl;
    }
    include template('sadmin_cycle');
}
else if($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $title=dhtmlchars($title);
        $link=dhtmlchars($link);
        $cat_tag=(int)$cat_tag;
        $art_id=(int)$art_id;
        $gallery='';
        $gallery_small='';
        if (!$_FILES['gallery']['name']) show_msg('请上传轮转图片');
        
        require_once 'include/upfile.class.php';
        $rowset = new upfile('gif,jpg,png,bmp','union/images/banner/');
        $gallery = $rowset->upload('gallery');
        
        $row=array(
            'title'=>$title,
            'link'=>$link,
            'img'=>$gallery,
            'img_small'=>$gallery_small,
            'supplier_id'=>$page_member_id,
            'art_id'=>$art_id,
            'cat_tag'=>$cat_tag
        );
        $db->insert("`{$tablepre}cycle`",$row);
        $db->free_result();
    }
    show_msg('添加成功',"sadmin.php?module=$module&action=list");
}
else if($action=='edit')
{
    $uid=(int)$uid;
    $cycle=$db->get_one("SELECT * FROM `{$tablepre}cycle` WHERE uid='$uid' AND supplier_id='$page_member_id'");
    
    if($_POST && (int)$step==1)
    {
        if(!$cycle) sadmin_show_msg('检索不到指定的轮转广告',$p_url);
        
        $title=dhtmlchars($title);
        $link=dhtmlchars($link);
        $cat_tag=(int)$cat_tag;
        $art_id=(int)$art_id;
        $gallery=$cycle['img'];
        $gallery_small=$cycle['img_small'];
        
        if($_FILES['gallery']['name']!='')
        {
            file_unlink('union/'.$cycle['img'],'bucket');
            //file_unlink(ProcImgPath($cycle['img_small']));
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','union/images/banner/');
            $gallery = $rowset->upload('gallery');
        }
        
        $row=array(
            'title'=>$title,
            'link'=>$link,
            'img'=>$gallery,
            'img_small'=>$gallery_small,
            'supplier_id'=>$page_member_id,
            'art_id'=>$art_id,
            'cat_tag'=>$cat_tag
        );
        $db->update("`{$tablepre}cycle`",$row," uid='$cycle[uid]' ");
        $db->free_result();
        move_page(base64_decode($p_url));
    }
    
    if(!$cycle) exit('检索不到指定的轮转广告');
    
    require_once template('sadmin_cycle_add');
    exit;
}
else if($action=='del')
{
	$rtl = $db->get_one("SELECT uid,img,img_small FROM `{$tablepre}cycle` WHERE uid='$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
	if(!$rtl) exit;
	file_unlink('union/'.$rtl['img'],'bucket');
	//file_unlink(ProcImgPath($rtl['img_small']));
    $db->query("DELETE FROM `{$tablepre}cycle` WHERE uid='$rtl[uid]'");
    exit;
}