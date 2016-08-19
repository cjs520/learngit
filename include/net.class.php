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
 * $Id: net.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
!defined('MVMMALL') && exit('Access Denied');
class net
{
    public static function get_data($url,$gp='GET',$post_fields=false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        
        if($gp=='POST')
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            if($post_fields)
            {
                if(is_array($post_fields)) $post_fields=http_build_query($post_fields);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            }
        }
        
        $resp = curl_exec($ch);
        curl_close($ch) ;
        return $resp;
    }//end get_data
}//end class pic
?>