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
 * $Id: swsc.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
!defined('MVMMALL') && exit('Access Denied');
class scsw
{
    public static function create_tag($content,$tag_num=5)
    {
        require 'include/scsw/pscws4.class.php';
        $cws = new PSCWS4('gbk');
        $cws->set_dict('include/scsw/dict.xdb');
        $cws->set_rule('include/scsw/rules.ini');
        
        $tag_num=(int)$tag_num;
        if($tag_num<=0) $tag_num=5;
        $content=mb_convert_encoding($content,'GBK','UTF-8');
        $cws->send_text($content);
        $ret = $cws->get_tops($tag_num,'r,v,p');
        $arr_rtl=array();
        foreach ($ret as $val)
        {
            $arr_rtl[]=mb_convert_encoding($val['word'],'UTF-8','GBK');
        }
        $cws->close();
        
        return implode(' ',$arr_rtl);
    }
}//end class shop
?>