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
 * $Id: infor_buy_detail.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';
require 'header.php';

$action=(int)$action;
$buy=$db->get_one("SELECT uid,goods_name,goods_category,pic,num,price,province,city,county,intro,tel,qq,ww,approval_date,register_date,detail 
                      FROM `{$tablepre}want_buy` 
                      WHERE uid='$action' AND approval_date>=10 
                      LIMIT 1");
if(!$buy) show_msg('检索不到指定的求购应信息');

$cat=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$buy[goods_category]' LIMIT 1");

if(!$buy['pic'] || !file_exists($buy['pic'])) $buy['pic']='images/noimages/noproduct.jpg';
$buy['price']=$buy['price']<=0?'面议':currency($buy['price']);
$buy['status']=$buy['approval_date']>10?'进行中':'已完成';
$buy['register_date']=date('Y-m-d',$buy['register_date']);
$buy['detail']=filter_editor_img($buy['detail']);


$arr_tmp=array(0=>'-- 全部 --');
foreach ($cat_parent as $val) $arr_tmp[$val['uid']]=$val['category_name'];
$sel_cat=drop_menu($arr_tmp,'cat_uid');

include template('infor_buy_detail');
footer();
