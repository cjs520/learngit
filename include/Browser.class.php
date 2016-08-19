<?php
/**
 * MVMMALL 网上商店系统
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2015-09-01 $
 * $Id: Browser.func.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

class Browser
{
    const WX='micromessenger';
    const IPHONE='iphone';
    const ANDROID='android';
    const MSIE='msie';
    const CHROME='chrome';
    const FIREFOX='firefox';
    const OTHER='other';
    
    
    public static function IsMobile()
    {
        //return true;
    
        if($_GET['ismobile']=='ismobile') return  true;
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) return true;
    
        if (isset ($_SERVER['HTTP_USER_AGENT']))
        {
            $clientkeywords = 'nokia|sony|ericsson|mot|samsung|htc|sgh|lg|sharp|sie-|philips|panasonic|alcatel|lenovo'.
                              '|iphone|ipod|blackberry|meizu|android|netfront|symbian|ucweb|windowsce|palm|operamini|operamobi|openwave|nexusone|cldc|midp|wap|mobile'.
                              '|micromessenger';
            if (preg_match("/($clientkeywords)/i", strtolower($_SERVER['HTTP_USER_AGENT']))) return true;
        }
    
        if (isset ($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) return true;
    
        if (isset ($_SERVER['HTTP_ACCEPT']))
        {
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && 
                (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || 
                (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
            {
                return true;
            }
        }
    
        return false;
    }//end IsMobile
    
    public static function BrowserType()
    {
        $arr_browser_type=array('chrome','firefox','msie','micromessenger');
        $type='other';
    
        foreach ($arr_browser_type as $val)
        {
            if(stristr($_SERVER['HTTP_USER_AGENT'],$val))
            {
                $type=$val;
                break;
            }
        }
        return $type;
    }//end BrowserType
    
    public static function MobileType()
    {
        $arr_browser_type=array('android','iphone');
        $type='other';
    
        foreach ($arr_browser_type as $val)
        {
            if(stristr($_SERVER['HTTP_USER_AGENT'],$val))
            {
                $type=$val;
                break;
            }
        }
        return $type;
    }//end MobileType
}