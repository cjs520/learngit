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
 * $Id: change_stock.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';

//goods_detail goods_group_detail goods_change_detail goods_onsale_detail
$arr_goods_table=array("{$tablepre}goods_detail","{$tablepre}goods_group_detail","{$tablepre}goods_change_detail","{$tablepre}goods_onsale_detail");
foreach ($arr_goods_table as $table)
{
    $q=$db->query("SELECT g_uid,attr_store FROM `$table`");
    while ($rtl=$db->fetch_array($q))
    {
        if(!$rtl['attr_store']) continue;
        $arr_tmp=explode('||',$rtl['attr_store']);
        foreach ($arr_tmp as $key=>$val)
        {
            $arr=explode('|',$val);
            $stock=$arr[sizeof($arr)-1];
            $arr[sizeof($arr)-1]=0;
            $arr[]=$stock;
            $arr_tmp[$key]=implode('|',$arr);
        }
        $str_tmp=implode('||',$arr_tmp);

        $db->query("UPDATE `$table` SET attr_store='$str_tmp' WHERE g_uid='$rtl[g_uid]'");
        $db->free_result(1);
        //echo $str_tmp;exit;
    }
    $db->free_result();
}


echo 'ok';
exit;