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
 * $Id: task_log.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $arr_log=array();
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}log_task");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT * FROM `{$tablepre}log_task` FORCE INDEX (register_date) 
                     ORDER BY `register_date` DESC  
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
        $arr_log[]=$rtl;
    }
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
    require_once template('task_log');
    footer();
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT module FROM `{$tablepre}log_task` WHERE uid='$uid' LIMIT 1");
    if($rtl)
    {
        $db->query("DELETE FROM `{$tablepre}log_task` WHERE uid='$uid'");
        $db->free_result();
        admin_log("删除任务日志：$rtl[module]");
    }
    
    exit('OK:删除成功');
}
else show_msg('pass_worng');

