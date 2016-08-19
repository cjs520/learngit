<?php

/**
 * MVM_MALL 网上商店系统 商品包装管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-17 $
 * $Id: withdraw.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

$arr_status=array(0=>'未审核',1=>'通过审核',2=>'驳回申请');
if ($action=='list')
{
    require_once 'include/pager.class.php';
    
    $total_count = $db->counter("{$tablepre}money_apply");
    $page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT * FROM `{$tablepre}money_apply` 
                     ORDER BY uid DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $shop=$db->get_one("SELECT shop_name,m_id FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        $rtl['shop_name']=$shop['shop_name'];
        $rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);
        $rtl['shop_m_id']=$shop['m_id'];
        
        $rtl['money']=currency($rtl['money']);
        $rtl['real_money']=currency($rtl['real_money']);
		$rtl['status_text']=$arr_status[$rtl['status']];
        $rtl['reg_time']=date('Y-m-d H:i',$rtl['reg_time']);
		
        if((int)$rtl['type']==0)
            $rtl['check_link']='<a href="#" uid="'.strval($rtl['uid']).'" rel="check" class="check" title="审核"></a>';
        else if((int)$rtl['type']==1)
            $rtl['check_link']='<a href="#" uid="'.strval($rtl['uid']).'" rel="manual_check" title="审核">手工转账</a>';
    	$money_apply[] = $rtl;
    }
    $page_list = $rowset->link("admincp.php?module=$module&action=list&page=");
    $db->free_result();
    
    require_once template('withdraw');
    footer();
}
else if($action=='edit')
{
	$status=intval($status)==1?1:2;
	$uid=(int)$uid;
	$money_apply=$db->get_one("SELECT * FROM `{$tablepre}money_apply` WHERE uid='$uid' LIMIT 1");
	if(!$money_apply) exit('检索不到指定的提现申请');
	if($money_apply['status']!=0) exit('本记录已经审核或撤销，不能进行设置');
	
	$member=$db->get_one("SELECT m_uid FROM `{$tablepre}member_shop` WHERE m_uid='$money_apply[supplier_id]' LIMIT 1");
	if(!$member) exit('检索不到提现商铺');
	foreach ($member as $key=>$val) $member[$key]=trim($val);
	
	$member['taobao']=$money_apply['account'];
	$member['member_name']=$money_apply['member_name'];
	$member['sn']=$money_apply['sn'];
	
	$link_info='';
	if($status==1)
	{
	    $pay = $db->get_one("SELECT cfg FROM `{$tablepre}payment_table` WHERE class_name='alipay' AND supplier_id='0' LIMIT 1");
	    if(!$pay) exit('站点还未安装淘宝支付接口，无法进行转账');
	    
	    $link_info=TranferAccount($money_apply,$member,unserialize($pay['cfg']));
	    admin_log("淘宝支付提现：$money_apply[sn]");

	}
	else
	{
	    $db->query("UPDATE `{$tablepre}member_table` SET 
	                member_money=member_money+'$money_apply[money]',
	                member_money_freeze=member_money_freeze-'$money_apply[money]' 
	                WHERE uid='$money_apply[supplier_id]'");
	    $db->query("UPDATE `{$tablepre}money_apply` SET status='2' WHERE uid='$money_apply[uid]'");
	    admin_log("撤销提现：$money_apply[sn]");
	}
	
	echo 'OK|||'.$link_info;
	exit;
}
else if($action=='check')
{
    $uid=(int)$uid;
	$money_apply=$db->get_one("SELECT * FROM `{$tablepre}money_apply` WHERE uid='$uid' LIMIT 1");
	if(!$money_apply) exit('检索不到指定的提现申请');
	if($money_apply['status']!=0) exit('本记录已经审核或撤销，不能进行设置');
	$member=$db->get_one("SELECT uid,member_id,member_name,member_money,member_money_freeze 
	                      FROM `{$tablepre}member_table` WHERE uid='$money_apply[supplier_id]' LIMIT 1");
	if(!$member) exit('检索不到指定的商家');
	
	$db->query("UPDATE `{$tablepre}money_apply` SET status='1' WHERE uid='$money_apply[uid]'");
	$sql = "INSERT INTO `{$tablepre}money_table` SET 
    		type='预付款',
            money_sess = '$money_apply[sn]',
            money_id = '$member[member_id]',
            money_add = '-$money_apply[money]',
            money_reason = '提现申请审核通过',
            money_left = '$member[member_money]',
            modify_ip = '$m_user_ip',
            register_date = '$m_now_time',
            approval_date = '$m_now_time'";
	
	$db->query($sql);
	
	$member_row['member_money_freeze']=$member['member_money_freeze']-floatval($money_apply['money']);
	if($member_row['member_money_freeze']<0) $member_row['member_money_freeze']=0;
	
	$db->update("`{$tablepre}member_table`",$member_row,"uid='$member[uid]'");
	$db->free_result();
	admin_log("手工转账提现：$money_apply[sn]");
	exit('OK');
}

function TranferAccount($money_apply,$member,$cfg)
{
    $link='';
    
    require_once 'include/payment/alipay.class.php';
    $rowst = new alipay($cfg);
    $link = $rowst->transfer_link($money_apply,$member);
    
    return $link;
}