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
 * $Id: consult.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

if($action=='list')
{
    require_once MVMMALL_ROOT.'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}ask_order"," `supplier_id`='$page_member_id' ");
    $ask_list=array();
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,name,tel,mobile,address,reg_time FROM `{$tablepre}ask_order` 
                     WHERE supplier_id='$page_member_id' 
                     ORDER BY uid DESC
                     LIMIT $from_record, $list_num");
    while ($rt = $db->fetch_array($q))
    {
        $rt['del'] = "admincp.php?module=$module&action=del&uid=$rt[uid]&ajax=ajax";
        $rt['reg_time'] = date('Y-m-d h:i:s',$rt['reg_time']);
        $ask_list[] = $rt;
    }
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&page=");
    include template('sadmin_consult');
    footer();
}
else if($action=='edit')
{
    $uid=(int)$uid;
	$rtl=$db->get_one("SELECT * FROM `{$tablepre}ask_order` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
	if(!$rtl) exit('检索不到指定询单');
	extract($rtl,EXTR_OVERWRITE);
	
	$reg_time=date('Y-m-d h:i:s',$reg_time);
	require_once template('sadmin_consult_add');
	exit;
}
else if($action=='del')
{
    if (is_numeric($uid)) $db->query("DELETE FROM `{$tablepre}ask_order` WHERE uid='$uid' AND supplier_id='$page_member_id'");
    elseif (is_array($uid_check))
    {
        $arr_uids=array();
        foreach ($uid_check as $key=>$val)
        {
            $val=(int)$val;
            if($val>0) $arr_uids[]=$val;
        }
        $arr_uids=array_unique($arr_uids);
        if($arr_uids)
        {
            $str_uid=implode(',',$arr_uids);
            $db->query("DELETE FROM `{$tablepre}ask_order` WHERE uid IN ($str_uid) AND supplier_id='$page_member_id'");
        }
    }
    
    $ajax != 'ajax' && show_msg('删除成功',"sadmin.php?module=$module&action=list");
}
