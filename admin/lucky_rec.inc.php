<?php

/**
 * MVM_MALL 网上商店系统  团购活动管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: lucky_rec.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

if($action=='list')
{
    $lucky_uid=(int)$lucky_uid;
    $ordersn=dhtmlchars($ordersn);
	$arr_lucky_rec=array();
    
    require_once 'include/pager.class.php';
	
    $order_sql=" ORDER BY uid DESC";
	$search_sql=" TRUE ";
	if($lucky_uid>0)
	{
	    $search_sql.=" AND lucky_uid='$lucky_uid'";
	    $order_sql='';
	}
	if($ordersn) $search_sql.=" AND ordersn LIKE '$ordersn%' ";
	
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}lucky_rec` WHERE $search_sql");
    $total_count = $rtl['cnt'];
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $sql = "SELECT uid,m_id,name,lucky_g_uid,ordersn,reg_date,approval_date,lucky_uid,name,address,tel,memo 
            FROM `{$tablepre}lucky_rec`  
            WHERE $search_sql
            $order_sql
            LIMIT $from_record, $list_num";
    $q = $db->query($sql);
    while ($rtl = $db->fetch_array($q))
    {
        if($rtl['lucky_g_uid']<=0) $rtl['goods_name']='未获奖';
        else
        {
            $rtl_tmp=$db->get_one("SELECT goods_name FROM `{$tablepre}lucky_goods` WHERE uid='$rtl[lucky_g_uid]' LIMIT 1");
            $rtl['goods_name']=$rtl_tmp['goods_name'];
        }
        
        $rtl_tmp=$db->get_one("SELECT name FROM `{$tablepre}lucky_table` WHERE uid='$rtl[lucky_uid]' LIMIT 1");
        $rtl['lucky_name']=$rtl_tmp['name'];
        
        $rtl['reg_date']=date('Y-m-d H:i',$rtl['reg_date']);
        if($rtl['lucky_g_uid']<=0) $rtl['is_send']='无需发货';
        else $rtl['is_send']=$rtl['approval_date']<10?'未发货':'已发货';
        
        $arr_lucky_rec[]=$rtl;
    }
    $db->free_result();
    
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&lucky_uid=$lucky_uid&ordersn=$ordersn&page=");
    
    require_once template('lucky_rec');
    footer();
}
else if ($action == 'edit')
{
    $uid=(int)$uid;
    
    if($_POST && (int)$step==1)
    {
        $address=dhtmlchars($address);
        $name=dhtmlchars($name);
        $tel=dhtmlchars($tel);
        $memo=dhtmlchars($memo);
        $db->query("UPDATE `{$tablepre}lucky_rec` SET 
                    address='$address',
                    name='$name',
                    tel='$tel',
                    memo='$memo' 
                    WHERE uid='$uid'");
        move_page(base64_decode($p_url));
    }
    
    $lucky_rec=$db->get_one("SELECT * FROM `{$tablepre}lucky_rec` WHERE uid='$uid' LIMIT 1");
    if($lucky_rec['lucky_g_uid']<=0) exit('ERR:该记录没有获奖，无需查看');
    $lucky_goods=$db->get_one("SELECT * FROM `{$tablepre}lucky_goods` WHERE uid='$lucky_rec[lucky_g_uid]' LIMIT 1");
    $lucky=$db->get_one("SELECT * FROM `{$tablepre}lucky_table` WHERE uid='$lucky_rec[lucky_uid]' LIMIT 1");
    
    $lucky_rec['reg_date']=date('Y-m-d H:i:s',$lucky_rec['reg_date']);
    $lucky_rec['is_send']=$lucky_rec['approval_date']>10?'已发货':'未发货';
    
    require_once template('lucky_rec_add');
    exit;
}
else if($action=='del')
{
    $uid = (int)$uid;
    $rec=$db->get_one("SELECT ordersn FROM `{$tablepre}lucky_rec` WHERE uid='$uid' LIMIT 1");
    if($rec)
    {
        $db->query("DELETE FROM `{$tablepre}lucky_rec` WHERE uid='$uid'");
        admin_log("删除抽奖记录：$rec[ordersn]");
        $db->free_result();
    }
    
    exit;
}
else if($action=='check')
{
    $uid=(int)$uid;
    $rec=$db->get_one("SELECT ordersn FROM `{$tablepre}lucky_rec` WHERE uid='$uid' LIMIT 1");
    if($rec)
    {
        $db->query("UPDATE `{$tablepre}lucky_rec` SET approval_date='$m_now_time' WHERE uid='$uid'");
         admin_log("抽奖奖品发货：$rec[ordersn]");
    }
    
    exit('OK:发货成功');
}
else admin_msg('pass_worng');
