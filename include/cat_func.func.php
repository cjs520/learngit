<?php
/**
 * MVM_MALL 网上商店系统  系统函数库
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-04-12 $
 * $Id: cat_func.func.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

function cat_bucket_level($uid,$cat_data)
{
    $arr_rtl=array();
    $uid=(int)$uid;
    if(!isset($cat_data[0]['child'][$uid])) return array();
    $arr_rtl=bfs($cat_data[0]['child'][$uid]);
    
    return $arr_rtl;
}

function get_children($uid,$uid_2_pid,$cat_data)
{
    $children=array();
    $parents=get_parents($uid,$uid_2_pid);
    $node=null;
    foreach ($parents as $val)
    {
        $val=(int)$val;
        if(!$node) $node=$cat_data[$val];
        else $node=$node['child'][$val];
    }
    
    $children=bfs($node);
    return $children;
}

function get_chidldren_uids($uid,$uid_2_pid,$cat_data)
{
    $children_uids=array();
    $children=get_children($uid,$uid_2_pid,$cat_data);
    foreach ($children as $val) $children_uids[]=(int)$val[0];
    return $children_uids;
}

function bfs($cat)
{
    $arr_rtl=array();
    //if(isset($cat['data']['uid'])) array_push($arr_rtl,array($cat['data']['uid'],$cat['data']['category_name']));
    
    if(!$cat['child']) return array();
    foreach ($cat['child'] as $key=>$val)
    {
        if(isset($val['data']['uid'])) array_push($arr_rtl,array($val['data']['uid'],$val['data']['category_name']));
        $rtl=bfs($val);
        if(!$rtl) continue;
        $arr_rtl=array_merge($arr_rtl,$rtl);
    }
    
    return $arr_rtl;
}

function get_parents($cat_id,$uid_2_pid)
{
    $parents=array();
    $tmp_id=$cat_id;
    while(true)
    {
        $parents[]=$tmp_id;
        if(!isset($uid_2_pid[$tmp_id])) break;
        $tmp_id=$uid_2_pid[$tmp_id];
    }
    $parents=array_reverse($parents);
    return $parents;
}