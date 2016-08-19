<?php
/**
 * MVM_MALL 网上商店系统  ajax
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2009-03-27 $
 * $Id: task.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
require_once 'include/common.inc.php';

if($module)
{
    include("task/{$module}.php");
}
else if($_GET['quick_task']=='quick_task' || in_array('quick_task',$_SERVER['argv']))
{
    $arr_quick_task=array('clear_auction_complete','clear_cache_db_memory');
    foreach ($arr_quick_task as $val) include("task/$val.php");
}
else
{
    $tasks=glob('task/*.php');
    sort($tasks,SORT_ASC);
    foreach ($tasks as $val) include $val;
}

echo 'mission complete';

function task_log($module,$action,$time)
{
	$row=array(
	    'module'=>$module,
	    'action'=>$action,
	    'register_date'=>$time
	);
	$GLOBALS['db']->insert("`$GLOBALS[tablepre]log_task`",$row);
}
?>