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
 * $Id: admin_log.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $use_index=" FORCE INDEX (`register_date`)";
    if($m_id)
    {
        $search_sql=" WHERE m_id='$m_id'";
        $use_index=" FORCE INDEX (`m_id`)";
    }
    
    $arr_log=array();
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}admin_log",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 20;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT * FROM `{$tablepre}admin_log` $use_index 
                     $search_sql 
                     ORDER BY `register_date` DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['register_date']=date('Y-m-d H:i:s',$rtl['register_date']);
        $arr_log[]=$rtl;
    }
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&m_id=".urlencode($m_id)."&page=");
    require_once template('admin_log');
    footer();
}
else show_msg('pass_worng');

