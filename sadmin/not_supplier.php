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
 * $Id: not_supplier.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
$arr_man_shop=array();
$q=$db->query("SELECT shop_m_uid,level FROM `{$tablepre}member_shop_manager` WHERE m_uid='$m_check_uid'");
while($rtl=$db->fetch_array($q))
{
    $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[shop_m_uid]' LIMIT 1");
    if(!$shop) continue;
    
    $rtl['shop_name']=$shop['shop_name'];
    $rtl['level']=$arr_level_name[$rtl['level']];
    $arr_man_shop[]=$rtl;
}
$db->free_result();

include template('sadmin_not_supplier');