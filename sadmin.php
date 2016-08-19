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
 * $Id: sadmin.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
$arr_level_name=array(1=>'二当家','2'=>'三当家','3'=>'四当家','4'=>'五当家');
if(!$m_check_id) show_msg('login_please',GetBaseUrl('logging','login'));

if($mvm_member['isSupplier']<1 && (int)$_SESSION['user']['man_shop']<=0)
{
    require_once 'header.php';
    require_once 'sadmin/not_supplier.php';
    footer();
}

$shop_manager_level='';
if((int)$_SESSION['user']['man_shop']>0)
{
    if($m_check_uid==1)
    {
        $shop_manager_level='超级管理';
        $page_member_id=(int)$_SESSION['user']['man_shop'];
    }
    else
    {
        $manager=$db->get_one("SELECT m_uid,level,rank_list 
                               FROM `{$tablepre}member_shop_manager` 
                               WHERE m_uid='$m_check_uid' AND shop_m_uid='{$_SESSION['user']['man_shop']}' 
                               LIMIT 1");
        if(!$manager) show_msg('您不是商铺授权管理','account.php?action=index');
        $shop_manager_level=$arr_level_name[$manager['level']];
        $page_member_id=$_SESSION['user']['man_shop'];
    }
}
else
{
    $shop_manager_level='大当家';
    $page_member_id=$m_check_uid;
}

$shop_file=$db->get_one("SELECT m_uid,m_id,shop_name,shop_expire,shop_level,shop_step,sellshow,member_homepage,isSupplier,xb_money 
                         FROM `{$tablepre}member_shop` WHERE m_uid='$page_member_id' 
                         LIMIT 1");
if(!$shop_file) show_msg('检索不到指定的商铺资料，请联系管理员');

$module=dhtmlchars($module);
$action=dhtmlchars($action);
$cscript='sadmin/'.$module.'.php';
if(!file_exists($cscript)) show_msg('此模块为主站功能');

require_once 'include/user_cache.class.php';
$ucache = new ucache($db,$tablepre);  

//加载分站管理员菜单
$admin_menu=$cache->get_cache("shop_level{$shop_file['shop_level']}_admin_menu");
$sell_not_allow_menu=array('consult','showgd');
$show_not_allow_menu=array('salegd','auction','order','payment','shipping','ctobuyer','user_rate','changegd','groupgd','goods',
                           'downgd','pack','goods_storage','sale_rank','consume_rank','grade','coupon');

//判断是否有调用的参数的权限
if($shop_file['sellshow']==2 && in_array($module,$show_not_allow_menu)) show_msg('您没有查看该模块的权限!');
if($shop_file['sellshow']==1 && in_array($module,$sell_not_allow_menu)) show_msg('您没有查看该模块的权限!!');
if(!in_array($module,array('index','sendsms','editor','shop_update','tpl')))
{
    $menu_filter_sql=" WHERE m_module='$module' AND m_action='$action'";
    if($_GET['type']) $menu_filter_sql.=" AND m_type='$type'";
    
    $menu_rtl=$db->get_one("SELECT uid,shop_level0,shop_level1,shop_level2,shop_level3,shop_level4 FROM `{$tablepre}admin_menu` $menu_filter_sql LIMIT 1");
    if($menu_rtl && !$menu_rtl['shop_level'.strval($shop_file['shop_level'])]) show_msg('您没有查看该模块的权限!!!');
    if(isset($manager) && !strstr($manager['rank_list'],$module)) $cscript='sadmin/noauth.php';
}
                           
//针对各自的类型排除不该显示的菜单
if($shop_file['sellshow']==1 && $sell_not_allow_menu)    //排除销售型商铺不能显示的菜单
{
    foreach ($admin_menu as $key=>$val)
    {
        foreach ($admin_menu[$key]['children'] as $key1=>$val1)
        {
            foreach ($admin_menu[$key]['children'][$key1]['children'] as $key2=>$val2)
            {
                $url=$admin_menu[$key]['children'][$key1]['children'][$key2]['menu_url'];
                $b_find=false;
                foreach ($sell_not_allow_menu as $k=>$v)
                {
                    if(strstr($url,$v))
                    {
                        $b_find=true;
                        break;
                    }
                }
                if($b_find) unset($admin_menu[$key]['children'][$key1]['children'][$key2]);
            }
        }
    }
}
else if($shop_file['sellshow']==2 && $show_not_allow_menu)    //排除展示型商铺不能显示的菜单
{
    foreach ($admin_menu as $key=>$val)
    {
        foreach ($admin_menu[$key]['children'] as $key1=>$val1)
        {
            foreach ($admin_menu[$key]['children'][$key1]['children'] as $key2=>$val2)
            {
                $url=$admin_menu[$key]['children'][$key1]['children'][$key2]['menu_url'];
                $b_find=false;
                foreach ($show_not_allow_menu as $k=>$v)
                {
                    if(strstr($url,$v))
                    {
                        $b_find=true;
                        break;
                    }
                }
                if($b_find) unset($admin_menu[$key]['children'][$key1]['children'][$key2]);
            }
        }
    }
}

$ucfg=$ucache->get_cache('cfg',$page_member_id);

//判断本店的开店步骤
$total_step=4;
$shop_step=$total_step+1;
if($shop_file['sellshow']==0)
{
    if(!($shop_file['shop_step'] & 8)) $shop_step=3;
    if(!($shop_file['shop_step'] & 4)) $shop_step=2;
    //if(!($mvm_member['shop_step'] & 2)) $shop_step=1;
    if(!($shop_file['shop_step'] & 1)) $shop_step=0;
}

$prev_url="sadmin.php?".$_SERVER['QUERY_STRING'];
foreach ($_POST as $key=>$val) $prev_url.="&$key=$val";
$prev_url=base64_encode($prev_url);

if($_SESSION['page_error'])
{
	$page_error=$_SESSION['page_error'];
	unset($_SESSION['page_error']);
}

require_once 'header.php';
require_once $cscript;
footer();

function tpl_array($dir_path='templates',$file='')
{
    $m_mall_skin = get_dirinfo($dir_path,$file);
    $size_skin = sizeof($m_mall_skin);
    $arr = array();
    for($i = 0 ; $i < $size_skin ; $i++)
    {
        $key = str_replace('.html', '', $m_mall_skin[$i]);
        $arr[$key] = $key;    	
    }
    return $arr;
}

function sadmin_show_msg($msg,$p_url)
{
	$_SESSION['page_error']=$msg;
	
	$p_url=base64_decode($p_url);
	move_page($p_url);
	exit;
}