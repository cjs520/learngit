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

$index_config='data/malldata/index_floor.config.php';
if(file_exists($index_config)) include $index_config;

if ($action=='list')
{
    if(!$index_floor || !is_array($index_floor)) $index_floor=array();
    
    if($_POST && (int)$step==1)
    {
        $floor=(int)$floor;
        if($floor<0 && $floor>=10) show_msg('您指定的楼层有误');
        
        $index_floor[$floor]['more_link']=dhtmlchars($more_link);
        
        if ($_FILES['floor_icon']['name']!='')
        {
            file_unlink($index_floor[$floor]['floor_icon'],'bucket');
            
            require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp',"images/banner/");
            $floor_icon_tmp = $f->upload('floor_icon');
            $index_floor[$floor]['floor_icon'] =IMG_URL.$floor_icon_tmp;//pic::PicZoom($floor_icon_tmp ,200,50,array(255,255,255),true);
        }
        
        file_put_contents($index_config,'<?php $index_floor='.var_export($index_floor,true).'; ?>');
        admin_log("设置首页楼层：第".strval($floor+1)."层");
        
        show_msg('楼层设置成功',"admincp.php?module=$module&action=$action");
    }
    
    for($i=0;$i<10;$i++)
    {
        if(!$index_floor[$i]['floor_icon'] || !@fopen($index_floor[$i]['floor_icon'],'r'))
            $index_floor[$i]['floor_icon']='images/noimages/noproduct.jpg';
        
            
    }
    
    require template('index_floor');
    footer();
}
else show_msg('pass_worng');
