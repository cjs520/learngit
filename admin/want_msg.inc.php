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
 * $Id: want_msg.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $t=(int)$t==1?1:0;
    if($t==0)
    {
        $table="{$tablepre}want_supply_msg";
    }
    else
    {
        $table="{$tablepre}want_buy_msg";
    }
    
    $arr_msg=array();
    require_once 'include/pager.class.php';
    $total_count = $db->counter("$table");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT * FROM `$table` WHERE uid>0
                     ORDER BY `uid` DESC  
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        if($t==0)
        {
            $supply=$db->get_one("SELECT goods_name FROM `{$tablepre}want_supply` WHERE uid='$rtl[supply_id]' LIMIT 1");
            $rtl['goods_name']=$supply['goods_name'];
            $rtl['url']="infor_supply_detail.php?action=$rtl[supply_id]";
        }
        else
        {
            $buy=$db->get_one("SELECT goods_name FROM `{$tablepre}want_buy` WHERE uid='$rtl[buy_id]' LIMIT 1");
            $rtl['goods_name']=$buy['goods_name'];
            $rtl['url']="infor_buy_detail.php?action=$rtl[buy_id]";
        }
        $rtl['status']=$rtl['approval_date']>10?'已采纳':'未采纳';
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
        $arr_msg[] = $rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&t=$t&page=");
    require_once template('want_msg');
    footer();
}
else if ($action=='del')
{
    $t=(int)$t==1?1:0;
    if($t==0)
    {
        $table="{$tablepre}want_supply_msg";
        $type='商机留言';
    }
    else
    {
        $table="{$tablepre}want_buy_msg";
        $type='求购留言';
    }
    
    if(is_array($uid_check))
    {
        for($i=0;$i<count($uid_check);$i++)
        {
            $uid = (int)$uid_check[$i];
            $rtl = $db->get_one("SELECT uid,m_id FROM `$table` WHERE uid='$uid' LIMIT 1");
            admin_log("删除{$rtl[m_id]}的$type");
            $db->query("DELETE FROM `$table` WHERE uid='$uid'");
        }
    }
    else
    {
    	$uid=(int)$uid;
    	$rtl=$db->get_one("SELECT uid,m_id FROM `$table` WHERE uid='$uid' LIMIT 1");
    	admin_log("删除{$rtl[m_id]}的$type");
        $db->query("DELETE FROM `$table` WHERE uid='$uid'");
    }
    if($_POST) move_page("admincp.php?module=$module&action=list&t=$t");
    else exit;
}
else show_msg('pass_worng');

