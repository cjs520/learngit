<?php

/**
 * MVM_MALL 网上商店系统  弹窗管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-09-19 $
 * $Id: popup.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
$cycle_path='data/malldata/cycle.data.php';

if ($action=='list')
{
    include $cycle_path;
    require_once template('popup_add');
    footer();
}
else if ($action=='add')
{
    $subject=dhtmlchars($subject);
    $left=(int)$left;
    $top=(int)$top;
    $width=(int)$width;
    $height=(int)$height;
    
    $row=array(
        'subject'=>$subject,
        'left'=>$left>0?$left:0,
        'top'=>$top>0?$top:0,
        'width'=>$width>0?$width:350,
        'height'=>$height>0?$height:350,
        'body'=>stripslashes($_POST['body'])
    );
    file_put_contents($cycle_path,'<?php '.PHP_EOL.'$popup='.var_export($row,true).';');
    admin_log("添加弹窗广告");
    show_msg('内容添加成功',"admincp.php?module=$module&action=list");
}
else if ($action=='del')
{
    file_unlink($cycle_path);
    admin_log("删除弹窗广告");
    show_msg('弹窗内容清除成功',"admincp.php?module=$module&action=list");
}
else show_msg('pass_worng');
