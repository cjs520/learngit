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
 * $Id: salegd.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
if($action=='list')
{
    require_once 'include/pager.class.php';

    $search_sql=" WHERE supplier_id='$page_member_id' AND start_date<='$m_now_time' AND end_date>'$m_now_time'";
    $arr_goods=array();
    $page=(int)$page;
    $total_count = $db->counter("`{$tablepre}goods_onsale`",$search_sql);
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();

    $q=$db->query("SELECT uid,goods_name,goods_sale_price,goods_file1,goods_status,goods_stock,supplier_id,goods_hit,start_date,end_date
                   FROM `{$tablepre}goods_onsale` 
                   $search_sql 
                   ORDER BY start_date 
                   LIMIT $from_record,$list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $detail=$db->get_one("SELECT goods_market_price FROM `{$tablepre}goods_onsale_detail` WHERE g_uid='$rtl[uid]' LIMIT 1");
        $rtl['goods_market_price']=$detail['goods_market_price'];
        $rtl['discount']=round($rtl['goods_sale_price']/$rtl['goods_market_price']*10,1);

        $rtl['sale_price']=$rtl['goods_sale_price'];
        $rtl['end_date']=date('Y-m-d',$rtl['end_date']);

        $rtl=goods_array($rtl);
        $rtl['url']=GetBaseUrl('salegd_detail',$rtl['uid'],'action',$rtl['supplier_id']);
        $arr_goods[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("group.php?&action=$action&page=");
    
	$mm_mall_title="限时促销 -- $mm_mall_title";
	require 'header.php';
	require_once template('goods_salegd');
	footer();
}
else show_msg('pass_worng');