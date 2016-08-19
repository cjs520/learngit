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
 * $Id: popup.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
$cycle_path="union/data/malldata/cycle_{$page_member_id}.data.php";

if($action=='list')
{
    include $cycle_path;
    include template('sadmin_popup_add');
}
else if($action=='add')
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
    show_msg('内容添加成功',"sadmin.php?module=$module&action=list");
}
else if($action=='del')
{
    file_unlink($cycle_path);
    show_msg('弹窗内容清除成功',"sadmin.php?module=$module&action=list");
}

