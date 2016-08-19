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
 * $Id: coupon.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/pic.class.php';
if($action=='list')
{
    $arr_coupon=array();
    $q = $db->query("SELECT uid,name,handout_type,discount,start_date,end_date,other_info,od FROM `{$tablepre}coupon_cat` 
                     WHERE `supplier_id`='$page_member_id' 
                     ORDER BY `od` DESC");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['start_date']=date('Y-m-d',$rtl['start_date']);
        $rtl['end_date']=date('Y-m-d',$rtl['end_date']);
        if($rtl['other_info']) $rtl['other_info']="<font color='red'>其它信息：$rtl[other_info]</font>";
        
        if($rtl['od']<1000) $rtl['status']='普通券';
        else if($rtl['od']==1000) $rtl['status']='普通券，申请退回';
        else if($rtl['od']==10000) $rtl['status']='主站推广，审核中';
        else if($rtl['od']>10000) $rtl['status']='主站推广，通过审核';
        
        $arr_coupon[] = $rtl;
    }
	include template('sadmin_coupon');
}
else if($action=='add')
{
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}coupon_cat` WHERE supplier_id='$page_member_id'");
    if($rtl['cnt']>=(int)$mm_shop_coupon) show_msg('您发布的优惠券数量已超过上限，请先删除部分没用的优惠券');
    
	if($_POST && (int)$step==1)
	{
        $name=dhtmlchars($name);
        $discount=floatval($discount);
        if(!$name) show_msg('请填写优惠券名称');
	    if($discount<=0) show_msg('请正确填写优惠券金额');
	    
        $handout_type=(int)$handout_type;
        if(!in_array($handout_type,array(0,1,2))) $handout_type=0;
        
	    $sale_price=0;
	    if($handout_type==1) $sale_price=intval($change_point);
	    else if($handout_type==2) $sale_price=intval($rtn_price);
	    
	    $od=(int)$od;
	    if($od>1000) $od-=1000;
	    if((int)$main==1) $od=10000;
	    
	    require_once 'include/supplier_upfile.class.php';
        $f = new upfile('gif,jpg,png,jpeg','',512*1024,0,$page_member_id);
	    if($_FILES['coupon_img']['name']!='')
        {
            $coupon_img = $f->upload('coupon_img');
            $coupon_img=$coupon_img.'@!web_coupon';
        }
	    
	    $row=array(
            'name'=>$name,
            'supplier_id'=>$page_member_id,
            'handout_type'=>$handout_type,
            'discount'=>$discount,
            'start_date'=>strtotime($start_date),
            'end_date'=>strtotime($end_date)+24*3600-1,
            'sale_price'=>$sale_price,
            'price_lbound'=>floatval($price_lbound),
            'coupon_img'=>$coupon_img,
            'od'=>$od
        );
        $db->insert("`{$tablepre}coupon_cat`",$row);
        $db->free_result();
        show_msg('优惠券发布成功',"sadmin.php?module=$module&action=list");
    }
    
    $coupon['coupon_img']=ProcImgPath($coupon['coupon_img']);
    $handout_type_0_checked='checked';
    include template('sadmin_coupon_add');
    footer();
}
else if($action=='edit')
{
	$coupon = $db->get_one("SELECT * FROM `{$tablepre}coupon_cat` WHERE uid='$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
	if(!$coupon) show_msg('检索不到您指定的优惠券');
	
	$tmp="handout_type_$coupon[handout_type]_checked";
	$$tmp='checked';
	if($coupon['handout_type']==1) $change_point=$coupon['sale_price'];
	else if($coupon['handout_type']==2) $rtn_price=$coupon['sale_price'];
	
	$coupon['start_date']=date('Y-m-d',$coupon['start_date']);
	$coupon['end_date']=date('Y-m-d',$coupon['end_date']);
	$coupon['coupon_img']=ProcImgPath($coupon['coupon_img']);
    include template('sadmin_coupon_add');
    footer();
}
else if($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,coupon_img FROM `{$tablepre}coupon_cat` WHERE uid='$uid' AND supplier_id='$page_member_id'");
    if(!$rtl) exit('ERROR:检索不到指定的优惠券');
   $db->query("DELETE FROM `{$tablepre}coupon` WHERE cc_uid='$uid'");
    $db->query("DELETE FROM `{$tablepre}coupon_cat` WHERE uid='$uid'");
    $rtl['coupon_img']=str_replace('@!web_coupon', '',$rtl['coupon_img']);
    file_unlink($rtl['coupon_img'],'bucket');
    exit('OK:删除成功');
}