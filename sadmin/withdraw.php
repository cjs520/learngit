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
 * $Id: withdraw.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
$arr_status=array(0=>'未审核',1=>'通过审核',2=>'驳回申请');
$member_account=$db->get_one("SELECT * FROM `{$tablepre}member_account` WHERE member_uid='$page_member_id' LIMIT 1");

if($action=='list')
{
    $member_account['type']=(int)$member_account['type'];
    $mvm_member['member_money']=currency($mvm_member['member_money']);
    $mvm_member['member_money_freeze']=currency($mvm_member['member_money_freeze']);
    $mm_withdraw_lbound=currency($mm_withdraw_lbound);
    
	require_once 'include/pager.class.php';
	$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}money_apply` WHERE supplier_id='$page_member_id'");
	$total_count=$rtl['cnt'];
    $page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT sn,money,real_money,type,account,reg_time,status 
                     FROM `{$tablepre}money_apply` 
                     WHERE `supplier_id`='$page_member_id' 
                     ORDER BY reg_time DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['status']=$arr_status[$rtl['status']];
        $rtl['money']=currency($rtl['money']);
        $rtl['real_money']=currency($rtl['real_money']);
        $rtl['reg_time']=date('Y-m-d H:i',$rtl['reg_time']);
        $rtl['type']=$rtl['type']==1?'银行卡':'支付宝';
    	$money_apply[] = $rtl;
    }
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&page=");
    include template('sadmin_withdraw');
}
else if($action=='add')
{
    if($mvm_member['isSupplier']<=1) show_msg('您不是正式商铺，无法申请提现');
    
    if($_POST && (int)$step==1)
    {
        $money=floatval($money);
        $type=(int)$type==1?1:0;
        $member_name=dhtmlchars($member_name);
        $account=dhtmlchars($account);
        $bank=dhtmlchars($bank);
        $mm_withdraw_lbound=floatval($mm_withdraw_lbound);
        
        if(!$member_name) show_msg('请填写开户名');
        if(!$account) show_msg('请填写提现账号');
        if($type==1 && !$bank) show_msg('请填写开户行');
        if($money<=0) show_msg('请填写一个大于0的合法数字');
        if($money<$mm_withdraw_lbound) show_msg("提现金额不能低于{$mm_withdraw_lbound}元");
        if($money>$mvm_member['member_money']) show_msg('您的资金账户余额不足，无法提现');
        
        $sn='MA'.strval(date('YmdHis')).rand(10,99);
        
        $real_money=$money-$money*floatval($mm_withdraw_rate)/100;
        $money_apply_row=array(
            'money'=>$money,
            'real_money'=>$real_money,
            'sn'=>$sn,
            'supplier_id'=>$m_check_uid,
            'reg_time'=>$m_now_time,
            'type'=>$type,
            'member_name'=>$member_name,
            'account'=>$account,
            'bank'=>$type==1?$bank:'',
            'status'=>0
        );
        $db->insert("`{$tablepre}money_apply`",$money_apply_row);
        
        $db->query("UPDATE `{$tablepre}member_table` SET 
                    member_money=member_money-'$money',
                    member_money_freeze=member_money_freeze+'$money'  
                    WHERE uid='$m_check_uid'");
        $db->free_result();
    }
    
    show_msg('提现成功，请等待管理员审核',"sadmin.php?module=$module&action=list");
}