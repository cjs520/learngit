<?php
/**
 * MVM_MALL 网上商店系统  验证码类
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: captcha.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

class Captcha
{
    var $mCheckCodeNum = 4;    //验证码位数
    var $mCheckCode = '';    //产生的验证码
    var $mCheckImage  = '';    //验证码的图片
    var $mDisturbColor  = '';    //干扰像素
    var $mCheckImageWidth = 80;    //验证码的图片宽度
    var $mCheckImageHeight  = 20;//验证码的图片宽度
    
    function OutFileHeader()
    {
    	header ("Content-type: image/png");
    }

    function CreateCheckCode()
    {
    	session_start();
        $_SESSION['mm_capcha'] = $this->mCheckCode = strtoupper(substr(md5(rand()),0,$this->mCheckCodeNum));
        return $this->mCheckCode;
    }
    
    function CreateImage()
    {
    	$this->mCheckImage = @imagecreate ($this->mCheckImageWidth,$this->mCheckImageHeight);
        imagecolorallocate($this->mCheckImage, 200, 200, 200);
        return $this->mCheckImage;
    }
    
    function SetDisturbColor()
    {
    	for ($i=0;$i<=128;$i++)
    	{
    		$this->mDisturbColor = imagecolorallocate ($this->mCheckImage, rand(0,255), rand(0,255), rand(0,255));
            imagesetpixel($this->mCheckImage,rand(2,128),rand(2,38),$this->mDisturbColor);
        }
    }

    function SetCheckImageWH($width,$height)
    {
    	if(floatval($width) <= 0 || floatval($height) <= 0) return false;
    	$this->mCheckImageWidth = $width;
    	$this->mCheckImageHeight = $height;
    	return true;
    }

    function WriteCheckCodeToImage()
    {
    	for ($i=0;$i<=$this->mCheckCodeNum;$i++)
    	{
    		$bg_color = imagecolorallocate ($this->mCheckImage, rand(0,255), rand(0,128), rand(0,255));
            $x = floor($this->mCheckImageWidth/$this->mCheckCodeNum)*$i;
            $y = rand(0,$this->mCheckImageHeight-15);
            imagechar ($this->mCheckImage, 5, $x, $y, $this->mCheckCode[$i], $bg_color);
    	}
    }

    function OutCheckImage()
    {
    	$this ->OutFileHeader();
        $this ->CreateCheckCode();
        $this ->CreateImage();
        $this ->SetDisturbColor();
        $this ->WriteCheckCodeToImage();
        imagepng($this->mCheckImage);
        imagedestroy($this->mCheckImage);
    }

    function CheckCode($code)
    {
    	if(!$code) return false;
    	$code=strtolower($code);
    	if(strtolower($_SESSION['mm_capcha']) != $code) return false;
    	$_SESSION['mm_capcha']=rand(1000,9999);
    	return true;
    }
}//end class Captcha
?>