<?php

/**
 * MVM_MALL 网上商店系统 商品拍卖
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-12 $
 * $Id: auction.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';

if($action=='list')
{
	require_once 'include/pager.class.php';
	
	$search_sql=" WHERE approval=1 AND supplier_id='$page_member_id' AND start_date<'$m_now_time' AND end_date>'$m_now_time' AND is_complete=0";
	$arr_auction=array();
	$page=(int)$page;
	$total_count = $db->counter("`{$tablepre}goods_auction`",$search_sql);
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$q=$db->query("SELECT uid,goods_name,goods_file1,goods_status,supplier_id,goods_hit,start_date,end_date,end_price,start_price,bid_add,is_complete
                   FROM `{$tablepre}goods_auction` 
                   $search_sql 
                   ORDER BY start_date 
                   LIMIT $from_record,$list_num");
	while ($rtl=$db->fetch_array($q))
	{
	    $rtl['left_time']=$rtl['end_date']-$m_now_time;

	    $rtl['btn_cls']='but_pai';
	    if($rtl['start_date']>$m_now_time)
	    {
	        $rtl['sold_out']='sold_begin';
	        $rtl['btn_cls']='but_start';
	    }
	    else if($rtl['end_date']<$m_now_time || $rtl['is_complete']>0)
	    {
	        $rtl['sold_out']='sold_over';
	        $rtl['btn_cls']='but_gray';
	    }

	    $rtl['end_price']=currency($rtl['end_price']);
	    $rtl['bid_add']=currency($rtl['bid_add']);
	    $rtl_tmp=$db->get_one("SELECT price FROM `{$tablepre}goods_auction_join` WHERE g_uid='$rtl[uid]' ORDER BY register_date DESC LIMIT 1");
        $rtl['cur_price']=$rtl_tmp?currency($rtl_tmp['price']):'还未出价';

	    $rtl=goods_array($rtl);
	    $rtl['url']=GetBaseUrl('auction_detail',$rtl['uid'],'action',$rtl['supplier_id']);
	    $arr_auction[]=$rtl;
	}
	$db->free_result();
	$page_list = $rowset->link("auction.php?&action=$action&page=");
	
	$mm_mall_title='拍卖';
    require 'header.php';
	require_once template('auction');
	footer();
}
else show_msg('pass_worng');
