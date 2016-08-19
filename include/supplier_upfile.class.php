<?php
//文件上传	
/**
 * MVM_MALL 网上商店系统  文件上传类
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-03 $
 * $Id: upfile.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once __DIR__ . '/oss-sdk/Common.php';
use OSS\OssClient;
use OSS\Core\OssUtil;
use OSS\Core\OssException;
class upfile
{
	private $filename;    //上传文件信息
    private $savename;    //保存名
    private $savepath = 'union/shopimg';    //原文件保存路径
    private $waterpath = 'union/watermark';    //水印保存路径
    private $format = '';    //文件格式限定，为空时不限制格式
    private $overwrite = 1;    //覆盖模式 $overwrite = 0 时不覆盖同名文件 $overwrite = 1 时覆盖同名文件
    private $maxsize = 210000000;    //文件最大字节
    private $ext;    //文件扩展名
    private $errno = 0;    //错误代号
    public $bucket;
    public $ossClient;


    // 构造函数
    // $path 保存路径
    // $format 文件格式(用逗号分开)
    // $maxsize 文件最大限制
    // $over 复盖参数
    public function __construct($format = '',$path = '',$maxsize = 0, $over = 0,$supplier_id=0)
    {
    	$this->bucket = Common::getBucketName();
    	$this->ossClient = Common::getOssClient();
    	if (is_null($this->ossClient)) $this->halt('buctket对象为空！');
    	 
    	 
    	if (!$path)
    	{
    		if((int)$supplier_id!=0) $this->savepath.="/user_img/$supplier_id";
    		$path = $this->savepath.'/'.date('Ym').'/';
    		try {
    			$content = $this->ossClient->getObject($this->bucket, $path);
    		} catch (OssException $e) {
    			$file = __FILE__;
    		    $options = array();
    		    $this->ossClient->multiuploadFile($this->bucket, $path, $file, $options);
    	   }
            $this->savepath = $path;
        }
        else
        {
            $this->savepath = substr($path,  - 1) == '/' ? $path : $path.'/';
        	try {
    			$content = $this->ossClient->getObject($this->bucket, $this->path);
    		} catch (OssException $e) {
    			$file = __FILE__;
    		    $options = array();
    		    $this->ossClient->multiuploadFile($this->bucket,$this->savepath, $file, $options);
    	   }
        }
        
        $this->overwrite = $over; //是否复盖相同名字文件
        $this->maxsize = !$maxsize ? $this->maxsize: $maxsize; //文件最大字节
        $this->format = $format;
    }

  
    // 功能：检测并组织文件
    // $form 文件域名称
    // $file 上传文件保存名称，为空或者上传多个文件时由系统自动生成名称
    public function upload($form, $file = '')
    {
    	if (is_array($form)) $filear = $form;
        else $filear = $_FILES[$form];
        //if (!is_writable($this->savepath)) $this->halt("指定的路径不可写，或者没有此路径!");
    
        $this->getext($filear['name']);    //取得扩展名
        $this->set_savename($file);    //设置保存文件名
        $this->copyfile($filear);
        return $this->savepath.$this->savename;
    }

  
    // 功能：检测并复制上传文件
    // $filear 上传文件资料数组
    private function copyfile($filear)
    {
    	if ($filear["size"] > $this->maxsize) $this->halt("上传文件 ".$filear["name"]." 大小超出系统限定值[".strval($this->maxsize/1024)." KB]，不能上传。");
    	if ($this->format != '' && !in_array(strtolower($this->ext), explode(',',strtolower($this->format)))) $this->halt($this->ext." 文件格式不允许上传。");
    		$options = array();
    		$object = $this->savepath.$this->savename;
    		try {
    			$this->ossClient->multiuploadFile($this->bucket,$object, $filear['tmp_name'], $options);
    		} catch (OssException $e) {
    			$this->halt($e->getMessage());
    		}
    	@unlink($filear["tmp_name"]);
    	
    }

  
    // 功能: 取得文件扩展名
    // $filename 为文件名称
    private function getext($filename)
    {
    	$path_info=pathinfo($filename);
    	$this->ext = $path_info['extension'];
    }

   
    // 功能: 设置文件保存名
    // $savename 保存名，如果为空，则系统自动生成一个随机的文件名
    private function set_savename($savename = '')
    {
    	$name = $savename;
    	if (!$savename)    // 如果未设置文件名，则生成一个随机文件名
    	{
    		$rnd = rand(100, 999);
            $name = time() + $rnd;
            $name = $name.".".$this->ext;
    	}
        return $this->savename = $name;
    }

  
    //* 功能：错误提示
    //* $msg 为输出信息
    private function halt($msg)
    {
    	global $lang;
  	    $str = $msg;
  	    require_once template('showmsg');
	    $output = str_replace(array('<!--<!---->','<!---->',"\r"),'',ob_get_contents());
	    ob_end_clean();
	    exit($output);
    }
}

?>
