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
 * $Id: goods_storage.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if($action=='list')
{
	$arr_brand=$cache->get_cache('brand');
	
	$search_sql=" WHERE approval_date>0";
    if($ps_subject)
    {
    	$ps_subject_txt=$ps_subject;
    	$search_sql.=" AND goods_name LIKE '%$ps_subject%'";
    	$ps_subject=urlencode($ps_subject);
    }
    
    require_once 'include/pager.class.php';
    $arr_goods=array();
    $cat_ids=array();
    $total_count = $db->counter("{$tablepre}goods_storage",$search_sql);
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,goods_uid FROM `{$tablepre}goods_storage` 
                     $search_sql 
                     ORDER BY approval_date DESC
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $g=$db->get_one("SELECT goods_name,goods_sale_price,goods_brand,goods_category,goods_file1,supplier_id FROM `{$tablepre}goods_table` WHERE uid='$rtl[goods_uid]' LIMIT 1");
        if(!$g) continue;
        $rtl=array_merge($rtl,$g);
        
        $rtl['goods_file1']=ProcImgPath($rtl['goods_file1']);
        $rtl['goods_url'] = GetBaseUrl('product',$rtl['goods_uid'],'action',$rtl['supplier_id']);
        $rtl['goods_brand']=$arr_brand[$rtl['goods_brand']];
        $cat_ids[]=$rtl['goods_category'];
        
        $arr_goods[] = $rtl;
    }
    $db->free_result();
    
    if($cat_ids)
    {
        $str_cat_ids=implode(',',$cat_ids);
        $q=$db->query("SELECT uid,category_name FROM `{$tablepre}category` WHERE uid IN ($str_cat_ids)");
        while($rtl=$db->fetch_array($q)) $cats[$rtl['uid']]=$rtl['category_name'];
    }
    $db->free_result();
    
    foreach ($arr_goods as $key=>$val)
    {
        $arr_goods[$key]['goods_category']=$cats[$val['goods_category']];
    }
    
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&ps_subject=$ps_subject&page=");
	include template('sadmin_goods_storage');
}
else if($action=='add')
{
	if($mvm_member['member_point']<(int)$mm_storage_point*sizeof($arr_uid)) exit('您的积分不够');
	$arr_uid=array($uid);
	foreach ($arr_uid as $val)
	{
		$val=(int)$val;
		if($val<=0) continue;
		StorageToGoods($val);
	}
	exit('入库完成');
}

function StorageToGoods($uid)
{
	global $db,$tablepre,$page_member_id,$mm_storage_point,$mm_storage_send_point;
	global $m_user_ip,$m_now_time,$m_check_id,$m_check_uid,$mvm_member;
	
	$mm_storage_point=(int)$mm_storage_point;
	$mm_storage_send_point=(int)$mm_storage_send_point;
	
	$storage=$db->get_one("SELECT goods_uid FROM `{$tablepre}goods_storage` WHERE uid='$uid' AND approval_date>0 LIMIT 1");
	$db->free_result();
	if(!$storage) return ;
	$goods_uid=$storage['goods_uid'];
	$g=$db->get_one("SELECT * FROM `{$tablepre}goods_table` WHERE uid='$storage[goods_uid]' LIMIT 1");
	$db->free_result();
	if(!$g) return ;
	if($g['supplier_id']==$page_member_id) return ;    //禁止入库自己的商品
	$supplier_id=$g['supplier_id'];
	$g=array_merge($g,$storage);
	
	require_once MVMMALL_ROOT. 'include/oss-sdk/Common.php';
	$bucket = Common::getBucketName();
    $ossClient = Common::getOssClient();

	//搬运商品
	//if(!is_dir("union/shopimg/user_img/$page_member_id")) mkdir("union/shopimg/user_img/$page_member_id",0777,true);
	if($g['goods_file1'])
	{
	    
		$basename=basename($g['goods_file1']);
		//copy(ProcImgPath($g['goods_file1']),"union/shopimg/user_img/$page_member_id/$basename");
		$from_object="union/".$g['goods_file1'];
		$to_object= "union/shopimg/user_img/$page_member_id/$basename";
	    copyObject($ossClient,$bucket,$from_object,$to_object);
		$g['goods_file1']="shopimg/user_img/$page_member_id/$basename";
	}
	unset($g['uid']);
	unset($g['goods_uid']);
	unset($g['approval']);
	unset($g['supplier_cat']);
	unset($g['supplier_cat2']);
	unset($g['supplier_cat3']);
	$g['supplier_id']=$page_member_id;
	$g['register_date']=$m_now_time;
	$insert_id=$db->insert("`{$tablepre}goods_table`",$g);
	
	//搬运商品详细信息
	$goods_detail=$db->get_one("SELECT * FROM `{$tablepre}goods_detail` WHERE g_uid='$goods_uid' LIMIT 1");
	$db->free_result();
	do
	{
	    if(!$goods_detail) break;
	    if($goods_detail['goods_file2'])
	    {
	        $basename=basename($goods_detail['goods_file2']);
	        //copy(ProcImgPath($goods_detail['goods_file2']),"union/shopimg/user_img/$page_member_id/$basename");
	        $from_object=$from_objec="union/".$g['goods_file2'];
	        $to_object= "union/shopimg/user_img/$page_member_id/$basename";
	       copyObject($ossClient,$bucket,$from_object,$to_object);
	        $goods_detail['goods_file2']="shopimg/user_img/$page_member_id/$basename";
	    }
	    $goods_detail['g_uid']=$insert_id;
	    $db->replace("`{$tablepre}goods_detail`",$goods_detail);
	    $db->free_result();
	}while (0);
	
	//搬运相册
	if(!is_dir("union/shopimg/gallery/$page_member_id")) mkdir("union/shopimg/gallery/$page_member_id",0777,true);
	$q=$db->query("SELECT imgbig,thumb FROM `{$tablepre}gallery` WHERE goods_id='$goods_uid' LIMIT 5");
	while($rtl=$db->fetch_array($q))
	{
	    if($rtl['thumb'])
	    {
	        $basename=basename($rtl['thumb']);
	       // copy(ProcImgPath($rtl['thumb']),"union/shopimg/gallery/$page_member_id/$basename");
	        $from_object="union/".$rtl['thumb'];
	        $to_object= "union/shopimg/gallery/$page_member_id/$basename";
	        copyObject($ossClient,$bucket,$from_object,$to_object);
	        $rtl['thumb']="shopimg/gallery/$page_member_id/$basename";
	    }
	    
	    if($rtl['imgbig'])
	    {
	        $basename=basename($rtl['imgbig']);
	        //copy(ProcImgPath($rtl['imgbig']),"union/shopimg/gallery/$page_member_id/$basename");
	        $from_object="union/".$rtl['imgbig'];
	        $to_object= "union/shopimg/gallery/$page_member_id/$basename";
	       copyObject($ossClient,$bucket,$from_object,$to_object);
	        $rtl['imgbig']="shopimg/gallery/$page_member_id/$basename";
	    }
	    
	    $rtl['goods_id']=$insert_id;
	    $rtl['supplier_id']=$page_member_id;
	    $db->insert("`{$tablepre}gallery`",$rtl);
	}
	$db->free_result();
	
	//给提供者加分
	add_score($supplier_id,$mm_storage_send_point,'商品库加分',"$good[goods_name]被使用");
	
	//给使用者扣分
	add_score($m_check_uid,$mm_storage_point*-1,'商品库扣分',"使用商品$good[goods_name]");
}
function copyObject($ossClient, $bucket,$from_object,$to_object)
{
   
    if(!$ossClient->doesObjectExist($bucket,$from_object)) return ;
    
    $from_bucket = $bucket;
    //$from_object = "oss-php-sdk-test/upload-test-object-name.txt";
    $to_bucket = $bucket;
   // $to_object = $from_object . '.copy';
   $ossClient->copyObject($from_bucket,$from_object, $to_bucket, $to_object);
}