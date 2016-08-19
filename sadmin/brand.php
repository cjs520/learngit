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
 * $Id: brand.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/pic.class.php';
if($action=='list')
{
	require_once MVMMALL_ROOT.'include/pager.class.php';
	$brand_rt=array();
    $total_count = $db->counter("{$tablepre}brand_table","isCheck='1' OR supplier_id='$page_member_id'");
    $page = $page ? (int)$page:1;
	$list_num = 20;
	$rowset = new Pager($total_count,$list_num,$page);

	$from_record = $rowset->_offset();
    $q = $db->query("SELECT id,brief,isCheck,logo,weburl,brandname FROM `{$tablepre}brand_table` 
                     WHERE isCheck='1' OR supplier_id='$page_member_id' 
                     ORDER by `id` DESC LIMIT $from_record, $list_num");
    while ($rt = $db->fetch_array($q))
    {
        $rt['edit_label'] = $rt['isCheck']==0 ? '修改' : '查看';
		$rt['logo']=IMG_URL.$rt['logo'];
    	$brand_rt[] = $rt;
    }
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&page=");
	include template('sadmin_brand');
}
else if($action=='add')
{
	if($_POST && (int)$step==1)
	{
        $brandname = dhtmlchars($brandname);
        $keywords = dhtmlchars($keywords);
		$brief = $brief;
        $weburl = dhtmlchars($weburl);
        $order = (int)$order;
        if ($_FILES['logo_file']['name']!='')
        {
            require_once MVMMALL_ROOT.'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','images/brand/');
            $logo = $rowset->upload('logo_file');
           $logo = $logo.'@!web_brand';//pic::PicZoom($logo,135,60,array(255,255,255),true);
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
                category_id = '$goods_category_value',
                train = '$train',
                supplier_id = '$page_member_id'";
        $db->query($sql);
        $db->free_result();
        $cache->delete('brand',0);
        move_page(base64_decode($p_url));
    }
	
    include template('sadmin_brand_add');
	exit;
}
else if($action=='edit')
{
	$brand_rt = $db->get_one("SELECT * FROM `{$tablepre}brand_table` WHERE id='$uid' LIMIT 1");
	if(!$brand_rt) exit('检索不到指定品牌');
    if($_POST && (int)$step==1)
    {
    	if($brand_rt['isCheck']!=0) sadmin_show_msg('该品牌已被审核，无法修改',$p_url);
        $brandname = dhtmlchars($brandname);
        $keywords = dhtmlchars($keywords);
		$brief = $brief;
        $weburl = dhtmlchars($weburl);
        $order = (int)$order;
        $logo = $brand_rt['logo'];
        if ($_FILES['logo_file']['name']!='')
        {
            require_once MVMMALL_ROOT.'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','images/brand/');
            $logo = $rowset->upload('logo_file');
            $brand_rt['logo'] && file_unlink(str_replace('@!web_brand','', $brand_rt['logo']),'bucket');
            $logo = $logo.'@!web_brand';
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
                category_id = '$goods_category_value',
                train = '$train'
                WHERE id ='$uid'";
        $db->query($sql);
        $db->free_result();
        $cache->delete('brand',0);
        move_page(base64_decode($p_url));
    }
    
    @extract($brand_rt,EXTR_OVERWRITE);

    include template('sadmin_brand_add');
	exit;
}
