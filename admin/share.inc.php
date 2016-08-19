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
 * $Id: share.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $arr_share=array();
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}order_share",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,goods_name,g_uid,module,pics,comment,supplier_id,m_uid,register_date 
                     FROM `{$tablepre}order_share`
                     WHERE uid>0 
                     ORDER BY uid DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $m=cur_member_info($rtl['m_uid']);
        $rtl['member_id']=$m['member_id'];
        
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
        $rtl['url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
        $rtl['pics']=unserialize($rtl['pics']);
        $arr_share[] = $rtl;
    }
    $db->free_result();
    
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&t=$t&page=");
    require_once template('share');
    footer();
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,goods_name,pics FROM `{$tablepre}order_share` WHERE uid='$uid'");
    if($rtl)
    {
        admin_log("删除分享：$rtl[goods_name]");
        $rtl['pics']=unserialize($rtl['pics']);
        foreach ($rtl['pics'] as $val) file_unlink($val);
        $db->query("DELETE FROM `{$tablepre}order_share` WHERE uid='$rtl[uid]'");
    }
    
    exit;
}
else show_msg('pass_worng');
