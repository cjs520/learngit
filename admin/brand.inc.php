<?php

/**
 * MVM_MALL 网上商店系统 商品品牌管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: brand.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/pic.class.php';

if ($action=='list')
{
    require_once 'include/pager.class.php';
    
    $isCheck=(int)$isCheck;
    $brandname=dhtmlchars($brandname);
    
    if($isCheck==-1) $sql_filter='WHERE isCheck=0';
    else if($isCheck==1) $sql_filter='WHERE isCheck=1';
    else $sql_filter="WHERE TRUE";
    
    if($brandname) $sql_filter.=" AND brandname LIKE '%$brandname%'";
    
    $total_count = $db->counter("{$tablepre}brand_table",str_replace('WHERE','',$sql_filter));
    $page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q  = $db->query("SELECT id,brandname,isCheck,weburl,train,brief,logo 
                      FROM `{$tablepre}brand_table` 
                      $sql_filter 
                      ORDER BY train 
                      LIMIT $from_record, $list_num");
    while ($rt = $db->fetch_array($q))
    {
        $rt['brief'] = mb_substr($rt['brief'],0,20,'UTF-8');
		$rt['isCheck']=$rt['isCheck']==1?'是':'<span class="orange">否</span>';
		$rt['logo']=IMG_URL.$rt['logo'];
    	$brand_rt[] = $rt;
    }
    $db->free_result();
    
    $brandname=urlencode($brandname);
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&isCheck=$isCheck&brandname=$brandname&page=");
    require_once template('brand');
    footer();
}
else if ($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $brandname = dhtmlchars($brandname);
        $keywords = dhtmlchars($keywords);
		$brief = strip_tags($brief);
        $weburl = dhtmlchars($weburl);
        $order = (int)$order;
        if ($_FILES['logo_file']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','images/brand/');
            $logo = $rowset->upload('logo_file');
            $logo = $logo.'@!web_brand';
        }
        $goods_category_value=0;
        foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $goods_category_value=$val;
        }
        $sql = "INSERT INTO `{$tablepre}brand_table` SET
                brandname = '$brandname',
                logo = '$logo',
                keywords = '$keywords',
                brief = '$brief',
                weburl = '$weburl',
                train = '$train',
                category_id = '$goods_category_value',
                isCheck = '$_POST[isCheck]'";
        $db->query($sql);
        $db->free_result();
        admin_log("添加品牌：$brandname");
        
        $cache->put_cache('brand');
        show_msg('success','admincp.php?module=brand&action=list');
    }
	    
    require_once template('brand_add');
    footer();
    
}
else if ($action=='edit')
{
    $uid=(int)$uid;
    $brand_rt = $db->get_one("SELECT * FROM `{$tablepre}brand_table` WHERE id='$uid' LIMIT 1");
    if(!$brand_rt) show_msg('检索不到您指定的品牌');
    
    if($_POST && (int)$step==1)
    {
        $brandname = dhtmlchars($brandname);
        $keywords = dhtmlchars($keywords);
		$brief = strip_tags($brief);
        $weburl = dhtmlchars($weburl);
        $order = (int)$order;
        $logo = $brand_rt['logo'];
        if ($_FILES['logo_file']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','images/brand/');
            $logo = $rowset->upload('logo_file');
            $logo = $logo.'@!web_brand';
            file_unlink(str_replace('@!web_brand','', $brand_rt['logo']),'bucket');
        }
        
        $goods_category_value=0;
        foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $goods_category_value=$val;
        }
        $sql = "UPDATE {$tablepre}brand_table SET
                brandname = '$brandname',
                logo = '$logo',
                keywords = '$keywords',
                brief = '$brief',
                weburl = '$weburl',
                train = '$train',
                category_id = $goods_category_value,
                isCheck = '$_POST[isCheck]' 
                WHERE id ='$uid'";
        $db->query($sql);
        $db->free_result();
        admin_log("编辑品牌：$brandname");
        
        $cache->put_cache('brand');
        show_msg('success','admincp.php?module=brand&action=list');
    }
    @extract($brand_rt,EXTR_OVERWRITE);
  
    $brief=dhtmlchars($brief);
    if($isCheck==1) $is_check='checked';
    else $check_brand_list=drop_menu($cache->get_cache('brand'),'check_brand_list');
    
    
    require_once template('brand_add');
    footer();
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $brand_rt = $db->get_one("SELECT id,logo,brandname FROM `{$tablepre}brand_table` WHERE id ='$uid'");
    if($brand_rt)
    {
        if($brand_rt['logo']!='') file_unlink(str_replace('@!web_brand','', $brand_rt['logo']),'bucket');
        $db->query("DELETE FROM `{$tablepre}brand_table` WHERE id = '$brand_rt[id]'");
        admin_log("删除品牌：$brand_rt[brandname]");
        
        $cache->delete('brand',0);
        $db->free_result();
    }
    
    exit('OK:删除成功');
} 
else if($action=='ajax')
{
	$id=(int)$id;
	$id==0 && exit;
	$db->query("UPDATE `{$tablepre}brand_table` SET $field='$v' WHERE id='$id'");
	$db->free_result();
}
else if($action=='merge')
{
	$from_id=(int)$uid;
	$target_id=(int)$check_brand_list;
	$from_brand=$db->get_one("SELECT id,logo,brandname FROM `{$tablepre}brand_table` WHERE id='$from_id' AND isCheck='0'");
	$target_brand=$db->get_one("SELECT id,brandname FROM `{$tablepre}brand_table` WHERE id='$target_id' AND isCheck='1'");
	if(!$from_brand || !$target_brand) show_msg('两个合并的品牌数据不完整');
	admin_log("合并品牌：$from_brand[brandname]和$target_brand[brandname]");
	
	$db->query("UPDATE `{$tablepre}goods_table` SET goods_brand='$target_brand[id]' WHERE goods_brand='$from_brand[id]'");
	file_unlink(str_replace('@!web_brand','', $from_brand['logo']),'bucket');
	$db->query("DELETE FROM `{$tablepre}brand_table` WHERE id='$from_brand[id]'");
	$db->free_result();
	show_msg('合并成功',"admincp.php?module=brand&action=list&isCheck=$last&page=$page");
}
else if($action=='change_brand_check')
{
	$uid==(int)$uid;
	$rtn_val='否';
	if($uid<=0) exit($rtn_val);
	
	$rtl=$db->get_one("SELECT isCheck,brandname FROM `{$tablepre}brand_table` WHERE id='$uid' LIMIT 1");
	if(!$rtl) exit($rtn_val);

	$rtl['isCheck']=$rtl['isCheck']==1?0:1;
	$rtn_val=$rtl['isCheck']==1?'是':'否';
	$db->query("UPDATE `{$tablepre}brand_table` SET isCheck='$rtl[isCheck]' WHERE id='$uid'");
	$db->free_result();
	admin_log($rtl['isCheck']==1?"审核品牌：$rtl[brandname]":"拒绝品牌：$rtl[brandname]");
	
	echo $rtn_val;
}
else show_msg('pass_worng');