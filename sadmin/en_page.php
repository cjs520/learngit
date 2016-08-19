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
 * $Id: en_page.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/pic.class.php';
if($action=='list')
{
    $list = $db->get_one("SELECT uid,member_id FROM `{$tablepre}member_table` WHERE `uid` = '$page_member_id' LIMIT 1");
    $shop=$db->get_one("SELECT m_uid,video_code,promote_pic,sellshow,supplier_notice FROM `{$tablepre}member_shop` WHERE m_uid='$page_member_id' LIMIT 1");
    if(!$shop) show_msg('检索不到指定商铺');
    $list=array_merge($list,$shop);
    
	if($_POST && (int)$step==1)
	{
	    require_once MVMMALL_ROOT.'include/upfile.class.php';
		
		$rows=array(
    		'supplier_notice' => $supplier_notice
		);
    		
    	//上传黄页图1
    	if ($_FILES['promote_pic']['name']!='')
    	{
    	    file_unlink(str_replace('@!promote_pic', '',$list['promote_pic']),'bucket');
			$rowset = new upfile('gif,jpg,png,bmp','upload/promote_pic/');
			$promote_pic = $rowset->upload('promote_pic');
			$promote_pic=$promote_pic.'@!promote_pic';
			$rows['promote_pic']=$promote_pic;
		}
		
		$rows = dhtmlchars($rows);
    	$rows['video_code']=$_POST['video_code'];
    	$db->update("{$tablepre}member_shop",$rows,"m_uid='$page_member_id'");
		
    	//上传企业图
    	if($_FILES['certi']['name'][0]!='')
    	{
            $path="union/upload/$page_member_id/certi/";
    	    $f = new upfile('jpg,jpeg,gif,png',$path);
            foreach ($_FILES['certi']['name'] as $key => $value)
            {
                $upload = array(
                    'name' => $_FILES['certi']['name'][$key],
                    'type' => $_FILES['certi']['type'][$key],
                    'tmp_name' => $_FILES['certi']['tmp_name'][$key],
                    'error' => $_FILES['certi']['error'][$key],
                    'size' => $_FILES['certi']['size'][$key],
                );
                $img = $f->upload($upload);
                $b_img=$img;
                //$b_img=ProcImgPath($img);
                $s_img=$img.'@!promote_pic_big';
                //$b_img=str_replace('union/','',$b_img);
                //$s_img=str_replace('union/','',$s_img);
                $db->query("INSERT INTO `{$tablepre}certi` SET 
                            b_img='$b_img',
                            s_img='$s_img',
                            supplier_id='$page_member_id'");
            }
    	}
    	
		show_msg('更新成功',"sadmin.php?module=$module&action=list");
	}
	$list['promote_pic']=IMG_URL.$list['promote_pic'];
	extract($list,EXTR_OVERWRITE);
    $url="$_URL[0]/shopshow.php?sid=$page_member_id";
    
    
    $certi_img=array();
    $q=$db->query("SELECT uid,b_img,s_img FROM `{$tablepre}certi` WHERE supplier_id='$page_member_id'");
    while($rtl=$db->fetch_array($q))
    {
        $rtl['b_img']=ProcImgPath($rtl['b_img']);
        $rtl['s_img']=ProcImgPath($rtl['s_img']);

        $certi_img[]=$rtl;
    }
    
	include template('sadmin_enpage');
}
else if($action=='del_certi')
{
    $uid=(int)$uid;
    if($uid<=0) exit;
    $rtl=$db->get_one("SELECT * FROM `{$tablepre}certi` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
    if(!$rtl) exit;
    file_unlink('union/'.$rtl['b_img'],'bucket');
    //file_unlink(ProcImgPath($rtl['s_img']));
    $db->query("DELETE FROM `{$tablepre}certi` WHERE uid='$uid'");
    exit;
}