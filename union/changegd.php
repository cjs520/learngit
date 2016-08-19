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
 * $Id: changegd.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
if($action=='list')
{
    require_once 'include/pager.class.php';

    $search_sql=" WHERE supplier_id='$page_member_id' AND approval=1";
    $arr_goods=array();
    $page=(int)$page;
    $total_count = $db->counter("`{$tablepre}goods_change`",$search_sql);
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();

    $q=$db->query("SELECT uid,goods_name,goods_sale_price,goods_sale_point,goods_file1,goods_status,supplier_id,goods_hit 
                   FROM `{$tablepre}goods_change` 
                   FORCE INDEX (`supplier_id`) 
                   $search_sql 
                   ORDER BY register_date 
                   LIMIT $from_record,$list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['goods_sale_point']=(int)$rtl['goods_sale_point'];
        $rtl=goods_array($rtl);
        $rtl['url']=GetBaseUrl('changegd_detail',$rtl['uid'],'action',$rtl['supplier_id']);
        $arr_goods[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("changegd.php?&action=$action&page=");
    
	$mm_mall_title="积分换购 -- $mm_mall_title";
	require_once MVMMALL_ROOT . 'header.php';
	require_once template('changegd');
	footer();
}
else show_msg('pass_worng');