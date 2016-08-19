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
 * $Id: ctobuyer.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
$roll=1;

if($action=='list')
{
	require_once 'include/pager.class.php';
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt 
    	               FROM `{$tablepre}comment_allow` 
    	               WHERE roll='$roll' AND from_id='$shop_file[m_id]'");
    $total_count=(int)$rtl['cnt'];
    $page = (int)$page>0 ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	    
    $q=$db->query("SELECT roll,uid,ordersn,supplier_id,shop_name,to_id 
    	           FROM `{$tablepre}comment_allow` ca 
    	           WHERE roll='$roll' AND from_id='$shop_file[m_id]'
    	           ORDER BY uid DESC 
    	           LIMIT $from_record,$list_num");
    $comment_list=array();
    while($rtl=$db->fetch_array($q))
    {
    	$rtl['url']="sadmin.php?module=order&action=list&ordersn=$rtl[ordersn]";
    		
    	$rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);    //商家url
    	$comment_list[]=$rtl;
    }
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&page=");
	include template('sadmin_ctobuyer');
}
else if($action=='write')
{
	if($_POST && (int)$step==1)
    {
    	$uid=(int)$allow_id;
    	$level=(int)$level;
    	$comment_allow=$db->get_one("SELECT uid,from_id,to_id,roll,ordersn FROM `{$tablepre}comment_allow` 
    	                             WHERE uid='$uid' AND from_id='$shop_file[m_id]' AND roll='$roll'
    	                             LIMIT 1");
    	if(!$comment_allow) show_msg('找不到相关评价');
    	if(!$mm_comment_level[$level]) $level=1;	
    	
    	//写入评价表
    	$comment_row=array(
    		'from_id'=>$comment_allow['from_id'],
    		'to_id'=>$comment_allow['to_id'],
    		'comment'=>strip_tags($comment),
    		'roll'=>$comment_allow['roll'],
    		'level'=>$level,
    		'reg_date'=>$m_now_time
    	);
    	$db->insert("`{$tablepre}order_user_comment`",$comment_row);
    	$db->query("DELETE FROM `{$tablepre}comment_allow` WHERE uid='$comment_allow[uid]'");
    	
    	//发送邮件
    	do
    	{
    	    if((int)$mm_mail_comment!=1) break;
    	    $member=$db->get_one("SELECT member_email FROM `{$tablepre}member_table` WHERE member_id='$comment_allow[to_id]' LIMIT 1");
    	    if(!$member) break;
    	    if(!$member['member_email']) break;
    	    
    	    smtp_mail($member['member_email'],
		        str_replace('{ordersn}',$comment_allow['ordersn'],"订单{ordersn}已成功评价"),
		        str_replace(array('{member_id}'),array($m_check_id),$mm_mail_comment_cnt)
		    );
    	}while (0);
    	
    	show_msg('评价成功',"sadmin.php?module=$module&action=list");
    }
}
