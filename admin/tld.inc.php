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
 * $Id: tld.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    require_once 'include/pager.class.php';
    $arr_tld=array();
    $total_count = $db->counter("{$tablepre}tld");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT * FROM `{$tablepre}tld` LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['url']=GetBaseUrl('index','','',$rtl['supplier_id']);
        $rtl['status']=$rtl['is_check']==1?'审定':'<span class="orange">未审定</span>';
        
        $shop=$db->get_one("SELECT m_id,shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        if($rtl) $rtl=array_merge($rtl,$shop);
        
        $arr_tld[]=$rtl;
    }
    $db->free_result();
    
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
    require_once template('tld');
    footer();;
}
else if($action=='del')
{
    $supid=(int)$supid;
    $rtl=$db->get_one("SELECT domain_name FROM `{$tablepre}tld` WHERE supplier_id='$supid' LIMIT 1");
    if($rtl)
    {
        admin_log("删除顶级域名申请：$rtl[domain_name]");
        $db->query("DELETE FROM `{$tablepre}tld` WHERE supplier_id='$supid'");
        $db->free_result();
        $db_mem_cache->op(0,'Id2Domain',$supid,'',db_memory_cache::DELETE );
    }
    
}
else if($action=='check')
{
    $info='';
    $supid=(int)$supid;
    do
    {
        if($supid<=0) break;
        $rtl=$db->get_one("SELECT is_check,domain_name FROM `{$tablepre}tld` WHERE supplier_id='$supid' LIMIT 1");
        if(!$rtl) break;
        $rtl['is_check']=$rtl['is_check']==1?0:1;
        $info=$rtl['is_check']==1?'审定':'<span class="orange">未审定</span>';
        
        $db->query("UPDATE `{$tablepre}tld` SET is_check='$rtl[is_check]' WHERE supplier_id='$supid'");
        $db->free_result();
        $db_mem_cache->op(0,'Id2Domain',$supid,'',db_memory_cache::DELETE );
        admin_log($rtl['is_check']==1?"审核顶级域名申请：$rtl[domain_name]":"拒绝顶级域名申请：$rtl[domain_name]");
    }while(0);
    
    exit($info);
}
else if($action=='edit')
{
    $supid=(int)$supid;
    $domain_name=dhtmlchars($domain_name);
    $domain=$db->get_one("SELECT domain_name FROM `{$tablepre}tld` WHERE supplier_id='$supid' LIMIT 1");
    if($domain && $supid>0 && $domain_name)
    {
        $rtl=$db->get_one("SELECT supplier_id FROM `{$tablepre}tld` WHERE domain_name='$domain_name' AND supplier_id<>'$supid'");
        if(!$rtl)
        {
            admin_log("编辑顶级域名：$domain[domain_name]");
            $db->query("UPDATE `{$tablepre}tld` SET domain_name='$domain_name' WHERE supplier_id='$supid'");
            $db->free_result();
            $db_mem_cache->op(0,'Id2Domain',$supid,'',db_memory_cache::DELETE );
        }
        
    }
    exit;
}
else show_msg('pass_worng');