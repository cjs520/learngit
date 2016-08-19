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
 * $Id: file_cache.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

class file_cache
{
    private $cache_dir;
    private $user_cache;
    private $context;
    public function __construct($context,$user_cache=false)
    {
        if($user_cache) $this->cache_dir='union/data/cache/';
        else $this->cache_dir=MVMMALL_CACHE;
        
        $this->user_cache=$user_cache;
        $this->context=$context;
    }//end __construct
    
    public function get_cache($cache_id,$userid=0)
    {
        if($this->user_cache)
        {
            $userid=(int)$userid;
    	    $cache_path=$userid==0?$this->cache_dir:$this->cache_dir.'user_cache/'.$userid.'/';
    	    $userid!=0 && $this->DetectDir($cache_path);
    	    
    	    if (file_exists($cache_path.$cache_id.'.cache.php')) include $cache_path.$cache_id.'.cache.php';
            else $$cache_id=$this->put_cache($cache_id,$userid);
        }
        else
        {
            if (file_exists($this->cache_dir.$cache_id.'.cache.php')) include $this->cache_dir.$cache_id.'.cache.php';
            else $$cache_id=$this->put_cache($cache_id);
        }
        return $$cache_id;
    }//end get_cache
    
    public function put_cache($cache_id,$userid=0)
    {
        $cache_data=$this->context->$cache_id();
        
        if($this->user_cache)
        {
            $userid=(int)$userid;
    	    $cache_path=$userid==0?$this->cache_dir:$this->cache_dir.'user_cache/'.$userid.'/';
    	    $userid!=0 && $this->DetectDir($cache_path);
    	    
    	    file_put_contents($cache_path.$cache_id.'.cache.php','<?php $'.$cache_id.'='.var_export($cache_data,true).'; ?>');
        }
        else
        {
            file_put_contents($this->cache_dir.$cache_id.'.cache.php','<?php $'.$cache_id.'='.var_export($cache_data,true).'; ?>');
        }
        return $cache_data;
    }//end put_cache
    
    public function delete($cache_name,$supplier_id)
    {
        $supplier_id=(int)$supplier_id;
        if($supplier_id>0)
        {
            file_unlink("union/data/cache/user_cache/{$supplier_id}/{$cache_name}.cache.php");
        }
        else
        {
            file_unlink("union/data/cache/{$cache_name}.cache.php");
            file_unlink("data/cache/{$cache_name}.cache.php");
        }
    }//end delete
    
    public function flush($only_main)
    {
        $cache_list =get_dirinfo(MVMMALL_ROOT.'data/cache');
        foreach ($cache_list as  $val)
        {
            file_unlink(MVMMALL_ROOT."data/cache/$val");
        }
        
        $cache_list =get_dirinfo(MVMMALL_ROOT.'union/data/cache');
        foreach ($cache_list as  $val)
        {
            !is_dir(MVMMALL_ROOT."union/data/cache/$val") && file_unlink(MVMMALL_ROOT."union/data/cache/$val");
        }
        
        $cache_list =get_dirinfo(MVMMALL_ROOT.'data/ad_cache');
        foreach ($cache_list as  $val)
        {
            !is_dir(MVMMALL_ROOT."data/ad_cache/$val") && file_unlink(MVMMALL_ROOT."data/ad_cache/$val");
        }
        if($only_main) return ;
        
        DeleteDir(MVMMALL_ROOT.'union/data/cache/user_cache/');
    }//end flush
    
    private function DetectDir($path='')
    {
    	if($path=='') return;
    	if(!is_dir($path)) mkdir($path,0755,true);
    }//end DetectDir
    
    public function write_cache($cache_file,$cache_data,$cache_var='cache')
    {
        $fp=@fopen($cache_file,'wb+');
        if(!$fp) return false;
        fwrite($fp,'<?php $'.$cache_var.'=');
        fwrite($fp,var_export($cache_data,true));
        fwrite($fp,';?>');
        fclose($fp);
        return $cache_data;
    }//end write_cache
    
    public function read_cache($cache_file,$cache_func,$cache_param,$cache_var='cache',$context=null)
    {
        if(!file_exists($cache_file))
        {
            $data=false;
            if(is_null($context))
            {
                $data=$this->write_cache($cache_file,$cache_func($cache_param),$cache_var);
            }
            else
            {
                $data=$this->write_cache($cache_file,$context->$cache_func($cache_param),$cache_var);
            }
            return $data;
        }
        else
        {
            include $cache_file;
            return $$cache_var;
        }
    }//end read_cache
    
    public function remove_cache($cache_file)
    {
        file_unlink($cache_file);
    }//end remove_cache
}