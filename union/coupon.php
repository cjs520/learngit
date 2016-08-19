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
 * $Id: coupon.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
if($action=='list')
{
    $arr_coupon=array();
    $q=$db->query("SELECT uid,name,coupon_img,handout_type,price_lbound,end_date,discount,sale_price 
                   FROM `{$tablepre}coupon_cat` 
                   WHERE supplier_id='$page_member_id' 
                   ORDER BY od DESC");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['sale_price']=(int)$rtl['sale_price'];
        $rtl['end_date']=date('Y-m-d',$rtl['end_date']);
        $arr_coupon[]=$rtl;
    }
    $db->free_result();
    
	$mm_mall_title="优惠券";
	require 'header.php';
	require_once template('coupon');
	footer();
}
else show_msg('pass_worng');