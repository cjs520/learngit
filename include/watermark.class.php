<?php

/**
 * MVM_MALL 网上商店系统  水印图片类
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  mvmmall  $
 * $Date: 2008-02-03 $
 * $Id: watermark.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
!defined('MVMMALL') && exit('Access Denied');
class Watermark
{
    var $src_image_name = '';    //输入图片的文件名(必须包含路径名)
    var $jpeg_quality = 90;    //jpeg图片质量
    var $save_file = '';    //输出文件名
    var $wm_image_name = '';    //水印图片的文件名(必须包含路径名.)
    var $wm_image_pos = 3;    //水印图片放置的位置
                              // 0 = middle
                              // 1 = top left
                              // 2 = top right
                              // 3 = bottom right
                              // 4 = bottom left
                              // 5 = top middle
                              // 6 = middle right
                              // 7 = bottom middle
                              // 8 = middle left
    var $wm_image_transition = 80;    //水印图片与原图片的融合度 (1=100)
    var $wm_text = '';    //水印文字(支持中英文以及带有\r\n的跨行文字)
    var $wm_text_size = 20;    //水印文字大小
    var $wm_text_angle = 5;    //水印文字角度,这个值尽量不要更改
    var $wm_text_pos = 3;    //水印文字放置位置
    var $wm_text_font = '';    //水印文字的字体
    var $wm_text_color = '#cccccc';    //水印字体的颜色值

    public function create()
    {
        $src_image_type = $this->get_type($this->src_image_name);
        $src_image = $this->createImage($src_image_type,$this->src_image_name);
        if (!$src_image) return;
        $src_image_w=imagesx($src_image);
        $src_image_h=imagesy($src_image);

        if ($this->wm_image_name)
        {
        	$this->wm_image_name = strtolower(trim($this->wm_image_name));
            $wm_image_type = $this->get_type($this->wm_image_name);
            $wm_image = $this->createImage($wm_image_type,$this->wm_image_name);
            $wm_image_w=ImageSX($wm_image);
            $wm_image_h=ImageSY($wm_image);
            $temp_wm_image = $this->getPos($src_image_w,$src_image_h,$this->wm_image_pos,$wm_image);
            $wm_image_x = $temp_wm_image['dest_x'];
            $wm_image_y = $temp_wm_image['dest_y'];
            
            if($wm_image_type=='png') imagecopy($src_image,$wm_image,$wm_image_x,$wm_image_y,0,0,$wm_image_w,$wm_image_h);
            else imagecopymerge($src_image,$wm_image,$wm_image_x,$wm_image_y,0,0,$wm_image_w,$wm_image_h,$this->wm_image_transition);
            
            imagedestroy($wm_image);
        }
        
        if ($this->wm_text)
        {
        	$temp_wm_text = $this->getPos($src_image_w,$src_image_h,$this->wm_text_pos);
            $wm_text_x = $temp_wm_text['dest_x'];
            $wm_text_y = $temp_wm_text['dest_y'];
            if(preg_match('/([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i', $this->wm_text_color, $color))
            {
            	$red = hexdec($color[1]);
                $green = hexdec($color[2]);
                $blue = hexdec($color[3]);
                $wm_text_color = imagecolorallocate($src_image, $red,$green,$blue);
            }
            else $wm_text_color = imagecolorallocate($src_image, 255,255,255);
            imagettftext($src_image, $this->wm_text_size, $this->wm_text_angle, $wm_text_x, $wm_text_y, $wm_text_color,$this->wm_text_font,  $this->wm_text);
        }

        if ($this->save_file)
        {
        	switch ($this->get_type($this->save_file))
        	{
        		case 'gif': 
        		    $src_img=imagegif($src_image, $this->save_file);
        		    break;
        		case 'jpeg':
        		case 'jpg':
        		    $src_img=imagejpeg($src_image, $this->save_file, $this->jpeg_quality);
        		    break;
        		case 'png':
        		    $src_img=imagepng($src_image, $this->save_file);
        		    break;
        		default:
        		    $src_img=imagejpeg($src_image, $this->save_file, $this->jpeg_quality);
        		    break;
        	}
        }
        imagedestroy($src_image);
    }

    public function createImage($type,$img_name)    //$type: 图片的类型，包括gif,jpg,png , $img_name:  图片文件名，包括路径名，例如 " ./mouse.jpg"
    {
    	if (!$type) $type = $this->get_type($img_name);
        switch ($type)
        {
            case 'gif':
         	    $tmp_img=@imagecreatefromgif($img_name);
         	    break;
         	case 'jpg':
         	case 'jpeg':
         	    $tmp_img=imagecreatefromjpeg($img_name);
         	    break;
         	case 'png':
         	    $tmp_img=imagecreatefrompng($img_name);
         	    break;
            default:
         	    $tmp_img=imagecreatefromstring($img_name);
                break;
         }
         return $tmp_img;
    }

    /*
    getPos 根据源图像的长、宽，位置代码，水印图片id来生成把水印放置到源图像中的位置
    $sourcefile_width:        源图像的宽
    $sourcefile_height: 原图像的高
    $pos:  位置代码
        0 = middle
        1 = top left
        2 = top right
        3 = bottom right
        4 = bottom left
        5 = top middle
        6 = middle right
        7 = bottom middle
        8 = middle left
    $wm_image: 水印图片ID
    */
    public function getPos($sourcefile_width,$sourcefile_height,$pos,$wm_image='')
    {
        if  ($wm_image)
    	{
    		$insertfile_width = ImageSx($wm_image);
            $insertfile_height = ImageSy($wm_image);
    	}
        else
        {
         	$lineCount = explode("\r\n",$this->wm_text);
            $fontSize = imagettfbbox($this->wm_text_size,$this->wm_text_angle,$this->wm_text_font,$this->wm_text);
            $insertfile_width = $fontSize[2] - $fontSize[0];
            $insertfile_height = count($lineCount)*($fontSize[1] - $fontSize[3]);
        }

        switch ($pos)
        {
         	case 0:
         	    $dest_x = ( $sourcefile_width / 2 ) - ( $insertfile_width / 2 );
                $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
                break;
            case 1:
                $dest_x = 0;
                $dest_y = $this->wm_text ? $insertfile_height : 0;
                break;
            case 2:
                $dest_x = $sourcefile_width - $insertfile_width;
                $dest_y = $this->wm_text ? $insertfile_height : 0;
                break;
            case 3:
                $dest_x = $sourcefile_width - $insertfile_width;
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
            case 4:
                $dest_x = 0;
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
            case 5:
                $dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
                $dest_y = $this->wm_text ? $insertfile_height : 0;
                break;
            case 6:
                $dest_x = $sourcefile_width - $insertfile_width;
                $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
                break;
            case 7:
                $dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
            case 8:
                $dest_x = 0;
                $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
                break;
            default:
                $dest_x = $sourcefile_width - $insertfile_width;
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
        }
        return array("dest_x"=>$dest_x,"dest_y"=>$dest_y);
    
    }

    public function get_type($img_name)    //获取图像文件类型
    {
    	$path_info=pathinfo($img_name);
    	return strtolower($path_info['extension']);
    }
    
    public static function SetWaterMark($img_path,&$cfg,$del_ori=true)
    {
        if(!$cfg || !is_array($cfg)) return $img_path;
        if(!file_exists($img_path)) return $img_path;
        
        $water=new Watermark();
        if($cfg['mm_wate_class']==0)
        {
            $water->wm_image_name = ProcImgPath($cfg['mm_wate_img']);
            $water->wm_image_pos = $cfg['mm_watermark'];
        }
        else
        {
            if(!$cfg['mm_text_wate']) return $img_path;
            
            $water->wm_text = $cfg['mm_text_wate'];
            $water->wm_text_size = $cfg['mm_text_size'];
            $water->wm_text_color = $cfg['mm_text_color'];
            $water->wm_text_angle = $cfg['mm_text_angle'];
            $water->wm_text_pos = $cfg['mm_watermark'];
            $water->wm_text_font = "include/fonts/$cfg[mm_text_fonts]";
        }
        
        $water->src_image_name = $img_path;
        $water->save_file = dirname($img_path).'/0'.basename($water->src_image_name);
        $water->create();
        
        if($del_ori) file_unlink($img_path);
        return $water->save_file;
    }
}//end class Watermark
?>