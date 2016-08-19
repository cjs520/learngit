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
    $arr_grade=array();
    $q = $db->query("SELECT group_id,group_name,degree FROM `{$tablepre}grade_table` 
                     WHERE is_admin='0'
                     ORDER BY `degree` DESC");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl_tmp=$db->get_one("SELECT discount FROM `{$tablepre}grade_discount` WHERE group_id='$rtl[group_id]' AND supplier_id='$page_member_id' LIMIT 1");
        if($rtl_tmp) $rtl['discount']=$rtl_tmp['discount'];
        $arr_grade[] = $rtl;
    }
    $db->free_result();
    
	include template('sadmin_grade');
}
else if($action=='edit')
{
    $uid=(int)$uid;
    $grade=$db->get_one("SELECT group_id FROM `{$tablepre}grade_table` WHERE group_id='$uid' LIMIT 1");
    if(!$grade) exit('ERROR:检索不到指定的等级');
    $row=array(
        'group_id'=>$uid,
        'supplier_id'=>$page_member_id,
        'discount'=>floatval($discount)
    );
    $db->replace("`{$tablepre}grade_discount`",$row);
    $db->free_result();
    
    exit('OK:设置成功');
}
else if($action=='del')
{
    $uid=(int)$uid;
    $db->query("DELETE FROM `{$tablepre}grade_discount` WHERE group_id='$uid' AND supplier_id='$page_member_id'");
    $db->free_result();
    exit('OK:删除成功');
}