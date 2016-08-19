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
 * $Id: set_manager.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if($m_check_uid!=$page_member_id && $m_check_uid!=1) show_msg('您不是超级管理员和商铺大当家，无权进入');

if($action=='list')
{
    $arr_manager=array();
    $q=$db->query("SELECT uid,m_id,level,rank_list FROM `{$tablepre}member_shop_manager` WHERE shop_m_uid='$page_member_id' ORDER BY level");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['rank_list']=$rtl['rank_list']?mb_substr($rtl['rank_list'],0,70,'UTF-8').'...':'无';
        $arr_manager[$rtl['level']]=$rtl;
    }
    
    for($i=1;$i<5;$i++)
    {
        if($arr_manager[$i]) continue;
        $arr_manager[$i]=array('uid'=>0,'m_id'=>'无','level'=>0,'rank_list'=>'无');
    }
    $db->free_result();
    
	include template('sadmin_set_manager');
}
else if($action=='edit')
{
    include 'include/rank_list.inc.php';
    $level=(int)$level;
    if($level<=0 || $level>5) sadmin_show_msg('等级指定错误',$prev_url);
	$manager = $db->get_one("SELECT * FROM `{$tablepre}member_shop_manager` WHERE `shop_m_uid`='$page_member_id' AND level='$level' LIMIT 1");
    if($_POST && (int)$step==1)
    {
        $m_id=dhtmlchars($m_id);
        $m=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$m_id' LIMIT 1");
        if(!$m) sadmin_show_msg('检索不到指定会员',$p_url); 
        if($m['uid']==$page_member_id) sadmin_show_msg('您不能为店铺大当家进行授权',$p_url);
        if($m['uid']==1) sadmin_show_msg('您无法对超级管理员进行授权',$p_url);
        $rank_list=implode(',',$rank);
        $row=array(
            'm_uid'=>$m['uid'],
            'm_id'=>$m_id,
            'shop_m_uid'=>$page_member_id,
            'rank_list'=>$rank_list,
            'level'=>$level
        );
        $db->replace("`{$tablepre}member_shop_manager`",$row);
        
        move_page(base64_decode($p_url));
    }
    
    include template('sadmin_set_manager_add');
    exit;
}
else if($action=='del')
{
    $uid=(int)$uid;
    if($uid<=0) exit('ERR:指定管理员错误');
    $db->query("DELETE FROM `{$tablepre}member_shop_manager` WHERE uid='$uid' AND shop_m_uid='$page_member_id'");
    $db->free_result();
    exit('OK:删除成功');
}
else if($action=='check_member')
{
    $m_id=dhtmlchars($m_id);
    $m=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$m_id' LIMIT 1");
    if(!$m) exit('ERR:检索不到指定会员');
    if($m['uid']==$page_member_id) exit('ERR:您不能为店铺大当家进行授权');
    if($m['uid']==1) exit('ERR:您无法对超级管理员进行授权');
    exit('OK:经检测，指定的会员资料无误');
}