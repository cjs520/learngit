<?php

/**
 * MVM_MALL 网上商店系统  会员预付款管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: member_money.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/


if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $search_sql="WHERE register_date>0";
    $use_index=" FORCE INDEX (`register_date`)";
    
    if((int)$step==1 || $ps_member)
    {
        $ps_member = dhtmlchars($ps_member);
        $ps_member_txt=$ps_member;
        $ps_member=urlencode($ps_member);
        $st=(int)$st;
        
        if($ps_member)
        {
            $search_sql .= " AND money_id = '$ps_member_txt' ";
            $m=$db->get_one("SELECT member_money,member_money_freeze FROM `{$tablepre}member_table` WHERE member_id='$ps_member_txt' LIMIT 1");
            if(!$m) show_msg('查询不到指定会员');
            $money_left_total=currency($m['member_money']+$m['member_money_freeze']);
            $money_left=currency($m['member_money']);

            $today_start=strtotime(date('Y-m-d'));
            $today_end=$today_start+24*3600;
            $rtl=$db->get_one("SELECT SUM(ABS(money_add)) AS sm FROM `{$tablepre}money_table`
                               WHERE money_id='$ps_member' AND register_date>='$today_start' AND register_date<'$today_end'");
            $money_today=currency($rtl['sm']);
            $use_index=" FORCE INDEX (`money_id`)";
        }
        
        $mt=dhtmlchars($mt);
        if($mt) $search_sql.=" AND type='$mt'";
        
        if($st==1) $search_sql.=" AND money_add>0";
        else if($st==2) $search_sql.=" AND money_add<0";
        
        $begin_time=strtotime($b_time);
        $end_time=strtotime($e_time);
        if($begin_time) $search_sql.=" AND register_date>='$begin_time'";
        if($end_time) $search_sql.=" AND register_date<='$end_time'";
    }
    
    $rtl=$db->get_one("SELECT SUM(money_add) AS sm FROM `{$tablepre}money_table` $search_sql AND money_add>0");
    $income_money=currency($rtl['sm']);
    $rtl=$db->get_one("SELECT SUM(money_add) AS sm FROM `{$tablepre}money_table` $search_sql AND money_add<0");
    $cost_money=currency($rtl['sm']);
    
    $arr_st=array('全部','收入','支出');
    $sel_st=drop_menu($arr_st,'st',(int)$st);
    
    $arr_mt=array(0=>'全部','注册赠送'=>'注册赠送','管理员设置'=>'管理员设置','商铺开张'=>'商铺开张','商铺升级'=>'商铺升级','商铺续费'=>'商铺续费','预付款充值'=>'预付款充值',
                  '购买模板'=>'购买模板','短信充值'=>'短信充值','取消订单'=>'取消订单','购物'=>'购物','分账'=>'分账','退货'=>'退货','积分购买'=>'积分购买','保证金'=>'保证金',
                  '消保申请'=>'消保申请');
    $sel_mt=drop_menu($arr_mt,'mt',$mt);
    
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}money_table",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 15;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,register_date,money_sess,money_id,money_add,money_left,approval_date,type,money_reason,other_info 
                     FROM {$tablepre}money_table $use_index 
                     $search_sql 
                     ORDER BY register_date DESC 
                     LIMIT $from_record,$list_num");
    $member_money = array();
    while($rtl = $db->fetch_array($q))
    {
        $rtl['reg_time'] = date('Y-m-d',$rtl['register_date']);
        if(!$rtl['money_sess']) $rtl['money_sess']=$rtl['register_date'];
        $rtl['money_add']>0?$rtl['add']=currency($rtl['money_add']):$rtl['minus']=currency($rtl['money_add']);
        if($rtl['approval_date']==0) $rtl['status']='待审核';
        else if($rtl['approval_date']>0) $rtl['status']='<span class="orange">成功</span>';
        else if($rtl['approval_date']==-1) $rtl['status']='已回退';
        else $rtl['status']='未知';
        $rtl['money_left']=currency($rtl['money_left']);
        if(!$rtl['other_info']) $rtl['other_info']='无附加信息';
        $member_money[] = $rtl;
    }
    $page_list = $rowset->link("admincp.php?module=member_money&action=list&step=$step&ps_member=$ps_member&st=$st&mt=$mt&b_time=$b_time&e_time=$e_time&page=");
    require_once template('member_money');
    footer();
}
else if($action=='del')
{
	$uid=(int)$uid;
	$rtl=$db->get_one("SELECT money_sess FROM `{$tablepre}money_table` WHERE uid='$uid' LIMIT 1");
	if($rtl)
	{
	    admin_log("删除资金明细：$rtl[money_sess]");
	    $db->query("DELETE FROM `{$tablepre}money_table` WHERE uid='$uid'");
	    $db->free_result();
	}
	
	exit;
}
else if($action=='rollback')
{
	$uid=(int)$uid;
	$rtl=$db->get_one("SELECT uid,money_sess,approval_date,money_id,money_add FROM `{$tablepre}money_table` WHERE uid='$uid' LIMIT 1");
	if(!$rtl) exit('检索不到指定记录');
	if($rtl['type']!='预付款' && substr($rtl['money_sess'],0,2)!='PM') exit('不是预付款相关记录，无法回退');
	if($rtl['approval_date']<=0) exit('未审核 或 已回退记录，无法回退');
	admin_log("回退资金明细：$rtl[money_sess]");
	
	$db->query("UPDATE `{$tablepre}member_table` SET member_money=member_money-'$rtl[money_add]' WHERE member_id='$rtl[money_id]'");
	$db->query("UPDATE `{$tablepre}money_table` SET approval_date='-1' WHERE uid='$uid'");
	exit('ok');
}
else if($action=='check')
{
	$uid=(int)$uid;
	$rtl=$db->get_one("SELECT type,money_sess,approval_date,money_add,money_id FROM `{$tablepre}money_table` WHERE uid='$uid' LIMIT 1");
	if(!$rtl) exit('检索不到指定记录');
	if($rtl['approval_date']!=0) exit('已回退 或 已审核记录，无法审核');
	admin_log("审核资金明细：$rtl[money_sess]");
	
	if($rtl['type']=='预付款' || substr($rtl['money_sess'],0,2)=='PM') 
	    $db->query("UPDATE `{$tablepre}member_table` SET member_money=member_money+'$rtl[money_add]' WHERE member_id='$rtl[money_id]'");
	$db->query("UPDATE `{$tablepre}money_table` SET approval_date='$m_now_time' WHERE uid='$uid'");
	exit('ok');
}