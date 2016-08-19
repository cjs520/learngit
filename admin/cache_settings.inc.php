<?php

/**
 * MVM_MALL 网上商店系统  配送插件管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: cache_settings.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

$cache_settings_file='data/malldata/cache_settings.config.php';
if ($action=='list')
{
    if($cache_settings['cache_type']=='memcache') $cache_type_memcache_checked='checked';
    else if($cache_settings['cache_type']=='redis') $cache_type_redis_checked='checked';
    else if($cache_settings['cache_type']=='file') $cache_type_file_checked='checked';
    
    if($cache_settings['memcache_ext']=='Memcache') $memcache_ext_memcache_checked='checked';
    else if($cache_settings['memcache_ext']=='Memcached') $memcache_ext_memcached_checked='checked';
    
    require template('cache_settings');
    footer();
}
else if($action=='add')
{
    if($_POST && (int)$step==1)
    {
    	$str_write="<?php \n";
    	$cache_settings=array(
    	    'cache_type'=>dhtmlchars($cache_type),
    	    'memcache_server'=>dhtmlchars($memcache_server),
    	    'memcache_port'=>intval($memcache_port),
    	    'memcache_pre'=>dhtmlchars($memcache_pre),
    	    'memcache_ext'=>dhtmlchars($memcache_ext),
            'redis_server'=>dhtmlchars($redis_server),
            'redis_port'=>intval($redis_port),
            'redis_db'=>intval($redis_db),
            'redis_pre'=>dhtmlchars($redis_pre)
    	);
    	$str_write.='$cache_settings='.var_export($cache_settings,true).";\n";
    	
    	$str_write.='?>';
    	file_put_contents($cache_settings_file,$str_write);
    	copy($cache_settings_file,'union/'.$cache_settings_file);
    	admin_log("设置商城缓存类型");
    	
    	show_msg('修改成功',"admincp.php?module=$module&action=list");
    }
}
else if($action=='test_memcache')
{
    $memcache_server=dhtmlchars($memcache_server);
    $memcache_port=(int)$memcache_port;
    $memcache_ext=dhtmlchars($memcache_ext);
    
    if(!$memcache_server || $memcache_port<=0) exit('ERR:请填写正确的memcache服务器IP与端口');
    if(!$memcache_ext) exit('ERR:请指定正确的Memcache扩展类型');
    if(!class_exists($memcache_ext)) exit("ERR:您指定的扩展{$memcache_ext}未安装");
    
    $m=new $memcache_ext();
    $rtl=$m->addServer($memcache_server,$memcache_port);
    if(!$rtl) exit('ERR:服务器添加失败，请检索服务器配置');
    $rtl=$m->set('helo','hello memcache');
    if(!$rtl) exit('ERR:写入数据失败，请检查服务器');
    $str=$m->get('helo');
    if($str!='hello memcache') exit("ERR:读出的数据为{$str},错误，请检查服务器配置");
    
    exit('OK:Memcache可以正常使用，回显数据：'.$str);
}
else if($action='test_redis')
{
    $redis_server=dhtmlchars($redis_server);
    $redis_port=(int)$redis_port;
    $redis_db=(int)$redis_db;
    
    if(!$redis_server || $redis_port<=0) exit('ERR:请填写正确的Redis服务器IP与端口');
    if(!class_exists('Redis')) exit('ERR:您还未安装Redis扩展');
    
    $redis=new Redis();
    $rtl=$redis->open($redis_server,$redis_port);
    if(!$rtl) exit('ERR:Redis服务器连接失败');
    $rtl=$redis->select($redis_db);
    if(!$rtl) exit('ERR:指定的数据库选择失败');
    
    $rtl=$redis->setex('helo',10,'hello redis');
    if(!$rtl) exit('ERR:数据写入失败，请检查服务器');
    $str=$redis->get('helo');
    if($str!='hello redis') exit("ERR:读出的数据为{$str}，错误，请检查服务器配置");
    $redis->close();
    
    exit('OK:Redis可以正常使用，回显数据：'.$str);
}
else show_msg('pass_worng');
