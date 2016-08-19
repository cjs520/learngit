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
 * $Id: wosmj.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');
require_once MVMMALL_ROOT . 'include/oss-sdk/Common.php';
use OSS\OssClient;
use OSS\Core\OssUtil;
use OSS\Core\OssException;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) alert('buctket对象为空');
if($action=='list')
{
	if(!$page_member_id) show_msg('您的账号异常');

	require_once MVMMALL_ROOT.'include/pager.class.php';
	$prefix = 'union/shopimg/user_img/'.$page_member_id.'/image/';
	$delimiter = '/';
	$nextMarker = '';
	$maxkeys = 500;
	$options = array(
            'delimiter' => $delimiter,
            'prefix' => $prefix,
            'max-keys' => $maxkeys,
            'marker' => $nextMarker,
	);
	try {
		$listObjectInfo = $ossClient->listObjects($bucket, $options);
	} catch (OssException $e) {
		printf(__FUNCTION__ . ": FAILED\n");
		printf($e->getMessage() . "\n");
		return;
	}
	// 得到nextMarker，从上一次listObjects读到的最后一个文件的下一个文件开始继续获取文件列表
	$nextMarker = $listObjectInfo->getNextMarker();
	$listObject = $listObjectInfo->getObjectList();
	$listPrefix = $listObjectInfo->getPrefixList();
 
	$total_count = count($listObject)-1;
    $page = $page ? (int)$page:1;
    $list_num = $total_count<10?$total_count:10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    if($from_record==0) $from_record=1; 
	$imgs=array();
	for($i=$from_record;$i<$from_record+$list_num;$i++){
		$rs=array_values((array)$listObject[$i]);
		$imgs[]=IMG_URL.$rs[0];
	}

    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&page=");
    include template('sadmin_pics');
}
else if($action=='del')
{
	if(!$page_member_id) exit('账号异常');
	$path=dhtmlchars($path);
	$path=str_replace(IMG_URL,'', $path);
	file_unlink($path,'bucket');
	exit('ok');
}
else if($action=='bat_del')
{
    if(!$page_member_id) exit('账号异常');
    if($_POST && (int)$step==1)
    {
        do
        {
            if(!is_array($path)) break;
            foreach ($path as $val)
            {
                $val=dhtmlchars($val);
	            $path=str_replace(IMG_URL,'', $val);
	            file_unlink($path,'bucket');
            }
        }while (0);
    }
    show_msg('删除成功',"sadmin.php?module=$module&action=list");
}