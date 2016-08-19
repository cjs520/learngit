<?php

/**
 * MVM_MALL 网上商店系统  配送插件管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: member_set.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if ($action=='list')
{
    if($_POST && (int)$step==1)
    {
    	$str_write="<?php \n";
    	//允许上传商品的数量
    	foreach ($allow_goods_items as $key=>$val) $allow_goods_items[$key]=intval($val);
    	$str_write.='$allow_goods_items='.var_export($allow_goods_items,true).";\n";
    	//上传商品空间
    	foreach ($allow_upload_size as $key=>$val) $allow_upload_size[$key]=floatval($val)*1024*1024;
    	$str_write.='$allow_upload_size='.var_export($allow_upload_size,true).";\n";
    	//升级费用
    	foreach ($update_money as $key=>$val) $update_money[$key]=floatval($val);
    	$str_write.='$update_money='.var_export($update_money,true).";\n";
    	//允许页面数量
    	foreach ($allow_page_items as $key=>$val) $allow_page_items[$key]=intval($val);
    	$str_write.='$allow_page_items='.var_export($allow_page_items,true).";\n";
    	//模板
    	$arr_tpl=array();
    	foreach ($tpl_data as $key=>$val)
    	{
    		if(!isset($arr_tpl[$key])) $arr_tpl[$key]=array();
    		$arr=explode(',',$val);
    		foreach ($arr as $v)
    		{
    			$v=strval($v);
    			$arr_tpl[$key][$v]=$v;
    		}
    	}
    	$tpl_data=$arr_tpl;
    	$str_write.='$tpl_data='.var_export($tpl_data,true).";\n";
    	
    	$str_write.='?>';
    	file_put_contents('data/malldata/config.inc.php',$str_write);
    	copy('data/malldata/config.inc.php','union/data/malldata/config.inc.php');
    	admin_log("设置商铺权限");
    	
    	show_msg('修改成功',"admincp.php?module=$module&action=list");
    }
    
    require template('member_set');
    footer();
}
else show_msg('pass_worng');
