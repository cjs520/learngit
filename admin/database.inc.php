<?php

/**
 * MVM_MALL 网上商店系统 数据库备份
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: database.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='export')
{
    if($dosubmit)
    {
        $fileid = isset($fileid) ? $fileid : 1;
        if($fileid==1 && $tables)
        {
            if(!isset($tables) || !is_array($tables)) show_msg('up_table');
            $random = mt_rand(1000, 9999);
            cache_write('bakup_tables.php', $tables);
        }
        else
        {
            if(!$tables = cache_read('bakup_tables.php')) show_msg('up_table');
        }
        $sqldump = '';
        $tableid = isset($tableid) ? $tableid - 1 : 0;
        $startfrom = isset($startfrom) ? intval($startfrom) : 0;
        $tablenumber = count($tables);
        for($i = $tableid; $i < $tablenumber && strlen($sqldump) < $sizelimit * 1000; $i++)
        {
            $sqldump .= sql_dumptable($tables[$i], $startfrom, strlen($sqldump));
            $startfrom = 0;
        }
        if(trim($sqldump))
        {
            $sqldump = "#mvmmall.cn Created\n# --------------------------------------------------------\n\n\n".$sqldump;
            $tableid = $i;
            $filename = $con_db_name.'_'.date('Ymd').'_'.$random.'_'.$fileid.'.sql';
            $fileid++;
            $bakfile ='data/db/'.$filename;
            if(!is_writable('data/db/')) show_msg('data_written');
            file_put_contents($bakfile, $sqldump);
            show_msg("wait_files",'admincp.php?module='.$module.'&action='.$action.'&sizelimit='.$sizelimit.'&tableid='.$tableid.'&fileid='.$fileid.'&startfrom='.$startrow.'&random='.$random.'&dosubmit=1');
        }
        else
        {
            cache_delete('bakup_tables.php');
            show_msg('backup_ready',"admincp.php?module=$module&action=$action");
        }
        exit;
    }

    else {
        $size = $bktables = $bkresults = $results= array();
        $k = 0;
        $totalsize = 0;
        $query = $db->query("SHOW TABLES FROM $con_db_name LIKE '%".dhtmlchars($tablepre)."%'");
        while($r = $db->fetch_row($query))
        {
            $tables[$k] = $r[0];
            $count = $db->get_one("SELECT count(*) as number FROM $r[0] WHERE 1");
            $results[$k] = $count['number'];
            $bktables[$k] = $r[0];
            $bkresults[$k] = $count['number'];
            $q = $db->query("SHOW TABLE STATUS FROM `".$con_db_name."` LIKE '".$r[0]."'");
            $s = $db->fetch_array($q);
            $size[$k] = round($s['Data_length']/1024/1024, 2);
            $totalsize += $size[$k];
            $k++;
        }
        include_once template('database');
        footer();
    }
}

/**数据库恢复**/
else if ($action=='import')
{
    if($dosubmit)
    {
        $fileid = $fileid ? $fileid : 1;
        $filename = $pre.$fileid.'.sql';
        $filepath = MVMMALL_ROOT.'data/db/'.$filename;
        if(file_exists($filepath))
        {
            $sql = file_get_contents($filepath);
            sql_execute($sql);
            $fileid++;
            show_msg("into_success","admincp.php?module=$module&action=".$action."&pre=".$pre."&fileid=".$fileid."&dosubmit=1");
        }
        else {
            show_msg("recovery_success","admincp.php?module=$module&action=$action");
        }
    }
	 else
	 {
		 $sqlfiles = glob(MVMMALL_ROOT.'data/db/*.sql');
		 if(is_array($sqlfiles))
		 {
			 $prepre = '';
			 $info = $infos = array();
			 foreach($sqlfiles as $id=>$sqlfile)
			 {
				 preg_match("/([a-z0-9_]+_[0-9]{8}_[0-9a-z]{4}_)([0-9]+)\.sql/i",basename($sqlfile),$num);
				 $info['filename'] = basename($sqlfile);
				 $info['filesize'] = round(filesize($sqlfile)/(1024*1024), 2);
				 $info['maketime'] = date('Y-m-d H:i:s', filemtime($sqlfile));
				 $info['pre'] = $num[1];
				 $info['number'] = $num[2];
				 if(!$id) $prebgcolor = '#E4EDF9';
				 if($info['pre'] == $prepre)
				 {
					 $info['bgcolor'] = $prebgcolor;
				 }
				 else
				 {
					 $info['bgcolor'] = $prebgcolor == '#E4EDF9' ? '#F1F3F5' : '#E4EDF9';
				 }
				 $prebgcolor = $info['bgcolor'];
				 $prepre = $info['pre'];
				 $infos[] = $info;
			 }
		 }
		 include_once template('database_import');
		 footer();
	 }
}

/**删除数据库文件**/
else if ($action=='delete')
{
    file_unlink('data/db/'.$filenames);
    show_msg('successfully_del',"admincp.php?module=$module&action=import");
}

/**下载文件**/
else if ($action=='down')
{
    $filename=trim(strtr($filename, array("\.." => "","\\\\" => "")));
    file_down('data/db/'.$filename);
}

/**上传文件**/
else if ($action=='uploadsql')
{
    if(fileext($_FILES['uploadfile']['name'])!='sql') show_msg('only_format');
    require_once 'include/upfile.class.php';
    $f = new upfile('sql','data/db/');
    $f->upload('uploadfile',$_FILES['uploadfile']['name']);
    show_msg('upload_success',"admincp.php?module=$module&action=import");
}

/**数据库修复**/
else if ($action=='repair')
{
    if($dosubmit)
    {
        if(in_array($operation,array('repair','optimize')))
        {
            foreach ($tables as $val) $db->query("$operation TABLE `$tables`");
        }
        show_msg('sheet_successful',"admincp.php?module=$module&action=$action");
    }
    else
    {
        $tables = array();
        $query = $db->query("SHOW TABLES FROM $con_db_name LIKE '%".dhtmlchars($tablepre)."%'");
        while($r = $db->fetch_row($query))
        {
            $tables[] = $r[0];
        }
        include_once template('database_epair');
        footer();
    }
}
/**执行SQL语句**/
else if ($action=='query')
{
    if($m_check_uid!=1) show_msg('store_founder');
    if($dosubmit)
    {
        $sql = stripslashes($sql);
        if(trim($sql) != '') sql_execute($sql);
        show_msg('sql_success');
    }
    include template('database_sql');
    footer();
}


function file_down($file)
{
    if($GLOBALS['m_check_uid']!=1) show_msg('down_founder');
	!file_exists($file) && show_msg('file_notexist');
	$filename = $filename ? $filename : basename($file);
	$filetype = fileext($filename);
    $filetype!='sql' && show_msg('only_sql');
	$filesize = filesize($file);
	header('Cache-control: max-age=31536000');
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + 31536000).' GMT');
	header('Content-Encoding: none');
	header('Content-Length: '.$filesize);
	header('Content-Disposition: attachment; filename='.$filename);
	header('Content-Type: '.$filetype);
	readfile($file);
	exit;
}

function fileext($filename)
{
	return trim(substr(strrchr($filename, '.'), 1));
}

function cache_write($file, $string, $type = 'array')
{
	if(is_array($string))
	{
		$type = strtolower($type);
		if($type == 'array')
		{
			$string = "<?php\n return ".var_export($string,TRUE).";\n?>";
		}
		elseif($type == 'constant')
		{
			$data='';
			foreach($string as $key => $value) $data .= "define('".strtoupper($key)."','".addslashes($value)."');\n";
			$string = "<?php\n".$data."\n?>";
		}
	}
	file_put_contents(MVMMALL_ROOT.'data/db/'.$file, $string);
}

function cache_read($file, $mode = 'i')
{
	$cachefile = 'data/db/'.$file;
	if(!file_exists($cachefile)) return array();
	return $mode == 'i' ? include $cachefile : file_get_contents($cachefile);
}

function cache_delete($file)
{
	return @unlink('data/db/'.$file);
}
function sql_dumptable($table, $startfrom = 0, $currsize = 0)
{
	global $db, $sizelimit, $startrow;

	if(!isset($tabledump)) $tabledump = '';
	$offset = 100;
	if(!$startfrom)
	{
		$tabledump = "DROP TABLE IF EXISTS $table;\n";
		$createtable = $db->query("SHOW CREATE TABLE $table");
		$create = $db->fetch_row($createtable);
		$tabledump .= $create[1].";\n\n";
	}

	$tabledumped = 0;
	$numrows = $offset;
	while($currsize + strlen($tabledump) < $sizelimit * 1000 && $numrows == $offset)
	{
		$tabledumped = 1;
		$rows = $db->query("SELECT * FROM $table LIMIT $startfrom, $offset");
		$numfields = $db->num_fields($rows);
		$numrows = $db->num_rows($rows);
		while ($row = $db->fetch_row($rows))
		{
			$comma = "";
			$tabledump .= "INSERT INTO $table VALUES(";
			for($i = 0; $i < $numfields; $i++)
			{
				$tabledump .= $comma."'".mysql_escape_string($row[$i])."'";
				$comma = ",";
			}
			$tabledump .= ");\n";
		}
		$startfrom += $offset;
	}
	$startrow = $startfrom;
	$tabledump .= "\n";
	return $tabledump;
}

function sql_execute($sql)
{
	global $db;
    $sqls = sql_split($sql);
	if(is_array($sqls))
    {
		foreach($sqls as $sql)
		{
			if(trim($sql) != '') 
			{
				$db->query($sql);
			}
		}
	}
	else
	{
		$db->query($sqls);
	}
	return true;
}

function sql_split($sql)
{
	global $db_charset, $db;
	if($db->version() > '4.1' && $db_charset)
	{
		$sql = preg_replace("/TYPE=(InnoDB|MyISAM)( DEFAULT CHARSET=[^; ]+)?/", "TYPE=\\1 DEFAULT CHARSET=".$db_charset,$sql);
	}
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query)
	{
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		$queries = array_filter($queries);
		foreach($queries as $query)
		{
			$str1 = substr($query, 0, 1);
			if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
		}
		$num++;
	}
	return($ret);
}
