<?php

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
!defined('MVMMALL') && exit('Access Denied');
require_once __DIR__ . '/oss-sdk/Common.php';
use OSS\OssClient;
use OSS\Core\OssUtil;
use OSS\Core\OssException;
class upfile
{
	private $filename;    //上传文件信息
    private $savename;    // 保存名
    public $savepath = 'shopimg';    // 原文件保存路径
    private $format = '';    // 文件格式限定，为空时不限制格式
    private $overwrite = 1;    // 覆盖模式,0 时不覆盖同名文件,1 时覆盖同名文件
    private $maxsize = 210000000;    //文件最大字节
    private $ext;    //文件扩展名
    private $errno = 0;    //错误代号
    public $bucket;
    public $ossClient;

    public function __construct($format = '',$path = '',$maxsize = 0, $over = 0)    //$path 保存路径,$format 文件格式(用逗号分开),$maxsize 文件最大限制,$over 复盖参数
    {
    	$this->bucket = Common::getBucketName();
    	$this->ossClient = Common::getOssClient();
    	if (is_null($this->ossClient)) $this->halt('buctket对象为空！');
    	
    	if (!$path)
    	{
    		$path =  'union/'.$this->savepath.'/user_img/'.date('Ym').'/';
    	}
    	else $this->savepath = substr($path,  - 1) == "/" ? $path : $path."/";
    
         try {
    			$content = $this->ossClient->getObject($this->bucket, $path);
    		} catch (OssException $e) {
    			$file = __FILE__;
    		    $options = array();
    		    $this->ossClient->multiuploadFile($this->bucket, $path, $file, $options);
    	   }
        
        
        
        $this->savepath = $path;
        $this->overwrite = $over;    //是否复盖相同名字文件
        $this->maxsize = (int)$maxsize<=0 ? $this->maxsize : (int)$maxsize;    //文件最大字节
        $this->format = $format;
        
    }

    public function upload($form, $file = '')
    {
    	$filear = is_array($form) ? $form : $_FILES[$form];
        //if (!is_writable($this->savepath)) $this->halt("指定的路径不可写，或者没有此路径!");
        
        $this->getext($filear["name"]); //取得扩展名
        $this->set_savename($file); //设置保存文件名
        $this->copyfile($filear);
        return str_replace('union/','',$this->savepath.$this->savename);
    }

    private function copyfile($filear)
    {
    	$filear["size"] > $this->maxsize && $this->halt("上传文件 $filear[name] 大小超出系统限定值[".strval($this->maxsize/1024).' KB]，不能上传。');
    	!$this->overwrite && file_exists($this->savename) && $this->halt($this->savename.' 文件名已经存在。');
        $this->format != '' && !in_array($this->ext, explode(',',$this->format)) && $this->halt($this->ext.' 文件格式不允许上传。');
        $options = array();
        $object = $this->savepath.$this->savename;
        try {
    			$this->ossClient->multiuploadFile($this->bucket,$object, $filear['tmp_name'], $options);
    		} catch (OssException $e) {
    			$this->halt($e->getMessage());
    		}
    	@unlink($filear["tmp_name"]);
    }

    private function getext($filename)
    {
    	$path_info=pathinfo($filename);
    	$this->ext = strtolower($path_info['extension']);
    }

    private function set_savename($savename = '')
    {
    	if(is_string($savename) && $savename) $name=$savename;
    	else
    	{
    		$rnd = rand(100, 999);
            $name = strval(time()).strval($rnd);
            $name = $name . '.' . $this->ext;
    	} 
        return $this->savename = $name;
    }

    private function halt($msg)
    {
    	global $lang;
  	    $str = $msg;
  	    require_once template('showmsg');
	    $output = str_replace(array('<!--<!---->','<!---->',"\r"),'',ob_get_contents());
	    ob_end_clean();
	    exit($output);
    }
}//end class upfile
?>