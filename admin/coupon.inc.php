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
 * $Id: coupon.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $search_sql='';
    
    $status=(int)$status;
    if($status==1) $search_sql=" WHERE od<=1000 ";
    else if($status==2) $search_sql=" WHERE od=10000 ";
    else if($status==3) $search_sql=" WHERE od>10000 ";
    
    $arr_coupon=array();
    require_once MVMMALL_ROOT.'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}coupon_cat",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,name,start_date,end_date,discount,supplier_id,od,member_num FROM `{$tablepre}coupon_cat` 
                     $search_sql 
                     ORDER BY od DESC
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        $rtl['shop_name']=$shop['shop_name'];
        
        $rtl['start_date']=date('Y-m-d',$rtl['start_date']);
        $rtl['end_date']=date('Y-m-d',$rtl['end_date']);
        
        if($rtl['od']<=1000) $rtl['status']='普通券';
        else if($rtl['od']==10000) $rtl['status']='<b>主站推广，未审核</b>';
        else if($rtl['od']>10000) $rtl['status']='<b style="color:red;">主站推广，已审核</b>';
        
        $arr_coupon[]  = $rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&status=$status&page=");
    
    $arr_status=array(0=>'-- 全部 --',1=>'普通券',2=>'主站推广，未审核',3=>'主站推广，已审核');
    $sel_status=drop_menu($arr_status,'status',$status);
    
    require_once template('coupon');
    footer();
}
else if ($action=='edit')
{
	$uid = (int)$uid;
    $coupon = $db->get_one("SELECT * FROM `{$tablepre}coupon_cat` WHERE uid='$uid' LIMIT 1");
    $db->free_result();
    if($_POST && $step==1)
    {
        $name=dhtmlchars($name);
        $discount=floatval($discount);
        if(!$name) sadmin_show_msg('请填写优惠券名称',$p_url);
	    if($discount<=0) sadmin_show_msg('请正确填写优惠券金额',$p_url);
	    
        $handout_type=(int)$handout_type;
        if(!in_array($handout_type,array(0,1,2))) $handout_type=0;
        
	    $sale_price=0;
	    if($handout_type==1) $sale_price=floatval($change_point);
	    else if($handout_type==2) $sale_price=floatval($rtn_price);
	    
	    $od=(int)$od;
	    
	    $row=array(
            'name'=>$name,
            'handout_type'=>$handout_type,
            'discount'=>$discount,
            'start_date'=>strtotime($start_date),
            'end_date'=>strtotime($end_date)+24*3600-1,
            'sale_price'=>$sale_price,
            'price_lbound'=>floatval($price_lbound),
            'od'=>$od
        );
        $db->update("`{$tablepre}coupon_cat`",$row," uid='$uid' ");
        $db->free_result();
        admin_log("编辑优惠券：$name");
        
        move_page(base64_decode($p_url));
    }
    $coupon['start_date']=date('Y-m-d',$coupon['start_date']);
    $coupon['end_date']=date('Y-m-d',$coupon['end_date']);
    
    $tmp="handout_type_$coupon[handout_type]_checked";
	$$tmp='checked';
	if($coupon['handout_type']==1) $change_point=$coupon['sale_price'];
	else if($coupon['handout_type']==2) $rtn_price=$coupon['sale_price'];
    
	$coupon['coupon_img']=ProcImgPath($coupon['coupon_img']);
	
    require_once template('coupon_add');
    exit;
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $coupon = $db->get_one("SELECT name FROM `{$tablepre}coupon_cat` WHERE uid='$uid' LIMIT 1");
    if($coupon)
    {
        admin_log("删除优惠券：$coupon[name]");
        $db->query("DELETE FROM `{$tablepre}coupon_cat` WHERE uid='$uid'");
        $db->query("DELETE FROM `{$tablepre}coupon` WHERE cc_uid='$uid'");
        $coupon['coupon_img']=str_replace('@!web_coupon', '',$coupon['coupon_img']);
        file_unlink($coupon['coupon_img'],'bucket');
        $db->free_result();
    }
    
    exit('OK:删除成功');
}
else if($action=='check')
{
    $uid=(int)$uid;
    $coupon = $db->get_one("SELECT name FROM `{$tablepre}coupon_cat` WHERE uid='$uid' LIMIT 1");
    if($coupon)
    {
        $is_check=(int)$is_check;
        $status=$m_now_time;
        if($is_check!=1) $status=1000;
        $info=dhtmlchars($info);
        admin_log("审核优惠券：$coupon[name]");
        $db->query("UPDATE `{$tablepre}coupon_cat` SET
                    od='$status',
                    other_info='$info' 
                    WHERE uid='$uid'");
        $db->free_result();
    }
    
    exit('OK:操作成功');
    
}
else if($action=='bat_check')
{
    if($_POST && (int)$step==1)
    {
        if(!is_array($uids)) show_msg('请正确提交数据');
        foreach ($uids as $key=>$val)
        {
            $val=(int)$val;
            if($val>0) $uids[$key]=$val;
            else unset($uids[$key]);
        }
        if(!$uids) show_msg('请选择需要审核的优惠券');
        
        $str_uids=implode(',',$uids);
        $db->query("UPDATE `{$tablepre}coupon_cat` SET od='$m_now_time',other_info='' WHERE uid IN ($str_uids)");
        $db->free_result();
        admin_log("批量审核优惠券");
    }
    
    show_msg('审核成功',base64_decode($_POST['p_url']));
}
else show_msg('pass_worng');

