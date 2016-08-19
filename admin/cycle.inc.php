<?php

/**
 * MVM_MALL 网上商店系统 flash轮转广告
 * ============================================================================
 * 版权所有 (C) 2007-2010 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-09-05 $
 * $Id: cycle.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if ($action=='list')
{
    $q = $db->query("SELECT * FROM `{$tablepre}cycle` WHERE supplier_id='0' ORDER BY cat_tag DESC");
    while ($rtl = $db->fetch_array($q))
    {
    	 $rtl['img']=IMG_URL.$rtl['img'];
          $photo[] = $rtl;
    }
    include template('cycle');
    footer();
}
else if ($action=='add')
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
        $rowset = new upfile('gif,jpg,png,bmp','images/banner/');
        $gallery = $rowset->upload('gallery');
        
        $row=array(
            'title'=>$title,
            'link'=>$link,
            'img'=>$gallery,
            'img_small'=>$gallery_small,
            'supplier_id'=>0,
            'art_id'=>$art_id,
            'cat_tag'=>$cat_tag
        );
        $db->insert("`{$tablepre}cycle`",$row);
        $db->free_result();
        admin_log("添加轮转广告：$title");
    }
    show_msg('添加成功',"admincp.php?module=$module&action=list");
}
else if($action=='edit')
{
    $uid=(int)$uid;
    $cycle=$db->get_one("SELECT * FROM `{$tablepre}cycle` WHERE uid='$uid' AND supplier_id='0'");
    
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
            file_unlink($cycle['img'],'buctket');
            //file_unlink($cycle['img_small']);
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','images/banner/');
            $gallery = $rowset->upload('gallery');
        }
        
        $row=array(
            'title'=>$title,
            'link'=>$link,
            'img'=>$gallery,
            'img_small'=>$gallery_small,
            'supplier_id'=>0,
            'art_id'=>$art_id,
            'cat_tag'=>$cat_tag
        );
        $db->update("`{$tablepre}cycle`",$row," uid='$cycle[uid]' ");
        $db->free_result();
        admin_log("编辑轮转广告：$title");
        move_page(base64_decode($p_url));
    }
    
    if(!$cycle) exit('检索不到指定的轮转广告');
    
    require_once template('cycle_add');
    exit;
}
else if ($action=='del')
{
	$uid=(int)$uid;
    $rt_img = $db->get_one("SELECT img,uid,img_small,title FROM `{$tablepre}cycle` WHERE uid='$uid' LIMIT 1");
    if(!$rt_img) exit;
    admin_log("删除轮转广告：$rt_img[title]");
    file_unlink($rt_img['img'],'buctket');
    //file_unlink($rt_img['img_small']);
    $db->query("DELETE FROM `{$tablepre}cycle` WHERE uid='$uid'");
    exit;
}
else show_msg('pass_worng');
