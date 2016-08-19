<?php

/**
 * MVM_MALL 网上商店系统 首页文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: wosmj.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

$nav_array = array(
    'head' => '顶部导航',
    'foot' => '底部导航',
    'help' => '帮助中心'
);

if($action=='list')
{
	require_once MVMMALL_ROOT.'include/pager.class.php';
	$search_sql = "WHERE n1.supplier_id='$page_member_id'";
	switch ($pos)
	{
		case 'head':
		    $search_sql .= " AND n1.pos= 'head'";
		    break;
		case 'foot':
		    $search_sql .= " AND n1.pos= 'foot'";
		    break;
		case 'middle':
		    $search_sql .= " AND n1.pos= 'middle'";
		    break;
		case 'help': 
		    $search_sql .= " AND n1.pos= 'help'";
		    break;
	}
	
	$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}nav` n1 $search_sql");;
	$total_count=$rtl['cnt'];
	
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$nav = array();
	$q = $db->query("SELECT n1.*,n2.title t FROM `{$tablepre}nav` n1 
	                 LEFT JOIN `{$tablepre}nav` n2 
	                 ON n1.pid=n2.nid 
	                 $search_sql 
	                 ORDER BY view 
	                 LIMIT $from_record,$list_num");
	while($rt = $db->fetch_array($q))
	{
		$style_array=explode("|",$rt['style']);
		$style_array[1] && $rt['title']="<b>".$rt['title']."</b>";
		$style_array[2] && $rt['title']="<i>".$rt['title']."</i>";
		$style_array[3] && $rt['title']="<u>".$rt['title']."</u>";
		$style_array[0] && $rt['title']="<font color=\"$style_array[0]\">".$rt['title']."</font>";
		$rt['pos'] =  $nav_array[$rt['pos']];
		$rt['win_type'] = $rt['target']==0?'本窗口':'新窗口';
		$rt['link'] = strtolower($rt['link']);
        if(is_inner_url($rt['link']) && strstr($rt['link'],'.php'))
        {
        	$rt['link'] .= strstr($rt['link'],'.php?')? "&supid=$page_member_id":"?supid=$page_member_id";
        	$rt['link'] = $mm_subdomain==1?$db_mem_cache->op(0,'Id2Domain',$page_member_id).$rt['link']:$rt['link']="union/$rt[link]";
        }
		$nav_rt[] = $rt;
	}
	$page_list = $rowset->link("sadmin.php?module=$module&action=$action&pos=$pos&page=");
	
	//构造栏目类型的下拉菜单
	$tpl_pos_menu=drop_menu($nav_array,'menu');
	
	include template('sadmin_navigation');
}
else if($action=='add' || $action=='edit')
{
	$colors = array(
	    'skyblue','royalblue','blue','darkblue','orange','orangered','crimson','red','firebrick','darkred','green',
	    'limegreen','seagreen','teal','deeppink','tomato','coral','purple','indigo','burlywood','sandybrown','sienna','chocolate','silver'
	);
	$nav_system = nav_system();
	foreach ($nav_system as $key=>$val) $sys_arr[] = $val[2] ? $val[2] :  $val[0]  ;    
	$menu_sys = drop_menu($sys_arr,'add_nav',$title,'onchange="add_navi(this.value);"');
	
	if($action=='edit')
	{
		$uid=(int)$uid;
		$nav_rt=$db->get_one("SELECT * FROM `{$tablepre}nav` WHERE nid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
	}
	
	if ($_POST && (int)$step==1)
	{
		$view = (int)$view;
		$title = dhtmlchars($title);
		$style = $color."|".$b."|".$i."|".$u;
		$link = dhtmlchars($link);
		$alt = dhtmlchars($alt);
		$pos = $_POST['pos'];
		
		if ($_FILES['nav_img']['name']!='')
		{
			$nav_img && file_unlink('union/'.$nav_img,'bucket');
			require_once 'include/upfile.class.php';
			$rowset  = new upfile('gif,jpg,png,bmp','union/images/banner/');
			$nav_img = $rowset->upload('nav_img');
		    file_unlink('union/'.$nav_rt['nav_img'],'bucket');
		}
		else $nav_img=$nav_rt['nav_img'];
		
		if ($action=='add')
		{
			$db->query("INSERT INTO `{$tablepre}nav` (title,style,link,alt,target,pos,view,nav_img,pid,supplier_id) 
			            VALUES('$title','$style','$link','$alt','$target','$pos','$view','$nav_img','$pid','$page_member_id')");
		}
		else if ($action=='edit'&&is_numeric($uid))
		{
			$db->query("UPDATE `{$tablepre}nav` SET 
			            title='$title',
			            style='$style',
			            link='$link',
			            alt='$alt',
			            target='$target',
			            pos='$pos',
			            view='$view',
			            nav_img='$nav_img',
			            pid='$pid' 
			            WHERE nid='$uid' AND `supplier_id`='$page_member_id'");
		}
		$db->free_result();
		$cache->delete('nav',$page_member_id);
		$cache->delete('nav2',$page_member_id);
		move_page(base64_decode($p_url));
	}
	if ($action=='edit')
	{
		@extract($nav_rt,EXTR_OVERWRITE);
		$style_array  = explode("|",$style);
		$style_array[1] && $b_check = 'checked';
		$style_array[2] && $i_check = 'checked';
		$style_array[3] && $u_check = 'checked';
		$target==1 ? $blank_check = 'checked' : $self_check = 'checked';

	}
	else $self_check = 'checked';
	
	switch ($pos)
	{
		case 'head':
		    $head_check = 'checked';
		    break; 
		case 'middle':
		    $middle_check = 'checked';
		    break; 
		case 'help':
		    $help_check = 'checked';
		    break;
		default:
		    $foot_check = 'checked';
		    break; 
	}
	foreach($colors as $c)
	{
		$ifselect=$c==$style_array[0] ? 'selected' : '';
		$color_select.="<option value=\"$c\" style=\"background-color:$c;color:$c\" $ifselect></option>";
	}
	
	$pid=(int)$pid;
	$arrMenu=array();
	$arrMenu[0]='--';
	$strSql="SELECT nid,title FROM `{$tablepre}nav` WHERE pid='0' AND nid<>'$nid' AND `supplier_id`='$page_member_id'";
	if($action=='edit') $strSql.=" AND pos='$pos'";
	$q=$db->query($strSql);
	while($rtl=$db->fetch_array($q))
	{
		$nid=$rtl['nid'];
		$title1=$rtl['title'];
		$arrMenu[$nid]=$title1;
	}
	$p_menu=drop_menu($arrMenu,'pid',$pid);
	include template('sadmin_navigation_add');
	exit;
}
else if($action=='del')
{
	do
	{
		$uid=(int)$uid;
		if($uid<=0) break;
		$rt_nav = $db->get_one("SELECT nav_img,nid FROM `{$tablepre}nav` WHERE  nid='$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
		if(!$rt_nav) continue;
        $rt_nav['nav_img'] && file_unlink('union/'.$rt_nav['nav_img'],'bucket');
	    $db->query("DELETE FROM `{$tablepre}nav` WHERE nid='$rt_nav[nid]'");
	    $db->free_result();
	    $cache->delete('nav',$page_member_id);
	    $cache->delete('nav2',$page_member_id);
	}while(0);
}
else if($action=='all_delete')
{
	do
	{
		if(!is_array($uid_check) || !$uid_check) break;
		foreach ($uid_check as $key=>$val)
		{
			$val=(int)$val;
			if($val<=0) unset($uid_check[$key]);
		}
		if(!$uid_check) break;
		$uid_check=array_unique($uid_check);
		$str_uid=implode(',',$uid_check);
		$uids=array();
		$q=$db->query("SELECT nav_img,nid FROM `{$tablepre}nav` WHERE nid IN ($str_uid) AND supplier_id='$page_member_id'");
		while($rtl=$db->fetch_array($q))
		{
			$rtl['nav_img'] && file_unlink('union/'.$rtl['nav_img'],'bucket');
			$uids[]=$rtl['nid'];
		}
		if(!$uids) break;
		$str_uid=implode(',',$uids);
		$db->query("DELETE FROM `{$tablepre}nav` WHERE nid IN ($str_uid)");
		$db->free_result();
		$cache->delete('nav',$page_member_id);
		$cache->delete('nav2',$page_member_id);
	}while(0);
	
	show_msg('删除成功',"sadmin.php?module=$module&action=list");
}

function nav_system()
{
	global $tablepre,$db,$page_member_id;
	$nav_system = array(
	    array('--',''),
	    array('购物车','cart.php?action=list'),
	    array('最新商品','goods.php?action=new'),
	    array('推荐商品','goods.php?action=best'),
	    array('热门商品','goods.php?action=hot'),
	    array('促销商品','goods.php?action=sales'),
	    array('团购活动','group.php?action=list'),
	    array('拍卖活动','auction.php?action=list'),
	    array('商城导航','sitemap.php')
	);

	/**页面**/
	$nav_system[] = array('--','--');
	$q = $db->query("SELECT uid,page_subject,page_name FROM `{$tablepre}page_table` WHERE `supplier_id`='$page_member_id' ORDER BY uid DESC");
	while($rt = $db->fetch_array($q)) $nav_system[] = array($rt['page_subject'],"page.php?action=$rt[page_name]");
	
	return $nav_system;
}

function is_inner_url($url)
{
	$url=strtolower($url);
	return substr($url,0,5)!='http:';
}