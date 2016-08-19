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
 * $Id: infor_supply_detsail.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';

$action=(int)$action;
$supply=$db->get_one("SELECT uid,goods_name,goods_category,pic,num,price,province,city,county,intro,tel,qq,ww,approval_date,register_date,detail,m_uid 
                      FROM `{$tablepre}want_supply` 
                      WHERE uid='$action' AND approval_date>=10 
                      LIMIT 1");
if(!$supply) show_msg('检索不到指定的供应信息');
$m=$db->get_one("SELECT member_id FROM `{$tablepre}member_table` WHERE uid='$supply[m_uid]' LIMIT 1");
$supply['member_id']=$m['member_id'];

$cat=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$supply[goods_category]' LIMIT 1");

if(!$supply['pic'] || !file_exists($supply['pic'])) $supply['pic']='images/noimages/noproduct.jpg';
$supply['price']=$supply['price']<=0?'面议':currency($supply['price']);
$supply['status']=$supply['approval_date']>10?'进行中':'已完成';
$supply['register_date']=date('Y-m-d',$supply['register_date']);
$supply['detail']=filter_editor_img($supply['detail']);


$arr_tmp=array(0=>'-- 全部 --');
foreach ($cat_parent as $val) $arr_tmp[$val['uid']]=$val['category_name'];
$sel_cat=drop_menu($arr_tmp,'cat_uid');

include template('infor_supply_detail');
footer();
