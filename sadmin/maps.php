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
 * $Id: maps.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if($action=='list')
{
	if($_POST && (int)$step==1)
    {
    	$lng=(int)$lng;
    	$lat=(int)$lat;
    	if($lng==0 || $lat==0) show_msg('请在地图上做出标注');
    	$map_title=trim($map_title);
    	$map_tip=trim($map_tip);
    	$member_tip=array(
    		    'lng'=>$lng,
    		    'lat'=>$lat,
    		    'map_title'=>$map_title,
    		    'map_tip'=>$map_tip
    	);
    	$db->update("`{$tablepre}member_shop`",$member_tip," `m_uid`='$page_member_id' ");
    	
    	show_msg('地图更新成功',"sadmin.php?module=$module&action=list");
    }
    $shop_file['isSupplier']<=1 && show_msg('您还不是正式提供商，无法进行地区设置');
    $rtl=$db->get_one("SELECT province,city,county,lng,lat,map_title,map_tip FROM `{$tablepre}member_shop` WHERE m_uid='$page_member_id' LIMIT 1");
    extract($rtl,EXTR_OVERWRITE);
    
	include template('sadmin_maps');
}