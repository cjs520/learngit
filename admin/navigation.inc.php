<?php

/**
 * MVM_MALL 网上商店系统  自定义导航管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: navigation.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
$nav_array = array(
    'head' => '头部导航',
    'middle' => '中部导航',
    'foot' => '底部导航',
    'help' => '帮助中心',
    'news' => '资讯导航'
);

if($action=='list')
{
	require_once MVMMALL_ROOT.'include/pager.class.php';
	$search_sql = " WHERE n1.supplier_id='0' ";
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
		case 'news':  
		    $search_sql .= " AND n1.pos= 'news'";
		    break;
		default:
		    $pos='';
	}
	$total_count = $db->counter("`{$tablepre}nav` n1",$search_sql);
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$nav = array();
	$rs	= $db->query("SELECT n1.*,n2.title t FROM `{$tablepre}nav` n1 
	                  LEFT JOIN `{$tablepre}nav` n2 
	                  ON n1.pid=n2.nid 
	                  $search_sql 
	                  ORDER BY view 
	                  LIMIT $from_record,$list_num");
    while($rt = $db->fetch_array($rs))
	{
		$style_array=explode("|",$rt['style']);
		$style_array[1] && $rt['title']="<b>".$rt['title']."</b>";
		$style_array[2] && $rt['title']="<i>".$rt['title']."</i>";
		$style_array[3] && $rt['title']="<u>".$rt['title']."</u>";
		$style_array[0] && $rt['title']="<font color=\"$style_array[0]\">".$rt['title']."</font>";
		$rt['pos'] =  $nav_array[$rt['pos']];
		$rt['edit'] = "admincp.php?module=$module&action=edit&uid=$rt[nid]&page=$page";
		$rt['del'] = "admincp.php?module=$module&action=del&uid=$rt[nid]&page=$page";
		$rt['win_type'] = $rt['target']==0?'本窗口':'新窗口';
		$nav_rt[] = $rt;
	}
	$db->free_result();
	$page_list = $rowset->link("admincp.php?module=$module&action=$action&pos=$pos&page=");
	
	$arr_menu=array(
	    'head'=>$lang['head_navigation'],
	    'middle'=>$lang['central_navigation'],
	    'foot'=>$lang['bottom_navigation'],
	    'help'=>$lang['help']
	);
	$tpl_pos_menu=drop_menu($arr_menu,'menu');
	
	require_once template('navigation');
	footer();
}
else if ($action=='add' || $action=='edit')
{
	$uid = (int)$uid;
	$colors = array('skyblue','royalblue','blue','darkblue','orange','orangered','crimson','red','firebrick','darkred','green',
	                'limegreen','seagreen','teal','deeppink','tomato','coral','purple','indigo','burlywood','sandybrown','sienna','chocolate','silver');
	$nav_system = nav_system();
	foreach ($nav_system as $key=>$val) $sys_arr[] = $val[2] ? $val[2] :  $val[0]  ;
	$menu_sys = drop_menu($sys_arr,'add_nav',$title,'onchange="add_navi(this.value);"');
	
	if ($setp==1 && $_POST)
	{
		$view = (int)$view;
		$title = dhtmlchars($title);
		$style = $color.'|'.$b.'|'.$i.'|'.$u;
		$link = dhtmlchars($link);
		$alt = dhtmlchars($alt);
		$pos = dhtmlchars($_POST['pos']);
		
		if ($_FILES['nav_img']['name']!='')
		{
			$nav_img && file_unlink(str_replace(IMG_URL,'', $nav_img),'buctket');
			require_once 'include/upfile.class.php';
			$rowset  = new upfile('gif,jpg,png,bmp','images/banner/');
			$nav_img = $rowset->upload('nav_img');
		}
		
		if ($action=='add')
		{
			$db->query("INSERT INTO `{$tablepre}nav` (title,style,link,alt,target,pos,view,nav_img,pid) 
			            VALUES('$title','$style','$link','$alt','$target','$pos','$view','$nav_img','$pid')");
			$db->free_result();
			admin_log("添加导航：$title");
		}
		else if ($action=='edit' && $uid > 0)
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
			            WHERE nid='$uid' AND supplier_id='0'");
			admin_log("编辑导航：$title");
		    
		}
		$cache->delete('nav',0);
		$cache->delete('nav2',0);
		move_page(base64_decode($p_url));
	}
	
	if ($action=='edit' && $uid > 0)
	{
		$nav_rt = $db->get_one("SELECT * FROM `{$tablepre}nav` WHERE nid='$uid' AND supplier_id='0' LIMIT 1");
		@extract($nav_rt,EXTR_OVERWRITE);
		$style_array = explode("|",$style);
		$style_array[1] && $b_check = 'checked';
		$style_array[2] && $i_check = 'checked';
		$style_array[3] && $u_check = 'checked';
		$target==1 ? $blank_check = 'checked' : $self_check = 'checked';
	}
	else $self_check = 'checked';
	
	if(!in_array($pos,array('head','middle','help','news','foot'))) $pos = 'foot';
	$pos_val = $pos;
	$pos = $pos.'_check';
	$$pos = 'checked';
	
	foreach($colors as $c)
	{
		$ifselect=$c==$style_array[0] ? 'selected' : '';
		$color_select.="<option value=\"$c\" style=\"background-color:$c;color:$c\" $ifselect></option>";
	}
	
	$pid = (int)$pid;
	$arrMenu = array();
	$arrMenu[0] = '--';
	$strSql = "SELECT nid,title FROM `{$tablepre}nav` WHERE pid='0' AND nid<>'$nid' AND supplier_id='0'";
	if($action=='edit') $strSql .= " AND pos='$pos_val'";
	$q=$db->query($strSql);
	while($rtl=$db->fetch_array($q))
	{
		$nid = $rtl['nid'];
		$title1 = $rtl['title'];
		$arrMenu[$nid] = $title1;
	}
	$p_menu = drop_menu($arrMenu,'pid',$pid);
	$nav_img=IMG_URL.$nav_img;
	require_once template('navigation_add');
	exit;
}
else if($action=='del')
{
	$uid = (int)$uid;
	if($uid <= 0) exit;
    $rt_nav = $db->get_one("SELECT nav_img,nid,title FROM `{$tablepre}nav` WHERE  nid='$uid' AND supplier_id='0' LIMIT 1");
    if($rt_nav)
    {
        $rt_nav['nav_img'] && file_unlink($rt_nav['nav_img'],'buctket');
        $db->query("DELETE FROM `{$tablepre}nav` WHERE nid='$uid'");
        $db->free_result();
        admin_log("删除导航：$rt_nav[title]");
        $cache->delete('nav',0);
        $cache->delete('nav2',0);
    }
    
	exit;
}
else if($action=='all_delete')
{
	!is_array($uid_check) && show_msg('pass_worng');
	foreach ($uid_check as $val)
	{
		$val = (int)$val;
		if($val <= 0) continue;
		$rt_nav = $db->get_one("SELECT nav_img,nid,title FROM `{$tablepre}nav` WHERE  nid='$val' AND supplier_id='0' LIMIT 1");
        if(!$rt_nav) continue;
        
		$rt_nav['nav_img'] && file_unlink($rt_nav['nav_img'],'buctket');
        $db->query("DELETE FROM `{$tablepre}nav` WHERE nid='$val'");
        $db->free_result();
        admin_log("删除导航：$rt_nav[title]");
	}
	$cache->delete('nav',0);
	$cache->delete('nav2',0);
	show_msg('success',"admincp.php?module=$module&action=list");
}
else if($action=='ajax')
{
	$uid=(int)$uid;
	$uid<=0 && exit;
	$db->query("UPDATE `{$tablepre}nav` SET $field='$v' WHERE nid='$uid' AND supplier_id='0'");
}
else show_msg('pass_worng');


//系统栏目
function nav_system()
{
	global $tablepre,$db,$cache;
	$nav_system = array(
	    array('--',''),
	    array('购物车','cart.php?action=list'),    //购物车
	    array('最新商品','goods.php?action=new'),    //最新商品
	    array('团购活动','group.php?action=list'),    //团购活动
	    array('拍卖活动','auction.php?action=list'),    //拍卖活动
	    array('品牌展示','brand.php?action=list'),    //品牌展示
	);
	
	/**页面**/
	$nav_system[] = array('--','--');
	$result = $db->query("SELECT uid,page_subject,page_name FROM `{$tablepre}page_table` WHERE supplier_id='0'");
	while($rt = $db->fetch_array($result)) $nav_system[] = array($rt['page_subject'],"page.php?action=$rt[page_name]");
	
	return $nav_system;
}