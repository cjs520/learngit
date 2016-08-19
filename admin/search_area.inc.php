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
 * $Id: search_area.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

$hot_area_file='data/malldata/hot_area.php';
if(file_exists($hot_area_file)) include $hot_area_file;
else $arr_hot_area=array();


if($action=='list')
{
    $arr_rtl=array();
    for($c=ord('A');$c<=ord('Z');$c++) $arr_rtl[chr($c)]=array();
    
    $q=$db->query("SELECT uid,cls,province,city,county FROM `{$tablepre}search_area` FORCE INDEX (`cls`) ORDER BY cls,od");
    while ($rtl=$db->fetch_array($q))
    {
        if(!isset($arr_rtl[$rtl['cls']])) continue;
        $rtl['area']=$rtl['province'];
        if($rtl['city']) $rtl['area']=$rtl['city'];
        if($rtl['county']) $rtl['area']=$rtl['county'];
        $rtl['url']='shop.php?province_s='.urlencode($rtl['province']).'&city_s='.urlencode($rtl['city']).'&county_s='.urlencode($rtl['county']);
        
        $arr_rtl[$rtl['cls']][]=$rtl;
    }
    
    $db->free_result();
    
    require_once template('search_area');
    footer();
}
else if ($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $province=dhtmlchars($province);
        $city=dhtmlchars($city);
        $county=dhtmlchars($county);
        
        if(!$province) sadmin_show_msg('请选择地区',$p_url);
        
        if((int)$_POST['type']==0)    //热门地区
        {
            $area_tmp=$province;
            if($city) $area_tmp=$city;
            if($county) $area_tmp=$county;
            array_push($arr_hot_area,array($area_tmp,'shop.php?province_s='.urlencode($province).'&city_s='.urlencode($city).'&county_s='.urlencode($county)));
            file_put_contents($hot_area_file,'<?php $arr_hot_area='.var_export($arr_hot_area,true).';');
            admin_log("添加热门地区：$province $city $county");
        }
        else    //普通地区
        {
            $row=array(
                'province'=>$province,
                'city'=>$city,
                'county'=>$county,
                'cls'=>chr((int)$cls+ord('A')),
                'od'=>(int)$od
            );
            
            $db->insert("`{$tablepre}search_area`",$row);
            $db->free_result();
            admin_log("添加普通地区：$province $city $county");
        }
        
        move_page(base64_decode($p_url));
    }
    
    $arr=range('A','Z');
    $sel_cls=drop_menu($arr,'cls');
    
    require_once template('search_area_add');
    exit;
}
else if ($action=='del_hot')
{
    $uid=(int)$uid;
    admin_log("删除热门地区：{$arr_hot_area[$uid][0]}");
    
    unset($arr_hot_area[$uid]);
    file_put_contents($hot_area_file,'<?php $arr_hot_area='.var_export($arr_hot_area,true).';');
    
    exit('OK:删除成功');
}
else if($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT province,city,county FROM `{$tablepre}search_area` WHERE uid='$uid' LIMIT 1");
    if($rtl)
    {
        $db->query("DELETE FROM `{$tablepre}search_area` WHERE uid='$uid'");
        $db->free_result();
        admin_log("删除普通地址：$rtl[province] $rtl[city] $rtl[county]");
    }
    
    exit('OK:删除成功');
}
else show_msg('pass_worng');

