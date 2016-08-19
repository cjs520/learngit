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
 * $Id: tpl.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/pic.class.php';

if($action=='list')
{
    $arr_tpl=array();
    $q = $db->query("SELECT * FROM `{$tablepre}tpl` ORDER BY sellshow");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['tpl_name']=trim($rtl['tpl_name']);
        if(!$rtl['tpl_name']) $rtl['tpl_name']='未命名';
        
        $rtl['tpl_code']=$rtl['sellshow']==1?"./union/templates/$rtl[tpl_code]/":"./union/show_templates/$rtl[tpl_code]/";
        
        $rtl['sellshow']=$rtl['sellshow']==1?'销售型':'展示型';
        if(!$rtl['s_img'] || !file_exists($rtl['s_img'])) $rtl['s_img']='images/noimages/noproduct.jpg';
        
        $rtl['price']=currency($rtl['price']);
        $arr_tpl[]=$rtl;
    }
    
    require_once template('tpl');
    footer();
}
else if ($action=='edit')
{
	$uid = (int)$uid;
    $tpl = $db->get_one("SELECT * FROM `{$tablepre}tpl` WHERE uid='$uid' LIMIT 1");
    if(!$tpl) exit('检索不到您指定的模板');
    
    if($_POST && $step==1)
    {
        if ($_FILES['img']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,jpeg','upload/tpl/');
            $b_img = $rowset->upload('img');
            $s_img=pic::PicZoom($b_img,240,320,array(255,255,255),false);
            
            file_unlink($tpl['s_img']);
            file_unlink($tpl['b_img']);
            
            $tpl['s_img']=$s_img;
            $tpl['b_img']=$b_img;
        }
        $tpl['tpl_name']=dhtmlchars($tpl_name);
        $tpl['price']=floatval($price);
        
        unset($tpl['uid']);
        
        $db->update("`{$tablepre}tpl`",$tpl," uid='$uid' ");
        $db->free_result();
        admin_log("编辑模板资料：$tpl_name");
        move_page(base64_decode($p_url));
    }
    @extract($tpl,EXTR_OVERWRITE);
    $tpl_code=$sellshow==1?"./union/templates/$tpl_code/":"./union/show_templates/$tpl_code/";
    $sellshow=$sellshow==1?'销售型':'展示型';
    
    require_once template('tpl_add');
    exit;
}
else if($action=='import_tpl')
{
    $arr_tpl=array();
    $arr_tpl_id=array();
    $arr_img=array();
    
	$q = $db->query("SELECT * FROM `{$tablepre}tpl`");
	while($rtl=$db->fetch_array($q))
	{
	    $arr_tpl[]=$rtl['tpl_code'];
	    $arr_tpl_id[]=$rtl['uid'];
	    $arr_img[]=array($rtl['b_img'],$rtl['s_img']);
	}
	$db->free_result();
	
	foreach ($arr_tpl as $key=>$val)
	{
	    if(!is_dir("union/templates/$val") && !is_dir("union/show_templates/$val"))
	    {
	        file_unlink($arr_img[$key][0]);
	        file_unlink($arr_img[$key][1]);
	        $db->query("DELETE FROM `{$tablepre}tpl` WHERE uid='{$arr_tpl_id[$key]}'");
	        
	        unset($arr_tpl[$key]);
	        unset($arr_tpl_id[$key]);
	        unset($arr_img[$key]);
	    }
	}
	
	//导入销售型模板
	$o_dir=dir('union/templates');
	while($tpl=$o_dir->read())
	{
	    if(in_array($tpl,array('.','..'))) continue;
	    if(strstr($tpl,'_wap')) continue;
	    if(in_array($tpl,$arr_tpl)) continue;
	    $db->query("INSERT INTO `{$tablepre}tpl` SET 
	                tpl_code='$tpl',
	                sellshow='1',
	                price='0'");
	}
	$db->free_result();
	
	//导入展示型模板
	$o_dir=dir('union/show_templates');
	while($tpl=$o_dir->read())
	{
	    if(in_array($tpl,array('.','..'))) continue;
	    if(strstr($tpl,'_wap')) continue;
	    if(in_array($tpl,$arr_tpl)) continue;
	    $db->query("INSERT INTO `{$tablepre}tpl` SET 
	                tpl_code='$tpl',
	                sellshow='2',
	                price='0'");
	}
    $db->free_result();
	admin_log("同步模版");
    exit('OK');
}
else show_msg('pass_worng');

