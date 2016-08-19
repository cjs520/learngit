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
 * $Id: index_floor.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if($action=='list')
{
    $mod_img=$db->get_one("SELECT content FROM `{$tablepre}def_mod` WHERE m_uid='$page_member_id' AND type='0' LIMIT 1");
    
    //获取推荐商品
    $arr_goods=array();
    $table=$shop_file['sellshow']==1?"{$tablepre}goods_table":"{$tablepre}goods_show";
    $g_mod=$db->get_one("SELECT content FROM `{$tablepre}def_mod` WHERE m_uid='$page_member_id' AND type='1' LIMIT 1");
    do
    {
        if(!$g_mod) break;
        $q=$db->query("SELECT uid,goods_name FROM `{$table}` WHERE uid IN ($g_mod[content]) AND supplier_id='$page_member_id'");
        while ($rtl=$db->fetch_array($q))
        {
            $arr_goods[]=$rtl;
        }
        $db->free_result();
    }while (0);
    
	include template('sadmin_index_floor');
}
else if($action=='edit')
{
    if($_POST && (int)$step==1)
    {
        $mod=close_tags($mod);
        $db->query("REPLACE INTO `{$tablepre}def_mod` (m_uid,type,content) 
                    VALUES ('$page_member_id','0','$mod')");
        
        $goods_uid=dhtmlchars($goods_uid);
        $arr_g_uid=explode(',',$goods_uid);
        foreach ($arr_g_uid as $key=>$val)
        {
            $val=(int)$val;
            if($val<=0) unset($arr_g_uid[$key]);
            else $arr_g_uid[$key]=$val;
        }
        $arr_g_uid=array_unique($arr_g_uid);
        array_push($arr_g_uid,0);
        $goods_uid=implode(',',$arr_g_uid);
        $db->query("REPLACE INTO `{$tablepre}def_mod` (m_uid,type,content) 
                    VALUES ('$page_member_id','1','$goods_uid')");
    }
    
    show_msg('编辑成功',"sadmin.php?module=$module&action=list");
}
else if($action=='search_goods')
{
    $table=$shop_file['sellshow']==1?"{$tablepre}goods_table":"{$tablepre}goods_show";
    $search_txt=dhtmlchars($search_txt);
    $arr_rtl=array();
    $q=$db->query("SELECT uid,goods_name FROM `$table` WHERE supplier_id='$page_member_id' AND goods_name LIKE '%$search_txt%' LIMIT 10");
    while ($rtl=$db->fetch_array($q))
    {
        $arr_rtl[]=$rtl;
    }
    
    exit(json_encode($arr_rtl));
}
else if($action=='get_default_custom_code')
{
    $custom_code_file='union/';
    $custom_code_file.=$shop_file['sellshow']==1?'templates/':'show_templates/';
    $custom_code_file.="$ucfg[mm_skin_name]/custom_code.php";;
    if(!file_exists($custom_code_file)) exit('没有默认代码, 请自行编辑');
    
    $custom_code=file_get_contents($custom_code_file);
    echo str_replace('shopimg','union/shopimg',$custom_code);
    
    exit;
}


function close_tags($html)
{
    $arr_single_tags = array('meta', 'img', 'br', 'link', 'area');
    preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    if (count($closedtags) == $len_opened)
    {
        return $html;
    }
    
    $openedtags = array_reverse($openedtags);
    for ($i = 0; $i < $len_opened; $i++)
    {
        if (!in_array($openedtags[$i], $arr_single_tags))
        {
            if (!in_array($openedtags[$i], $closedtags))
            {
                    $html .= '</' . $openedtags[$i] . '>';
            }
            else
            {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
    }
    return $html;
}
