<?php

/**
 * MVM_MALL 网上商店系统 商品相册
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: gallery.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/common.inc.php';
$uid = (int)$uid;
$product = $db->get_one("SELECT uid,goods_name,goods_sale_price,goods_market_price,goods_file1,goods_file2 FROM `{$tablepre}goods_table` WHERE uid = '$uid'");
$product == false && move_page('./');
$gallery = array();
$gallery[0]['imgid'] = 0;
$gallery[0]['thumb'] = $product['goods_file1'];
$gallery[0]['imgbig'] = $product['goods_file2'];
$gallery[0]['img_desc'] = $product['goods_name'];
$result  = $db->query("SELECT * FROM `{$tablepre}gallery` WHERE goods_id = '$uid' ORDER BY `imgid` ASC");
while ($list = $db->fetch_array($result)) $gallery[] = $list;

$product['title'] = $product['goods_name'];
$product['goods_market_price'] = currency($product['goods_market_price']);
$product['goods_sale_price'] = currency($product['goods_sale_price']);
require_once MVMMALL_ROOT . 'header.php';
require_once template('gallery');
