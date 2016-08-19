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
 * $Id: xb_money.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if($action=='list')
{
    $shop_file['xb_money']=currency($shop_file['xb_money']);
    $member_xb=array();
	require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}xb_money","money_id='$shop_file[m_id]'");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,money_reason,type,money_add,money_sess,money_left,money_id,register_date 
                     FROM `{$tablepre}xb_money` 
                     WHERE money_id='$shop_file[m_id]' 
                     ORDER BY register_date DESC
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
        $rtl['money_left']=currency($rtl['money_left']);
        $rtl['money_add']>=0?$rtl['add']=currency($rtl['money_add']):$rtl['minus']=currency($rtl['money_add']);
        $member_xb[]=$rtl;
    }
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&page=");
	include template('sadmin_xb_money');
}