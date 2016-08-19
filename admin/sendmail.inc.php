<?php
/**
 * MVM_MALL 网上商店系统  后台邮件发送
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: sendmail.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='add')
{
    $sel_grade=drop_menu($m_class_array,'grade');
	require_once template('sendmail');
	footer();
}
else if($action=='send')
{
    if($_POST && (int)$step==1)
	{
	    $arr_recv=split(';',trim($receiver));
	    foreach ($arr_recv as $key=>$val)
	    {
	        if(!preg_match('/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/',$val)) unset($arr_recv[$key]);
	    }
	    if(sizeof($arr_recv)<=0) exit('ERR:请指定合法的接收邮箱');
	    if(sizeof($arr_recv)>100) exit('ERR:您指定的接收邮箱大于100，无法发送');
	    $str_email=implode('|',$arr_recv);
	    $mail_subject=trim($mail_subject);
	    $mail_body=trim($mail_body);
	    $rtl=smtp_mail($str_email,$mail_subject,$mail_body,true);
	    
	    exit($rtl?'OK:发送成功':'ERR:发送失败');
	}
}
else if($action=='search_member')
{
    $m_id=dhtmlchars($m_id);
    $grade=(int)$grade;
    $is_sup=(int)$is_sup;
    $page=(int)$page;
    
    $search_sql=" WHERE member_email<>''";
    if($m_id) $search_sql.=" AND member_id LIKE '%$m_id%' ";
    if($grade>0) $search_sql.=" AND member_class='$grade' ";
    if($is_sup==1) $search_sql.=" AND isSupplier='0' ";
    if($is_sup==2) $search_sql.=" AND isSupplier>'0'";
    
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}member_table",$search_sql);
	$arr_grade = $m_class_array;//会员等级
	$page = $page<=0?1:(int)$page;
	$list_num = 30;
	
	$arr_member=array();
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$q = $db->query("SELECT member_id,member_email 
	                 FROM `{$tablepre}member_table` 
	                 $search_sql 
	                 ORDER BY register_date DESC 
	                 LIMIT $from_record, $list_num");
	while ($rtl=$db->fetch_array($q))
	{
	    $arr_member[]=$rtl;
	}
	$db->free_result();
	
    echo json_encode(array(
        'page'=>$page,
        'total_page'=>$rowset->pages,
        'arr_member'=>$arr_member
    ));
    exit;
}

