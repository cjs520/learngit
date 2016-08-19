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
 * $Id: community.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/pic.class.php';

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
    
    $arr_community=array();
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}community",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,c_logo,c_name,c_cat,c_tag,approval_date,register_date,back_reason,od FROM `{$tablepre}community` 
                     $search_sql 
                     $order_sql 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
    	$rtl['c_logo'] = IMG_URL.$rtl['c_logo'];
        if(!$rtl['c_logo'] || !@fopen($rtl['c_logo'],'r')) $rtl['c_logo']='images/noimages/noproduct.jpg';
        $rtl['status']=get_status($rtl['approval_date']);
        
        $cat=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$rtl[c_cat]' LIMIT 1");
        $rtl['c_cat']=$cat['category_name'];
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE c_uid='$rtl[uid]'");
        $rtl['member_num']=(int)$rtl_tmp['cnt'];
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE c_uid='$rtl[uid]'");
        $rtl['topic_num']=(int)$rtl_tmp['cnt'];
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE c_uid='$rtl[uid]'");
        $rtl['comment_num']=(int)$rtl_tmp['cnt'];
        
        $arr_community[] = $rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&t=$t&page=");
    require_once template('community');
    footer();
}
else if ($action=='edit')
{
	$uid = (int)$uid;
    $comm = $db->get_one("SELECT * FROM `{$tablepre}community` WHERE uid='$uid' LIMIT 1");
    if(!$comm) sadmin_show_msg('检索不到您指定的生活圈',$p_url);
    
    if($_POST && (int)$step==1)
    {
        $cat_uid=(int)$cat[1];
        $c_name=dhtmlchars(trim($c_name));
        $c_intro=dhtmlchars(strip_tags($c_intro));
        $c_hobby=dhtmlchars($c_hobby);
        $c_proclaim=dhtmlchars($c_proclaim);
        $c_tag=dhtmlchars(trim(strip_tags($c_tag)));
        $join_check=(int)$join_check==2?2:1;
        $od=(int)$od;
        
        $arr_tmp=explode(' ',$c_tag);
        $arr_tmp_target=array();
        $i=0;
        foreach ($arr_tmp as $val)
        {
            if($i>=3) break;
            $val=trim($val);
            if(!$val) continue;
            $arr_tmp_target[]=$val;
            $i++;
        }
        $c_tag=implode(' ',$arr_tmp_target);
        
        $c_logo=$comm['c_logo'];
        if ($_FILES['c_logo']['name']!='')
        {
        	file_unlink(str_replace('@!community', '', $c_logo),'buctket');
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','upload/community/');
            $c_logo = $rowset->upload('c_logo');
            $c_logo=$c_logo.'@!community';
        }
        $row=array(
            'c_name'=>$c_name,
            'c_tag'=>$c_tag,
            'c_cat'=>$cat_uid<=0?$comm['c_cat']:$cat_uid,
            'c_intro'=>$c_intro,
            'c_hobby'=>$c_hobby,
            'c_proclaim'=>$c_proclaim,
            'c_logo'=>$c_logo,
            'join_check'=>$join_check,
            'od'=>$od
        );
        $db->update("`{$tablepre}community`",$row," uid='$comm[uid]' ");
        $db->free_result();
        
        admin_log("修改生活圈：$comm[c_name]");
        move_page(base64_decode($p_url));
    }
    
    $cat_name='';
    do
    {
        $rtl=$db->get_one("SELECT category_id,category_name FROM `{$tablepre}category` WHERE uid='$comm[c_cat]' LIMIT 1");
        $cat_name=$rtl['category_name'];
        if($rtl['category_id']<=0) break;
        $rtl=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$rtl[category_id]' LIMIT 1");
        $cat_name=$rtl['category_name'].' -> '.$cat_name;
    }while (0);
    
    $cat_parent=$cache->get_cache('right_tree');
    if($comm['join_check']==2) $join_checked_2='checked';
    else $join_checked_1='checked';
    $pic=IMG_URL.$comm['c_logo'];
    if(!$pic || !@fopen($pic,'r')) $pic='images/noimages/noproduct.jpg';
    
    require_once template('community_add');
    exit;
}
else if($action=='check')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,c_name FROM `{$tablepre}community` WHERE uid='$uid' LIMIT 1");
    if($rtl)
    {
        $db->query("UPDATE `{$tablepre}community` SET approval_date='$m_now_time' WHERE uid='$uid' LIMIT 1");
        $db->free_result();
        admin_log("审核生活圈：$rtl[c_name]");
    }
    exit;
}
else if($action=='back')
{
    $uid=(int)$uid;
    $back_reason=dhtmlchars(strip_tags($back_reason));
    $rtl=$db->get_one("SELECT uid,c_name FROM `{$tablepre}community` WHERE uid='$uid' LIMIT 1");
    if($rtl)
    {
        admin_log("驳回生活圈：$rtl[c_name]");
        $db->query("UPDATE `{$tablepre}community` SET 
                    approval_date='-1',
                    back_reason='$back_reason' 
                    WHERE uid='$uid' LIMIT 1");
        $db->free_result();
    }
    exit;
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,c_logo,c_name FROM `{$tablepre}community` WHERE uid='$uid'");
     file_unlink(str_replace('@!community', '', $rtl['c_logo']),'bucket');
    admin_log("删除生活圈：$rtl[c_name]");
    $db->query("DELETE FROM `{$tablepre}community` WHERE uid='$rtl[uid]'");
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