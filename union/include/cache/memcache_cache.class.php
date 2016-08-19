<?php

/**
 * MVM_MALL 网上商店系统  购物车
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-02 $
 * $Id: memcache_cache.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

class memcache_cache
{
    private $user_cache;
    private $cache_settings;
    private $mem;
    private $now_time;
    private $expire;
    private $cache_dir;
    private $context;
    
    public function __construct($context,$user_cache=false)
    {
        $this->cache_settings=$GLOBALS['cache_settings'];
        $this->now_time=$GLOBALS['m_now_time'];
        $this->expire=$this->now_time+12*3600;    //缓存12小时自动更新
        $this->context=$context;
        $this->user_cache=$user_cache;
        $this->cache_dir='union/';
        
        if($this->cache_settings['memcache_ext']=='Memcached') $this->mem=new Memcached();
        else $this->mem=new Memcache();
        
        $this->mem->addServer($this->cache_settings['memcache_server'],$this->cache_settings['memcache_port']); 
    }
    
    public function __destruct()
    {
        if($this->cache_settings['memcache_ext']=='Memcached') $this->mem->quit();
        else $this->mem->close();
    }
    
    public function get_cache($cache_id,$userid=0)
    {
        $memcache_key='';
        $userid=(int)$userid;
    	$cache_path=$userid==0?$this->cache_dir:'user_cache/'.$userid.'/';
    	$memcache_key=$this->cache_settings['memcache_pre'].$cache_path.$cache_id;
        
        //防并发读取
        $tick=0;
        $data=null;
        do
        {
            $data=$this->mem->get($memcache_key);
            $expire=intval($this->mem->get($memcache_key.'_expire'));
            if($tick>=3) break;
            
            if ($data!==false)
            {
                if($expire-$this->now_time<1800 && $this->get_lock($memcache_key,3))    //如果缓存过期时间小于半小时，并能获取更新锁，从容更新
                {
                    $data=$this->put_cache($cache_id,$userid);
                    $this->del_lock($memcache_key);
                }
                break;
            }
            else
            {
                $tick++;
                if($this->get_lock($memcache_key,3))
                {
                    $data=$this->put_cache($cache_id,$userid);
                    $this->del_lock($memcache_key);
                    break;
                }
                else
                {
                    sleep(1);
                }
            }
        }while (1);
        return $data;
    }//end get_cache
    
    public function put_cache($cache_id,$userid=0)
    {
        $cache_data=$this->context->$cache_id();
        
        $userid=(int)$userid;
    	$cache_path=$userid==0?$this->cache_dir:'user_cache/'.$userid.'/';
    	    
    	$this->mem->set($this->cache_settings['memcache_pre'].$cache_path.$cache_id,$cache_data);
    	$this->mem->set($this->cache_settings['memcache_pre'].$cache_path.$cache_id.'_expire',$this->expire);
        
        return $cache_data;
    }//end put_cache
    
    private function get_lock($key,$expire=3)
    {
        $rtl=false;
        $expire=(int)$expire;
        if($expire>10 || $expire<=0) $expire=3;
        if($this->cache_settings['memcache_ext']=='Memcached') $rtl=$this->mem->add($this->cache_settings['memcache_pre'].$key.'___lock','lock',$expire);
        else $rtl=$this->mem->add($this->cache_settings['memcache_pre'].$key.'___lock','lock',false,$expire);
        return $rtl;
    }//end function _add
    
    private function del_lock($key)
    {
        $this->mem->delete($this->cache_settings['memcache_pre'].$key.'__lock');
    }//end function del_lock
}