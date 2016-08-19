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
 * $Id: community_topic.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $t=(int)$t;
    if(!in_array($t,array(0,1,2))) $t=0;
    
    if($t==0)
    {
        $search_sql=" WHERE approval_date>10";
        $order_sql=" ORDER BY approval_date DESC";
    }
    else if($t==1)
    {
        $search_sql=" WHERE approval_date=0";
        $order_sql=" ORDER BY register_date DESC";
    }
    else if($t==2)
    {
        $search_sql=" WHERE approval_date=-1";
        $order_sql=" ORDER BY register_date DESC";
    }
    
    $arr_topic=array();
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}community_topic",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,t_name,m_uid,c_uid,tags,register_date FROM `{$tablepre}community_topic` 
                     $search_sql 
                     $order_sql 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $m=$db->get_one("SELECT member_id FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
        if($m) $rtl=array_merge($rtl,$m);
        
        $comm=$db->get_one("SELECT c_name FROM `{$tablepre}community` WHERE uid='$rtl[c_uid]' LIMIT 1");
        $comm['detail_url']=GetBaseUrl('life_detail',$rtl['c_uid']);
        if($comm) $rtl=array_merge($rtl,$comm);
        
        $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
        $rtl['topic_url']=GetBaseUrl('life_post',$rtl['uid']);
        $arr_topic[] = $rtl;
    }
    $db->free_result();
    
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&t=$t&page=");
    require_once template('community_topic');
    footer();
}
else if($action=='edit')
{
    $uid=(int)$uid;
    $topic=$db->get_one("SELECT uid,t_name,content,tags FROM `{$tablepre}community_topic` WHERE uid='$uid' LIMIT 1");
    if(!$topic) show_msg('检索不到指定的生活圈主题');
    
    if($_POST && (int)$step==1)
    {
        $detail=preg_replace('/<script.*?script>/i','',$detail);
        $tags=dhtmlchars(trim($tags));
        
        if(!$detail) show_msg('请填写主题内容');
        if(!$title) show_msg('请填写标题内容');

        if($cover && strstr($cover,'union'))
        {
            $start_pos=strpos($cover,'union');
            $cover=substr($cover,$start_pos);
        }

        $arr_tmp=explode(' ',$tags);
        $arr_tmp2=array();
        foreach ($arr_tmp as $key=>$val)
        {
            if($key>=5) break;
            $arr_tmp2[]=$val;
        }
        $tags=implode(' ',$arr_tmp2);

        $row=array(
            't_name'=>$title,
            'content'=>$detail,
            'cover'=>$cover,
            'tags'=>$tags
        );
        $db->update("`{$tablepre}community_topic`",$row," uid='$topic[uid]' ");
        $db->free_result();

        show_msg('主题修改成功',base64_decode($p_url));
    }
    
    require_once template('community_topic_add');
    footer();
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,t_name FROM `{$tablepre}community_topic` WHERE uid='$uid'");
    admin_log("删除生活圈话题：$rtl[t_name]");
    $db->query("DELETE FROM `{$tablepre}community_topic` WHERE uid='$rtl[uid]'");
    exit;
}
else show_msg('pass_worng');


function get_status($approval_date)
{
    $approval_date=(int)$approval_date;
    if($approval_date==-1) return '已驳回';
    else if($approval_date==0) return '未审核';
    else if($approval_date>10) return '已审核';
}