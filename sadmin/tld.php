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
 * $Id: tld.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if($action=='list')
{
    $rtl_tld=$db->get_one("SELECT * FROM `{$tablepre}tld` WHERE supplier_id='$page_member_id' LIMIT 1");
    
	if($_POST && (int)$step==1)
	{
	    $domain_name=dhtmlchars(strtolower(trim($domain_name)));
	    if(!$domain_name) show_msg('请填写顶级域名');
	    $domain_name=str_replace('http:','',$domain_name);
	    $domain_name=str_replace(array('/','\\'),'',$domain_name);
	    
	    $db->query("REPLACE INTO `{$tablepre}tld` (supplier_id,domain_name,is_check) VALUES ('$page_member_id','$domain_name','0')");
	    $db->free_result();
	    $db_mem_cache->op(0,'Id2Domain',$page_member_id,'',db_memory_cache::DELETE );
	    show_msg('提交成功，请等待审核',"sadmin.php?module=$module&action=list");
	}
	else if($_POST && (int)$step==2)
	{
	    $member_homepage=dhtmlchars($member_homepage);
	    if(!preg_match('/^[0-9a-zA-Z]{5,15}$/',$member_homepage)) show_msg('请填写正确的二级域名，由5~15个英文和数字组成');
	    
	    $domain_file='data/malldata/sudomain.dat';
        if(file_exists($domain_file))
        {
        	$preserve_domain=file_get_contents($domain_file); 
        	$rtl=array_search($member_homepage,explode('|',$preserve_domain));
        	if($rtl) show_msg('您的二级域名被占用，请更换一个');
        }
        
        $rtl=$db->get_one("SELECT m_uid FROM `{$tablepre}member_shop` WHERE member_homepage='$member_homepage' AND m_uid<>'$page_member_id' LIMIT 1");
        if($rtl) show_msg('您的二级域名已被占用，请更换一个');
        $db->query("UPDATE `{$tablepre}member_shop` SET member_homepage='$member_homepage' WHERE m_uid='$page_member_id'");
        $db->free_result();
        $db_mem_cache->op(0,'Id2Domain',$page_member_id,'',db_memory_cache::DELETE );
        
        show_msg('设置成功！',"sadmin.php?module=$module&action=list");
	}
	
    extract($rtl_tld);
    $status=$is_check==1?'已审核':'未审核';
    $allow_modify=strpos($shop_file['member_homepage'],'mvm')===0;
    
	include template('sadmin_tld');
}