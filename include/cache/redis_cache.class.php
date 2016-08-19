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
 * $Id: redis_cache.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

class redis_cache
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
        if($this->user_cache) $this->cache_dir='union/';
        else $this->cache_dir='';
        $user = "qzredis";
        $pwd = "ASDf16888";
        $this->mem=new Redis();
        $this->mem->connect('eb8859a8ef1d4128.m.cnsza.kvstore.aliyuncs.com','6379'); 
        if ($this->mem->auth($user . ":" . $pwd) == false) {
        die($this->mem->getLastError());
        }
        
        $this->mem->select($this->cache_settings['redis_db']);
    }
    
    public function __destruct()
    {
        $this->mem->close();
    }
    
    public function get_cache($cache_id,$userid=0)
    {
        $memcache_key='';
        if($this->user_cache)
        {
            $userid=(int)$userid;
    	    $cache_path=$userid==0?$this->cache_dir:'user_cache/'.$userid.'/';
    	    $memcache_key=$this->cache_settings['redis_pre'].$cache_path.$cache_id;
        }
        else $memcache_key=$this->cache_settings['redis_pre'].$cache_id;
        
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
                $data=unserialize($data);
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
        $ser_cache_data=serialize($cache_data);
        
        if($this->user_cache)
        {
            $userid=(int)$userid;
    	    $cache_path=$userid==0?$this->cache_dir:'user_cache/'.$userid.'/';
    	    
    	    $this->mem->set($this->cache_settings['redis_pre'].$cache_path.$cache_id,$ser_cache_data,43200);
    	    $this->mem->set($this->cache_settings['redis_pre'].$cache_path.$cache_id.'_expire',$this->expire,43200);
        }
        else
        {
            $this->mem->set($this->cache_settings['redis_pre'].$cache_id,$ser_cache_data,43200);
            $this->mem->set($this->cache_settings['redis_pre'].$cache_id.'_expire',$this->expire,43200);
        }
        return $cache_data;
    }//end put_cache
    
    public function delete($cache_name,$supplier_id)
    {
        $supplier_id=(int)$supplier_id;
        if($supplier_id>0)
        {
            $this->mem->delete($this->cache_settings['redis_pre']."user_cache/$supplier_id/".$cache_name);
            $this->mem->delete($this->cache_settings['redis_pre']."user_cache/$supplier_id/".$cache_name.'_expire');
        }
        else
        {
            $this->mem->delete($this->cache_settings['redis_pre'].$cache_name);
            $this->mem->delete($this->cache_settings['redis_pre'].$cache_name.'_expire');
            $this->mem->delete($this->cache_settings['redis_pre'].'union/'.$cache_name);
            $this->mem->delete($this->cache_settings['redis_pre'].'union/'.$cache_name.'_expire');
        }
    }//end delete
    
    public function flush($only_main)
    {
        $this->mem->flushDB();
    }//end flush
    
    private function get_lock($key,$expire=3)
    {
        $rtl=false;
        $expire=(int)$expire;
        if($expire>10 || $expire<=0) $expire=3;
        $rtl=$this->mem->setnx($key.'__lock','lock');
        if($rtl) $this->mem->setTimeout($key.'__lock',$expire);
        return $rtl;
    }//end function get_lock
    
    private function del_lock($key)
    {
        $this->mem->delete($key.'__lock');
    }//end function del_lock
    
    public function write_cache($cache_file,$cache_data,$cache_var='cache')
    {
        $memcache_key=$this->cache_settings['redis_pre'].md5($cache_file);
        $ser_cache_data=serialize($cache_data);
        
        $this->mem->set($memcache_key,$ser_cache_data,43200);
        $this->mem->set($memcache_key.'_expire',$this->expire,43200);
        return $cache_data;
    }//end write_cache
    
    public function read_cache($cache_file,$cache_func,$cache_param,$cache_var='cache',$context=null)
    {
        $memcache_key=$this->cache_settings['redis_pre'].md5($cache_file);
        
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
                $data=unserialize($data);
                if($expire-$this->now_time<1800 && $this->get_lock($memcache_key,3))    //如果缓存过期时间小于半小时，并能获取更新锁，从容更新
                {
                    if(is_null($context))
                    {
                        $data=$this->write_cache($cache_file,$cache_func($cache_param),$cache_var);
                    }
                    else
                    {
                        $data=$this->write_cache($cache_file,$context->$cache_func($cache_param),$cache_var);
                    }
                    $this->del_lock($memcache_key);
                }
                break;
            }
            else
            {
                $tick++;
                if($this->get_lock($memcache_key,3))
                {
                    if(is_null($context))
                    {
                        $data=$this->write_cache($cache_file,$cache_func($cache_param),$cache_var);
                    }
                    else
                    {
                        $data=$this->write_cache($cache_file,$context->$cache_func($cache_param),$cache_var);
                    }
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
    }//end read_cache
    
    public function remove_cache($cache_file)
    {
        $memcache_key=$this->cache_settings['redis_pre'].md5($cache_file);
        $this->mem->delete($memcache_key);
    }//end remove_cache
}