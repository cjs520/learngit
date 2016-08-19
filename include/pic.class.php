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
 * $Id: pic.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
!defined('MVMMALL') && exit('Access Denied');
class pic
{
    public static function PicZoom($pic_path,$width=191,$height=147,$bk_rgb=array(255,255,255),$is_del_ori=true)    //图片按比例缩放，并自动补白
    {
        if(!$pic_path || !file_exists($pic_path)) return $pic_path;
        if(!function_exists('imagecreatetruecolor')) return $pic_path;

        $arr_src=array();

        $arr_tmp=getimagesize($pic_path);
        if(!$arr_tmp) return $pic_path;    //不是图片
        //获得原始图像的大小
        $arr_src['width']=$arr_tmp[0];
        $arr_src['height']=$arr_tmp[1];
        //取得文件扩展名
        $basename=basename($pic_path);
        $arr_tmp=explode('.',$basename);
        $ext=strtolower($arr_tmp[sizeof($arr_tmp)-1]);    //扩展名
        //生成新图片路径，一律存成JPG
        $arr_src['new_path']=dirname($pic_path).'/'.time().rand(100,999).'.jpg';
        //根据比例计算新的长宽 目标的坐标x y
        if($width/$height>$arr_src['width']/$arr_src['height'])    //以height为基准
        {
            $arr_src['new_height']=$height;
            $arr_src['new_width']=$arr_src['width']/$arr_src['height']*$height;
            $arr_src['to_x']=($width-$arr_src['new_width'])/2;
            $arr_src['to_y']=0;
        }
        else    //以width为基准
        {
            $arr_src['new_width']=$width;
            $arr_src['new_height']=$arr_src['height']/$arr_src['width']*$width;
            $arr_src['to_x']=0;
            $arr_src['to_y']=($height-$arr_src['new_height'])/2;
        }
        //列出创建图片资源的函数表
        $create_fun_map=array(
            'jpg'=>'imagecreatefromjpeg',
            'jpeg'=>'imagecreatefromjpeg',
            'gif'=>'imagecreatefromgif',
            'png'=>'imagecreatefrompng'
        );
        if(!$create_fun_map[$ext]) return $pic_path;
        $create_fun=$create_fun_map[$ext];

        //参数计算完毕，创建图像
        $dst_img=imagecreatetruecolor($width,$height);
        $bk_color=imagecolorallocate($dst_img,$bk_rgb[0],$bk_rgb[1],$bk_rgb[2]);
        imagefill($dst_img,0,0,$bk_color);
        $src_img=$create_fun($pic_path);
        $src_img2=self::ImageResize($src_img,$arr_src['new_width'],$arr_src['new_height']);    //利用ImageResize高清压缩，先将原图压缩
        imagedestroy($src_img);
        $src_img=$src_img2;
        imagecopymerge($dst_img,$src_img,$arr_src['to_x'],$arr_src['to_y'],0,0,$arr_src['new_width'],$arr_src['new_height'],100);

        //输出到文件
        imagejpeg($dst_img,$arr_src['new_path'],100);

        //销毁各方资源
        imagedestroy($dst_img);
        imagedestroy($src_img);

        if($is_del_ori) @unlink($pic_path);    //删除原图
        return $arr_src['new_path'];
    }//end function PicZoom
    
    public static function PicZoomWithWH($pic_path,$width=-1,$height=-1,$is_del_ori=true)
    {
        if(!$pic_path || !file_exists($pic_path)) return $pic_path;
        if(!function_exists('imagecreatetruecolor')) return $pic_path;
        if($width<=0 && $height<=0) return $pic_path;
        
        $arr_tmp=getimagesize($pic_path);
        if(!$arr_tmp) return $pic_path;    //不是图片
        
        //获得原始图像的大小
        $old_width=$arr_tmp[0];
        $old_height=$arr_tmp[1];
        
        if($old_width<=$width && $old_height<=$height) return $pic_path;
        if($old_width>$width && $width>0)
        {
            $new_width=$width;
            $new_height=ceil($new_width*$old_height/$old_width);
            return self::PicZoom($pic_path,$new_width,$new_height,array(255,255,255),$is_del_ori);
        }
        else if($old_height>$height && $height>0)
        {
            $new_height=$height;
            $new_width=ceil($new_height*$old_width/$old_height);
            return self::PicZoom($pic_path,$new_width,$new_height,array(255,255,255),$is_del_ori);
        }
        
        return $pic_path;
    }//end function PicZoomWithWH
    
    public static function ImageResize ($src, $x, $y)
    {
        $dst=imagecreatetruecolor($x, $y);
        $pals=imagecolorstotal($src);

        for ($i=0; $i<$pals; $i++)
        {
            $colors= imagecolorsforindex($src, $i);
            imagecolorallocate($dst, $colors['red'], $colors['green'], $colors['blue']);
        }
        $scX =(imagesx ($src)-1)/$x;
        $scY =(imagesy ($src)-1)/$y;
        $scX2 =intval($scX/2);
        $scY2 =intval($scY/2);

        for ($j = 0; $j < ($y); $j++)
        {
            $sY = intval($j * $scY);
            $y13 = $sY + $scY2;
            for ($i = 0; $i < ($x); $i++)
            {
                $sX = intval($i * $scX);
                $x34 = $sX + $scX2;
                $c1 = imagecolorsforindex($src, imagecolorat($src, $sX, $y13));
                $c2 = imagecolorsforindex($src, imagecolorat($src, $sX, $sY));
                $c3 = imagecolorsforindex($src, imagecolorat($src, $x34, $y13));
                $c4 = imagecolorsforindex($src, imagecolorat($src, $x34, $sY));
                $r = ($c1['red']+$c2['red']+$c3['red']+$c4['red'])/4;
                $g = ($c1['green']+$c2['green']+$c3['green']+$c4['green'])/4;
                $b = ($c1['blue']+$c2['blue']+$c3['blue']+$c4['blue'])/4;
                imagesetpixel($dst, $i, $j, imagecolorclosest($dst, $r, $g, $b));
            }
        }
        return $dst;
    }//end function ImageResize
}//end class pic
?>