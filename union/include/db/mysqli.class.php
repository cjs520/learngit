<?php
/**
 * MVM_MALL 网上商店系统  mysql数据库类
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: mysqli.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL')) exit;
class dbmysql
{
	private $link=null;
	private $arr_query=array();
	
	public function __construct($con_db_host,$con_db_id,$con_db_pass, $con_db_name = '',$db_charset='utf8',$pconnect = 0)
	{
		!($this->link = mysqli_connect($con_db_host,$con_db_id,$con_db_pass,$con_db_name)) && $this->halt('Can not connect to MySQL server');
		
		$this->query("SET sql_mode=''", $this->link);
		$this->query("SET NAMES $db_charset",$this->link);
		$this->query("USE $con_db_name", $this->link);
		$this->free_result();
	}
	
	public function __destruct()
	{
	    if($this->link==null) return ;
	    $this->free_result();
	    $this->close();
	    $this->link=null;
	}
	
	public function fetch_array($query, $result_type = MYSQLI_ASSOC)
	{
	    return mysqli_fetch_array($query,$result_type);
	}
	
	public function get_all($sql)
    {
        $q = $this->query($sql);
        $arr=array();
        while($rtl=$this->fetch_array($q)) $arr[]=$rtl;
        $this->free_result(1);
        return $arr;
    }
	
	public function update($table, $bind=array(),$where = '')
	{
		$set = array();
	    foreach ($bind as $col => $val) $set[] = "`$col` = '$val'";
	    
	    $sql = 'UPDATE '. $table . ' SET ' . implode(',', $set) . (($where) ? " WHERE $where" : '');
        $this->query($sql);
        $this->free_result(1);
	}
	
	public function insert($table, $bind=array())
	{
		$set = array();
	    $vals = array();
	    foreach ($bind as $col => $val)
	    {
	        $set[] = "`$col`";
	        $vals[] = "'$val'";
	    }
	    $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $set).') ' . 'VALUES (' . implode(', ', $vals).')';
        $this->query($sql);
        $this->free_result(1);
        
        return $this->insert_id();
	}
	
	public function replace($table, $bind=array())
	{
		$set = array();
	    $vals = array();
	    foreach ($bind as $col => $val)
	    {
	        $set[] = "`$col`";
	        $vals[] = "'$val'";
	    }
	    $sql = 'REPLACE INTO ' . $table . ' (' . implode(', ', $set).') ' . 'VALUES (' . implode(', ', $vals).')';
        $this->query($sql);
        
        $this->free_result(1);
	}
	
	public function get_one($sql, $type=MYSQLI_ASSOC)
	{
		!strstr(strtoupper($sql),'LIMIT') && $sql.=' LIMIT 1';
		$query = $this->query($sql, $type);
		$rtl = $this->fetch_array($query, $type);
		
		$this->free_result(1);
		return $rtl ;
	}
	
	public function query($sql, $type = '')
	{
		if(!($query = mysqli_query($this->link,$sql))) $this->halt('MySQL Query Error', $sql);
		
		array_push($this->arr_query,$query);
		return $query;
	}
	
	public function affected_rows()
	{
	    return mysqli_affected_rows($this->link);
	}
	
	public function counter($table_name,$where_str='', $field_name='*')
	{
	    $where_str = trim($where_str);
	    if($where_str && strtolower(substr($where_str,0,5))!='where') $where_str = "WHERE $where_str";
	    $query = " SELECT COUNT($field_name) AS cnt FROM $table_name $where_str ";
	    $rtl = $this->get_one($query);
	    
	    return $rtl['cnt'];
	}
	
	public function error()
	{
		return mysqli_error($this->link);
	}

	public function errno()
	{
		return mysqli_errno($this->link);
	}
	
	public function free_result($time_limit=-1)
	{
	    if($time_limit==-1)
	    {
	        while ($this->arr_query)
		    {
		        $q=array_pop($this->arr_query);
		        mysqli_free_result($q);
		    }
	    }
	    else
	    {
	        while ($this->arr_query && $time_limit>0)
		    {
		        $q=array_pop($this->arr_query);
		        mysqli_free_result($q);
		        $time_limit--;
		    }
	    }
	}
	
	public function insert_id()
	{
	    $rtl=$this->get_one("SELECT last_insert_id() AS insert_id");
	    return $rtl['insert_id'];
	}
	
	public function fetch_row($query)
	{
		return $this->fetch_array($query,MYSQLI_NUM);
	}
	
	public function num_fields($query)
	{
		return mysqli_num_fields($query);
	}
	
	public function num_rows($query)
	{
		return mysqli_num_rows($query);
	}
	
	public function version()
	{
		return mysqli_get_server_version($this->link);
	}
	
	public function close()
	{
		return mysqli_close($this->link);
	}
	
	public function halt($message = '',$sql)
	{
	    //show_msg('数据出错，请联系管理员');
		$sqlerror = $this->error();
		$sqlerrno = $this->errno();
		$sqlerror = str_replace($dbhost,'dbhost',$sqlerror);
		echo"<html><head><title>MvMmall</title><style type='text/css'>P,BODY{FONT-FAMILY:tahoma,arial,sans-serif;FONT-SIZE:10px;}A { TEXT-DECORATION: none;}a:hover{ text-decoration: underline;}TD { BORDER-RIGHT: 1px; BORDER-TOP: 0px; FONT-SIZE: 16pt; COLOR: #000000;}</style><body>\n\n";
		echo"<table style='TABLE-LAYOUT:fixed;WORD-WRAP: break-word'><tr><td>";
		echo"<br /><br /><b>The URL Is</b>:<br />http://$_SERVER[HTTP_HOST]$REQUEST_URI";
		echo"<br /><br /><b>MySQL Server Error</b>:<br />$sqlerror  ( $sqlerrno )";
		echo"<br /><br /><b>MySQL Error SQL</b>:<br />$sql";
		echo"<br /><br /><b>You Can Get Help In</b>:<br /><a target=_blank href=http://www.mvmmall.cn/><b>http://www.mvmmall.cn</b></a>";
		echo"</td></tr></table>";
		exit;
	}
}//end class dbmysql