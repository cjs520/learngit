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
        $this->cache_dir=MVMMALL_CACHE;
        
        $this->user_cache=$user_cache;    //在子站中，这个参数必然为true
        $this->context=$context;
    }//end __construct
    
    public function get_cache($cache_id,$userid=0)
    {
        $userid=(int)$userid;
    	$cache_path=$userid==0?$this->cache_dir:$this->cache_dir.'user_cache/'.$userid.'/';
    	$userid!=0 && $this->DetectDir($cache_path);
    	
        if (file_exists($cache_path.$cache_id.'.cache.php')) include $cache_path.$cache_id.'.cache.php';
        else $$cache_id=$this->put_cache($cache_id,$userid);
        
        return $$cache_id;
    }//end get_cache
    
    public function put_cache($cache_id,$userid=0)
    {
        $userid=(int)$userid;
    	$cache_path=$userid==0?$this->cache_dir:$this->cache_dir.'user_cache/'.$userid.'/';
    	$userid!=0 && $this->DetectDir($cache_path);
    	
    	$cache_data=$this->context->$cache_id();
        file_put_contents($cache_path.$cache_id.'.cache.php','<?php $'.$cache_id.'='.var_export($cache_data,true).'; ?>');
        
        return $cache_data;
    }//end put_cache
     
    private function DetectDir($path='')
    {
    	if($path=='') return;
    	if(!is_dir($path)) mkdir($path,0755,true);
    }//end DetectDir
    
}