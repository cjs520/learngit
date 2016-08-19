<?php
/**
 * MVM_MALL 网上商店系统 session文件处理类
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Date: 2008-03-24 $
 * $Id: session.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
!defined('MVMMALL') && exit('Access Denied');
class mvm_session
{
	public static function handler()
	{
		session_module_name('user');
        session_set_save_handler(
            array('mvm_session', 'open'),
            array('mvm_session', 'close'),
            array('mvm_session', 'read'),
            array('mvm_session', 'write'),
            array('mvm_session', 'destroy'),
            array('mvm_session', 'gc')
        );
    }
    
    public static function open($sess_path, $sess_name)
    {
    	global $sess_save_path, $sess_save_name, $session_save_dir;
        mkdir($session_save_dir,0777,true);
        $sess_save_path = realpath($session_save_dir); 
        $sess_save_name = $sess_name;
        return true;
    }
    
    public static function close()
    {
    	return true;
    }
    
    public static function read($sid)
    {
    	global $sess_save_path, $sess_save_name, $sess_data_md5;
        $sess_file = $sess_save_path . '/mvm_sess_' . $sid;
        
        if(!file_exists($sess_file)) return '';
        if(($sess_file_size = filesize($sess_file)) != 0 && $sess_file_handle = fopen($sess_file, 'r'))
        {
        	$sess_data = fread($sess_file_handle, $sess_file_size);
            $sess_data_md5 = md5($sess_data);
            fclose($sess_file_handle);
        }
        
        return $sess_data;
    }

    public static function write($sid, $sess_data)
    {
    	global $sess_save_path, $sess_data_md5, $sess_save_name;
        $sess_file = $sess_save_path . '/mvm_sess_' . $sid;
        
        if($sess_data == "") return true;
        if(md5($sess_data) == $sess_data_md5) return touch($sess_file);
        
        if($sess_file_handle = fopen($sess_file, 'w'))
        {
        	$rtl=fwrite($sess_file_handle, $sess_data);
            fclose($sess_file_handle);
            return $rtl;
        }
        return false;
    }
    
    public static function destroy($sid)
    {
        global $sess_save_path, $sess_save_name;
        $sess_file = $sess_save_path . '/mvm_sess_' . $sid;
        return @unlink($sess_file);
    }

    public static function gc($maxlifetime)
    {
        return true;
    }
}//end class mvm_session