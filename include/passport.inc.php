<?php

/**
 * MVM_MALL 网上商店系统  通行接口
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-03 $
 * $Id: passport.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL')) exit('Access Denied');

$list = $db->get_one("SELECT uid,member_id,member_class,member_pass FROM `{$tablepre}member_table` WHERE member_id = '$login_id' LIMIT 1");
if (!$list || $list['member_pass']!=$login_pass) show_msg('ERR:登录失败，请确认用户名或密码是否正确');


if($mm_close==0 && $list['uid']!=1) move_page('./');
$_SESSION['user']['mvm_sess_uid'] = $list['uid'];
$_SESSION['user']['mvm_sess_id'] = $list['member_id'];
$db->free_result();

$grade=$db->get_one("SELECT is_admin,rank_list FROM `{$tablepre}grade_table` WHERE group_id='$list[member_class]' LIMIT 1");
$_SESSION['user']['mvm_is_admin']=$grade['is_admin'];
if($grade['is_admin']==1) $_SESSION['user']['mvm_rank_list']=$grade['rank_list'];

//统计数据
$statistics=$db->get_one("SELECT login_time,last_login_time,total_login FROM `{$tablepre}member_statistics` WHERE m_uid='$list[uid]' LIMIT 1");
if($statistics)
{
    $statistics['last_login_time']=$statistics['login_time'];
    $statistics['login_time']=$m_now_time;
    $statistics['total_login']++;
    $db->update("`{$tablepre}member_statistics`",$statistics," m_uid='$list[uid]' ");
}
else
{
    $db->query("REPLACE INTO `{$tablepre}member_statistics` (m_id,last_login_time,login_time,total_login,m_uid) 
                VALUES ('$list[member_id]','$m_now_time','$m_now_time','1','$list[uid]')");
}
$db->free_result();

//更新购物车
$mvm_member=$list;
if($cart) $cart->update_discount();

if(defined('REGISTER')) move_page('register_end.php');
else
{
    if($subrel)
    {
        echo "<script type='text/javascript' src='http://$subrel/index.php?p3p_set_sid=set&sid=$sessionID' reload='1'></script>";
        show_msg('login_succeed',"http://$subrel/");
    }
    else
    {
        if(substr($mm_refer_url,strlen($mm_refer_url)-1,1)=='?') $mm_refer_url=substr($mm_refer_url,0,strlen($mm_refer_url)-1);
        if(!$mm_refer_url) $mm_refer_url='./';
        show_msg('OK：登录成功',$mm_refer_url);
    }
}