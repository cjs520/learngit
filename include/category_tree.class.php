<?php

/**
 * MVM_MALL 网上商店系统 分类树处理类
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-18 $
 * $Id: category_tree.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if (!defined('MVMMALL')) exit('Access Denied');
class tree
{
    var $arr_tree = array(); 
	var $left_node=array();
	
	function new_node($id,$name,$parent,$show,$order,$arr_other)
	{
		$parent=(int)$parent;
		$layer=0;
		do
		{
		    if($parent == 0) break;
		    
		    if(isset($this->arr_tree[$parent][2])) $layer=(int)$this->arr_tree[$parent][2]+1;
			else 
			{
				if(!isset($this->left_node[$parent])) $this->left_node[$parent]=array();
				$this->left_node[$parent][]=$id;
			}
		}while(0);
		
		$this->arr_tree[$id] = array($name,$parent,$layer,$show,$order,$arr_other);
		$this->set_left_node($id);
	}
	
	function set_left_node($id)
	{
		if(!isset($this->left_node[$id])) return ;
		if(!isset($this->arr_tree[$id])) return ;
		foreach ($this->left_node[$id] as $val)
		{
			$this->arr_tree[$val][2]=(int)$this->arr_tree[$id][2]+1;
			$this->set_left_node($$val);
		}
		unset($this->left_node[$id]);
	}
	
    function get_childs($id=0)    //获取子节点，如果没有子节点，则返回一个空的数组
    {
		$childs = array();
		foreach ($this->arr_tree as $key => $value)
		{
			if ($id != $value[1]) continue;
			
			$childs[$key] = $value;
			$childs = $childs + $this->get_childs($key);
		}
		return $childs;
	}
	
	function get_id($id)    //获取子节点的id，子节点的id依次存储在一个一维数组中，如果没有子节点，则返回false
	{
		$childs = $this->get_childs($id);
		if (!$childs) return false;
		$childsid = array();
		for (reset($childs);$key=key($childs);next($childs)) $childsid[] = $key;
		
		return $childsid;
	}
	
	function get_node($id)    //获取节点信息，返回一个一维数组，共三个值，分别为节点名称，节点的父节点id，节点的层次，如果该节点不存在，返回false
	{
		return isset($this->arr_tree[$id]) ? $this->arr_tree[$id] : false;
	}
	
    function get_parent($id)    //获取父节点的信息，返回的是一个一维数组，其值分别为：父节点的id，父节点的名称，父节点的父节点，父节点所在的层次，如果该节点不存在或为根节点，返回false
	{
		if (!isset($this->arr_tree[$id])) return false;
		if (!isset($this->arr_tree[$this->arr_tree[$id][1]])) return false;
		$parent = $this->arr_tree[$this->arr_tree[$id][1]];
		return array_merge(array($this->arr_tree[$id][1]),$parent);
	}
	
    function get_root()    //获取根节点
	{
		$rootnodes = array();
		foreach ($this->arr_tree as $key => $value) 
		    $value[1] || $rootnodes[$key] = $value;
		return $rootnodes;
	}
	
    function get_layer($id)    //获取一个节点的层次
	{
		return $this->arr_tree[$id][2];
	}
}//end class tree