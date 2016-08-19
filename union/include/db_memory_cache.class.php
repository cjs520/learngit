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
 * $Id: db_memory_cache.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
!defined('MVMMALL') && exit('Access Denied');
class db_memory_cache
{
    const GET='GET';
    const FETCH='FETCH';
    const SET='SET';
    const UPDATE='UPDATE';
    const DELETE='DELETE';
    const LOCK='LOCK';
    const UNLOCK='UNLOCK';
    const EXPIRE=7200;
    const LOCK_EXPIRE=10;
    
    private $last_lock_key;
    
    public function __construct(){}
    
    public function op($supplier_id,$cache_name,$k,$v='',$op='GET',$other_param=array())
    {
        global $db,$tablepre,$m_now_time;
        if($op==self::LOCK )
        {
            $supplier_id=0;
            $cache_name='lock';
        }
        
        if($op==self::UNLOCK ) $mem_key=$k;
        else $mem_key=strval($supplier_id).'_'.$cache_name.'_'.strval($k);
        $expire=$m_now_time+self::EXPIRE ;
        
        switch ($op)
        {
            case self::GET :
            case self::FETCH :
                $rtl=$db->get_one("SELECT * FROM `{$tablepre}memory_cache` WHERE k='$mem_key' LIMIT 1");
                if($rtl) return $rtl['v'];
                if($op==self::FETCH ) return '';
                //if specified cache is not exist
                //do update next
            case self::UPDATE :
            case self::SET :
                $v=$op==self::SET  ?$v:$this->$cache_name($k,$other_param);
                $row=array(
                    'k'=>$mem_key,
                    'v'=>$v,
                    'expire'=>$expire
                );
                $db->replace("`{$tablepre}memory_cache`",$row);
                return $v;
                break;
            
            case self::DELETE :
                $db->query("DELETE FROM `{$tablepre}memory_cache` WHERE k='$mem_key'");
                $db->free_result(1);
                break;
            case self::LOCK :
                $expire=$m_now_time+self::LOCK_EXPIRE ;
                $row=array(
                    'k'=>$mem_key,
                    'v'=>$v,
                    'expire'=>$expire
                );
                $db->update("`{$tablepre}memory_cache`",$row," k='$mem_key' AND expire<='$m_now_time'");
                $is_success=($db->affected_rows()>=1);
                if($is_success)
                {
                    $GLOBALS['mvm_lock'][$mem_key]=$mem_key;
                    return $is_success;
                    break;
                }
                
                $db->replace("`{$tablepre}memory_cache`",$row);
                $is_success=($db->affected_rows()<=1);
                if($is_success)
                {
                    $GLOBALS['mvm_lock'][$mem_key]=$mem_key;
                    $this->last_lock_key=$mem_key;
                }
                return $is_success;
                
                break;
            case self::UNLOCK :
                $db->query("DELETE FROM `{$tablepre}memory_cache` WHERE k='$mem_key'");
                $db->free_result(1);
                unset($GLOBALS['mvm_lock'][$mem_key]);
                break;
        }
    }//end op
    
    public function GetLastLockKey()
    {
        return $this->last_lock_key;
    }//end GetLastLockKey
    
    public function Id2Domain($k,$other_param)
    {
        global $db,$tablepre,$_URL;
        $k=(int)$k;
        $arr_url=explode('.',str_replace('http://','',$_URL[0]),2);
     	$url_suffix=$arr_url[1];
     	
        $rtl=$db->get_one("SELECT domain_name FROM `{$tablepre}tld` WHERE supplier_id='$k' AND is_check='1'");
        if($rtl) return 'http://'.$rtl['domain_name'].'/';
        
        $rtl=$db->get_one("SELECT member_homepage FROM `{$tablepre}member_shop` WHERE m_uid='$k' LIMIT 1");
        if(!$rtl) return false;
        $url="http://$rtl[member_homepage].$url_suffix/";
        $url=str_replace("\\",'/',$url);
     	if(substr($url,-1,1)!='/') $url.='/';
     	return $url;
    }//end Id2Domain
    
    public function code($k,$other_param)
    {
        global $db,$tablepre,$m_now_time;
        $code=substr(md5($m_now_time),0,6).rand(10,99);
        return $code;
    }//end code
    
}//end class db_memory_cache
?>