<?php

require_once MVMMALL_ROOT . 'include/oss-sdk/Common.php';
use OSS\OssClient;
use OSS\Core\OssUtil;
use OSS\Core\OssException;

$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if (is_null($ossClient)) alert('buctket对象为空');
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
 * $Id: editor.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if($action=='upload')
{
    $save_path = 'union/shopimg/user_img/'.$page_member_id.'/';
    //创建对象object 
    try {
    			$content = $ossClient->getObject($bucket, $save_path);
    		} catch (OssException $e) {
    			$file = __FILE__;
    		    $options = array();
    		    $ossClient->multiuploadFile($bucket, $save_path, $file, $options);
    	   }

 
    //文件保存URL
    $save_url = 'union/shopimg/user_img/'.$page_member_id.'/';
    //定义允许上传的文件扩展名
    $ext_arr = array(
    'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
    'flash' => array('swf', 'flv'),
    'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
    'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
    );
    //最大文件大小
    $max_size = 1000000;
    //PHP上传失败
    if (!empty($_FILES['imgFile']['error']))
    {
        switch($_FILES['imgFile']['error'])
        {
            case '1':
            $error = '超过php.ini允许的大小。';
            break;
            case '2':
            $error = '超过表单允许的大小。';
            break;
            case '3':
            $error = '图片只有部分被上传。';
            break;
            case '4':
            $error = '请选择图片。';
            break;
            case '6':
            $error = '找不到临时目录。';
            break;
            case '7':
            $error = '写文件到硬盘出错。';
            break;
            case '8':
            $error = 'File upload stopped by extension。';
            break;
            case '999':
            default:
            $error = '未知错误。';
        }
        alert($error);
    }

    //有上传文件时
    if (empty($_FILES) === false)
    {
        //原文件名
        $file_name = $_FILES['imgFile']['name'];
        //服务器上临时文件名
        $tmp_name = $_FILES['imgFile']['tmp_name'];
        //文件大小
        $file_size = $_FILES['imgFile']['size'];
        //检查文件名
        if (!$file_name) alert("请选择文件。");
        
        //检查文件大小
        if ($file_size > $max_size) alert("上传文件大小超过限制。");
        
        //检查目录名
        $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
        if (empty($ext_arr[$dir_name])) alert("目录名不正确。");
        
        //获得文件扩展名
        $temp_arr = explode(".", $file_name);
        $file_ext = array_pop($temp_arr);
        $file_ext = trim($file_ext);
        $file_ext = strtolower($file_ext);
        //检查扩展名
        if (in_array($file_ext, $ext_arr[$dir_name]) === false)
        {
            alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
        }
        
        //创建文件夹
        if ($dir_name !== '')
        {
            $save_path .= $dir_name . "/";
            $save_url .= $dir_name . "/";
            
           try {
    			$content = $ossClient->getObject($bucket, $save_path);
    		} catch (OssException $e) {
    			$file = __FILE__;
    		    $options = array();
    		    $ossClient->multiuploadFile($bucket, $save_path, $file, $options);
    	   }
        }
        
        //新文件名
        $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
      
        $file_path = $save_path . $new_file_name;
        
         try {
    		    $options = array();
    		    $ossClient->multiuploadFile($bucket, $file_path,$tmp_name, $options);
    		} catch (OssException $e) {
    			alert("上传文件失败。");
    	  }
    	  
        header('Content-type: text/html; charset=UTF-8');
        echo json_encode(array('error' => 0, 'url' => IMG_URL.$file_path));
        exit;
    }
}
elseif ($action='list_files'){

   //根目录路径，可以指定绝对路径，比如 /var/www/attached/
    $root_path = 'union/shopimg/user_img/'.$page_member_id.'/';
   
    //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
    $root_url = 'union/shopimg/user_img/'.$page_member_id.'/';
    //扩展名
    $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp','swf', 'flv');
    
    //目录名
    $dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
    if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) exit("Invalid Directory name.");
    
    if ($dir_name !== '')
    {
        $root_path .= $dir_name . "/";
        $root_url .= $dir_name . "/";
    }
    
     //根据path参数，设置各路径和URL
    if (empty($_GET['path']))
    {
        $current_path = $root_path . '/';
        $current_url = $root_url;
        $current_dir_path = '';
        $moveup_dir_path = '';
    }
    else
    {
        $current_path = $root_path . '/' . $_GET['path'];
        $current_url = $root_url . $_GET['path'];
        $current_dir_path = $_GET['path'];
        $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
    }
    
    
    //排序形式，name or size or type
    $order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);

    //不允许使用..移动到上一级目录
    if (preg_match('/\.\./', $current_path)) exit('Access is not allowed.');
    
    //最后一个字符不是/
    if (!preg_match('/\/$/', $current_path)) exit('Parameter is not valid.');
    
    
    $prefix = $root_path;
    $delimiter = '/';
    $nextMarker = '';
    $maxkeys = 500;
    
    $file_list = array();
    while (true) {
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
        for($i=1;$i<count($listObject);$i++){
        	$rs=array_values((array)$listObject[$i]);
        	//获得文件扩展名
        	$temp_arr = explode(".", $rs[0]);
        	$file_ext = array_pop($temp_arr);
        	$file_ext = trim($file_ext);
        	$file_ext = strtolower($file_ext);
        	$file_list[$i]['is_dir'] = false;
        	$file_list[$i]['has_file'] = false;
        	$file_list[$i]['filesize'] = filesize(IMG_URL.$rs[0]);
        	$file_list[$i]['dir_path'] = '';
        	$file_ext = strtolower(pathinfo(IMG_URL.$rs[0], PATHINFO_EXTENSION));
        	$file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
        	$file_list[$i]['filetype'] = $file_ext;
        	$file_list[$i]['filename'] = IMG_URL.$rs[0]; //文件名，包含扩展名
        	$file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime(OSS_URL.$rs[0])); //文件最后修改时间
        }
            
        if ($nextMarker === '') {
            break;
        }
    }
 
   //排序
    usort($file_list, 'cmp_func');
    
    $result = array();
    //相对于根目录的上一级目录
    $result['moveup_dir_path'] = $root_url;
    //相对于根目录的当前目录
    $result['current_dir_path'] = $current_dir_path;
    //当前目录的URL
    $result['current_url'] = '';
    //文件数
    $result['total_count'] = count($file_list);
    //文件列表数组
    $result['file_list'] = $file_list;

    //输出JSON字符串
    header('Content-type: application/json; charset=UTF-8');
    echo json_encode($result);
    exit;
}

function cmp_func($a, $b)
{
    global $order;
    if ($a['is_dir'] && !$b['is_dir']) return -1;
    if (!$a['is_dir'] && $b['is_dir']) return 1;
    
    if ($order == 'size')
    {
        if ($a['filesize'] > $b['filesize']) return 1;
        if ($a['filesize'] < $b['filesize']) return -1;
        return 0;
    }
    else if ($order == 'type')
    {
        return strcmp($a['filetype'], $b['filetype']);
    }
    else
    {
        return strcmp($a['filename'], $b['filename']);
    }
    
}

function alert($msg)
{
	header('Content-type: text/html; charset=UTF-8');
	echo json_encode(array('error' => 1, 'message' => $msg));
	exit;
}
