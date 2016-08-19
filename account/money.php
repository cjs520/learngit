<?php
$money_left_total=currency($mvm_member['member_money']+$mvm_member['member_money_freeze']);
$money_left=currency($mvm_member['member_money']);

$total_count = $db->counter("{$tablepre}money_table","money_id = '$m_check_id'");
require_once 'include/pager.class.php';
$page = $page ? (int)$page:1;
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q = $db->query("SELECT type,money_sess,money_id,money_reason,money_add,money_left,approval_date,other_info,register_date 
                 FROM `{$tablepre}money_table`
                 WHERE money_id = '$m_check_id' 
                 ORDER BY register_date DESC 
                 LIMIT $from_record, $list_num");
while($rtl = $db->fetch_array($q))
{
    $rtl['reg_time'] = date('Y-m-d',$rtl['register_date']);
    if(!$rtl['money_sess']) $rtl['money_sess']=$rtl['register_date'];
    if(!$rtl['other_info']) $rtl['other_info']='无附加信息';
    
    $rtl['money_add']>0?$rtl['add']=currency($rtl['money_add']):$rtl['minus']=currency($rtl['money_add']);
    if($rtl['approval_date']==0) $rtl['status']='等待审核';
    else if($rtl['approval_date']>0) $rtl['status']='交易成功';
    else if($rtl['approval_date']==-1) $rtl['status']='已回退';
    else $rtl['status']='未知';
    $rtl['money_left']=currency($rtl['money_left']);
    $money_log[] = $rtl;
}
//支付列表
$not_allow_list=array('integral','advance','COD');
$payment= $cache->get_cache('payment');
foreach ($payment as $key=>$val)
if (in_array($val['class_name'],$not_allow_list)) unset($payment[$key]);

$page_list  = $rowset->link('account.php?action=money&page=');

require 'header.php';
require_once template('member_money');