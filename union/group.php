<?php

/**
 * MVM_MALL 网上商店系统 团购活动
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-04-12 $
 * $Id: group.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
if($action=='list')
{
    require_once 'include/pager.class.php';


    $search_sql=" WHERE supplier_id='$page_member_id' AND start_date<='$m_now_time' AND end_date>'$m_now_time' AND approval=1";
    $arr_group=array();
    $page=(int)$page;
    $total_count = $db->counter("`{$tablepre}goods_group`",$search_sql);
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();

    $q=$db->query("SELECT uid,goods_name,goods_sale_price,goods_file1,goods_status,goods_stock,supplier_id,goods_hit,start_date,end_date
                   FROM `{$tablepre}goods_group` 
                   $search_sql 
                   ORDER BY start_date 
                   LIMIT $from_record,$list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $detail=$db->get_one("SELECT goods_market_price FROM `{$tablepre}goods_group_detail` WHERE g_uid='$rtl[uid]' LIMIT 1");
        $rtl['goods_market_price']=$detail['goods_market_price'];
        $rtl['discount']=round($rtl['goods_sale_price']/$rtl['goods_market_price']*10,1);
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_goods` WHERE goods_table='{$tablepre}goods_group' AND g_uid='$rtl[uid]' AND status=1");
        $rtl['join_num']=(int)$rtl_tmp['cnt'];
    
        $rtl['sale_price']=$rtl['goods_sale_price'];
        if($rtl['goods_stock']<=0) $rtl['sold_out']='sold_out';
        else if($rtl['start_date']>$m_now_time) $rtl['sold_out']='sold_begin';
        else if($rtl['end_date']<$m_now_time) $rtl['sold_out']='sold_over';
        if($rtl['sold_out']) $rtl['btn_cls']='but_gray';

        $rtl=goods_array($rtl);
        $rtl['url']=GetBaseUrl('group_detail',$rtl['uid'],'action',$rtl['supplier_id']);
        $arr_group[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("group.php?&action=$action&page=");
    
	$mm_mall_title="团购";
	require_once MVMMALL_ROOT . 'header.php';
	require_once template('group');
	footer();
}
else show_msg('pass_worng');