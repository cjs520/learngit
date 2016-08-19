<?php

/**
 * MVM_MALL 网上商店系统  订单管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-15 $
 * $Id: rcm_ad.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

if ($action=='list')
{
    require_once 'include/pager.class.php';
    
    $search_sql="WHERE uid>0 ";
    $order_sql=" ORDER BY uid DESC ";
    
    if((int)$search==1)
    {
    	if(!isset($adtype)) $adtype=-1;
    	else $adtype=(int)$adtype;
    	if($adtype!=-1) $search_sql.=" AND ad_type='$adtype'";
    	
    	$post_module=$_POST?$_POST['module']:$post_module;
    	if($post_module!='0')
    	{
    		$search_sql.=" AND module='$post_module'";
    		if($other_param!='0') $search_sql.=" AND other_param='$other_param'";
    		$search_sql.=" AND pos='$pos'";
    		$order_sql='';
    	}
    }
    if($act=='show') $search_sql.=" AND m_uid>0 AND expire>='$m_now_time'";
    if($act=='not_show') $search_sql.=" AND (m_uid<=0 OR expire<'$m_now_time')";
    if($act=='expire') $search_sql.=" AND expire<='$m_now_time'+3*24*3600 AND m_uid>'0'";
    
    $total_count = $db->counter("{$tablepre}ad_table", $search_sql);
    $page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT * FROM `{$tablepre}ad_table` 
                     $search_sql 
                     $order_sql 
                     LIMIT $from_record, $list_num");
    
    $str_info="post_module=$post_module&other_param=$other_param&ad_option=$ad_option&adtype=$adtype&pos=$pos&page=$page&search=$search";
    $str_info=base64_encode($str_info);
    
    while ($rtl=$db->fetch_array($q))
    {
    	if($rtl['info'] || $rtl['cur_info'])
    	{
    		$arr_tmp=unserialize($rtl['cur_info']);
    		if(!is_array($arr_tmp)) $arr_tmp=unserialize($rtl['info']);
    		if(!is_array($arr_tmp)) unset($arr_tmp);
    	}
    	
    	$rtl['title']='无标题';
    	$rtl['pic']='无图';
    	if($arr_tmp)
    	{
    		if($rtl['ad_type']==0) {$rtl['title']=$arr_tmp['goods_name'];$rtl['pic']=$arr_tmp['goods_pic'];}
    		else if($rtl['ad_type']==1) {$rtl['title']=$arr_tmp['shop_name'];$rtl['pic']=$arr_tmp['shop_logo'];}
    		else {$rtl['title']=$arr_tmp['title'];$rtl['pic']=$arr_tmp['pic'];}
    		$rtl['pic']=IMG_URL.$rtl['pic'];
    		if(!$rtl['pic']) $rtl['pic']='无图';
    		else $rtl['pic']="<img src='$rtl[pic]' width='150' />"; 
    	}
    	unset($arr_tmp);    //用完及时清理掉
    	
    	$rtl['expire']=$rtl['m_id']?date('Y-m-d',$rtl['expire']):'未被申请';
    	$rtl['ad_type']=$ad_config['ad_type'][$rtl['ad_type']];
    	
    	$ad_list[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=rcm_ad&action=list&act=$act&adtype=$adtype&post_module=$post_module&ad_option=$ad_option&other_param=$other_param&pos=$pos&search=$search&page=");
    
    //联动数据
	$arr_tmp=array();
	$arr_tmp=$ad_config;
	unset($arr_tmp['ad_type']);
	$json_data=json_encode($arr_tmp);
	
    require_once template('rcm_ad');
    footer();
} 
else if($action=='add' || $action=='edit')
{
	if($_POST && (int)$step==1) //数据提交
	{
		$ad_type=(int)$ad_type;
		$expire=(int)$expire<=0?30:(int)$expire;
		$uid=(int)$uid;
		
		if($action=='edit')
		{
			$tmp_rtl=$db->get_one("SELECT info,ad_type FROM `{$tablepre}ad_table` WHERE uid='$uid' LIMIT 1");
			$tmp_info=unserialize($tmp_rtl['info']);
		}
		
		if($action=='edit' && 
		   ($_FILES['goods_img']['name']!='' || $_FILES['shop_logo']['name']!='' || $_FILES['ad_pic']['name']!='') && 
		   $uid>=0 && $tmp_rtl && is_array($tmp_info))    //如果是修改，并且有重新上传图片，则判断是否删除原来的图片
        {
            
            if($tmp_rtl['ad_type']==0) $pic=$tmp_info['goods_pic'];
            else if($tmp_rtl['ad_type']==1) $pic=$tmp_info['shop_logo'];
            else if($tmp_rtl['ad_type']==2) $pic=$tmp_info['pic'];

            if($pic && strstr($pic,'upload/ad_pic/')) file_unlink($pic,'bucket'); 
        }
		
		if($ad_type==0)    //处理商品广告
		{
			$goods_id=(int)$goods_id;
			$goods_name=trim($goods_name);
			$goods_type=(int)$goods_type;
			$goods_table=goods_table($goods_type);
			$detail_table=goods_detail_table($goods_type);
			if($goods_name=='') show_msg('请把商品名称填写完整');
			//预先的工作
			if($goods_id<=0) show_msg('请正确选择商品ID 或 自己填写一个正确的ID');
			$goods_rtl=$db->get_one("SELECT goods_sale_price,supplier_id FROM `$goods_table` WHERE uid='$goods_id' LIMIT 1");
			!$goods_rtl && show_msg('请正确选择商品ID 或 自己填写一个正确的ID!!!');
			$goods_price=currency($goods_rtl['goods_sale_price']);
			$supplier_id=$goods_rtl['supplier_id'];
			if($_FILES['goods_img']['name']!='')    //商品图片
			{
			    require_once 'include/upfile.class.php';
			    $f = new upfile('jpg,jpeg,gif',"upload/ad_pic/");
                $ad_pic = $f->upload('goods_img');
                
			}
			else $ad_pic=$now_goods_img;
			$goods_info=array(
			    'goods_id'=>$goods_id,
			    'goods_type'=>$goods_type,
			    'goods_name'=>stripslashes($goods_name),
			    'goods_desc'=>stripslashes($goods_desc),
			    'goods_price'=>$goods_price,
			    'goods_pic'=>str_replace(IMG_URL, '', $ad_pic),
			    'supplier_id'=>$supplier_id,
			    'shop_name'=>stripslashes($goods_shop_name),
			    'title_color'=>$color
			);
			
			$row=array(
			    'ad_type'=>$ad_type,
			    'pos'=>$pos,
			    'ad_order'=>(int)$order,
			    'module'=>$_POST['module'],
			    'other_param'=>$other_param,
			    'info'=>daddslashes(serialize($goods_info)),
			    'price'=>(int)$price,
			    'wh'=>dhtmlchars($wh),
			    'tip'=>dhtmlchars($tip)
			);
			
			if($action=='add')
			{
			    $db->insert("`{$tablepre}ad_table`",$row);
			    $db->free_result();
			    admin_log("添加商品类型广告：$row[module] $row[pos] $row[other_param]");
			}
			else
			{
				$uid=(int)$uid;
				$db->update("`{$tablepre}ad_table`",$row," uid='$uid' ");
				$db->free_result();
			    admin_log("编辑商品类型广告：$row[module] $row[pos] $row[other_param]");
			}
		}
		else if($ad_type==1)    //处理商家广告
		{
			$shop_name=trim($shop_name);
			if($shop_name=='') show_msg('请把商家名称填写完整');
			if($shop_id<=0) show_msg('请正确选择商家ID 或 自己填写一个正确的ID');
			$shop_rtl=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE uid='$shop_id' LIMIT 1");
			!$shop_rtl && show_msg('检索不到指定商家');
			if($_FILES['shop_logo']['name']!='')    //商家logo
			{
			    require_once MVMMALL_ROOT.'include/upfile.class.php';
			    $f = new upfile('jpg,jpeg,gif',"upload/ad_pic/");
                $ad_pic = $f->upload('shop_logo');
			}
			else $ad_pic=$now_shop_logo;
			
			$shop_info=array(
			    'shop_name'=>stripslashes($shop_name),
			    'shop_logo'=>$ad_pic,
			    'member_uid'=>$shop_id,
			    'shop_desc'=>stripslashes($shop_desc),
			    'star'=>(int)$star,
			    'title_color'=>$color
			);
			
			$row=array(
			    'ad_type'=>$ad_type,
			    'pos'=>$pos,
			    'ad_order'=>(int)$order,
			    'module'=>$_POST['module'],
			    'other_param'=>$other_param,
			    'info'=>daddslashes(serialize($shop_info)),
			    'price'=>(int)$price,
			    'wh'=>dhtmlchars($wh),
			    'tip'=>dhtmlchars($tip)
			);
			
			if($action=='add')
			{
			    $db->insert("`{$tablepre}ad_table`",$row);
			    $db->free_result();
			    admin_log("添加商铺类型广告：$row[module] $row[pos] $row[other_param]");
			}
			else
			{
				$uid=(int)$uid;
				$db->update("`{$tablepre}ad_table`",$row," uid='$uid' ");
				$db->free_result();
			    admin_log("编辑商铺类型广告：$row[module] $row[pos] $row[other_param]");
			}
			
		}
		else if($ad_type==2)    //处理其它类型的广告
		{
			$ad_title=trim($ad_title);
			$ad_url=trim($ad_url);
			if($ad_title=='' || $ad_url=='') show_msg('请把广告标题和广告链接地址填写完整');
			if($_FILES['ad_pic']['name']!='')    //广告图片
			{
			    require_once MVMMALL_ROOT.'include/upfile.class.php';
			    $f = new upfile('jpg,jpeg,gif',"upload/ad_pic/");
                $ad_pic = $f->upload('ad_pic');
			}
			else $ad_pic=$now_ad_pic;
			$ad_info=array(
			    'title'=>stripcslashes($ad_title),
			    'show_cat'=>$show_cat,
			    'pic'=>$ad_pic,
			    'url'=>$ad_url,
			    'desc'=>stripslashes($desc),
			    'title_color'=>$color
			);
			
			$row=array(
			    'ad_type'=>$ad_type,
			    'pos'=>$pos,
			    'ad_order'=>(int)$order,
			    'module'=>$_POST['module'],
			    'other_param'=>$other_param,
			    'info'=>daddslashes(serialize($ad_info)),
			    'price'=>(int)$price,
			    'wh'=>dhtmlchars($wh),
			    'tip'=>dhtmlchars($tip)
			);
			
			if($action=='add')
			{
			    $db->insert("`{$tablepre}ad_table`",$row);
			    $db->free_result();
			    admin_log("添加其它类型广告：$row[module] $row[pos] $row[other_param]");
			}
			else
			{
				$uid=(int)$uid;
				$db->update("`{$tablepre}ad_table`",$row," uid='$uid' ");
				$db->free_result();
			    admin_log("编辑其它类型广告：$row[module] $row[pos] $row[other_param]");
			}
		}
		else show_msg('类型选择出错');
		$page_info=base64_decode($page_info);
		show_msg('广告添加成功',"admincp.php?module=rcm_ad&action=list&$page_info");
	}
	///////////////end 提交结束//////////////////////
	
	
	//联动数据
	$arr_tmp=array();
	$arr_tmp=$ad_config;
	unset($arr_tmp['ad_type']);
	$json_data=json_encode($arr_tmp);
	
	if($action=='add')    //添加的数据预处理
	{
	    //接受个部参数
	    $ad_type=(int)$ad_type;
	    $goods_id=(int)$goods_id;
	    $shop_id=(int)$shop_id;
	    $goods['user_img']=$shop['user_img']=$ad['user_img']='images/noimages/noproduct.jpg';
	    if($ad_type==0 && (int)$goods_id!=0)    //表示是商品类型广告
	    {
	    	$rtl=$db->get_one("SELECT gt.uid,gt.goods_name,gt.goods_file1,gt.supplier_id FROM `{$tablepre}goods_table` gt WHERE gt.uid='$goods_id' LIMIT 1");
	    	if($rtl)
	    	{
	    	    $shop=$db->get_one("SELECT m_uid,shop_name,shop_intro FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
	    	    if($shop) $rtl=array_merge($shop,$rtl);
	    		$goods=array();
	    		$goods['goods_id']=$goods_id;
	    		$goods['goods_name']=$rtl['goods_name'];
	    		//$goods['desc']=$rtl['goods_advance'];
	    		$goods['shop_name']=$rtl['shop_name'];
                $goods['img']=$rtl['supplier_id']==0?$rtl['goods_file1']:'union/'.$rtl['goods_file1'];
                //if(!$rtl['goods_file1'] || !file_exists($goods['img'])) $goods['img']='images/noimages/noproduct.jpg';
	    	}
	    }
	    else if($ad_type==1 && $shop_id!=0)    //表示是商家类型广告
	    {
	    	$rtl=$db->get_one("SELECT m_uid,shop_name,up_logo,isSupplier,shop_intro 
	    	                   FROM `{$tablepre}member_shop`  
	    	                   WHERE m_uid='$shop_id' 
	    	                   LIMIT 1");
	    	if($rtl)
	    	{
	    		if($rtl['isSupplier']<2) show_msg('这不是正式商家，无法进行发布');
	    		$shop=array();
	    		$shop['shop_id']=$shop_id;
	    		$shop['shop_name']=$rtl['shop_name'];
	    		$shop['shop_desc']=$rtl['shop_intro'];
	    		//$shop['star']=$rtl['star'];
                
                $shop['logo']=$rtl['up_logo']?'union/'.$rtl['up_logo']:'';
                $shop['logo']=IMG_URL.$shop['logo'];
	    	}
	    }
	}
	else if($action=='edit' && (int)$uid>0)    //编辑的数据预处理
	{
		$rtl=$db->get_one("SELECT * FROM `{$tablepre}ad_table` WHERE uid='$uid' LIMIT 1");
		if(!$rtl) show_msg('找不到编辑的记录');
		$module=$rtl['module'];
		$other_param=$rtl['other_param'];
		$pos=$rtl['pos'];
		
		$info=unserialize($rtl['info']);
		!is_array($info) && $info=array();
		$cur_info=unserialize($rtl['cur_info']);
		!is_array($cur_info) && $cur_info=array();

		if($rtl['ad_type']==0)    //商品广告
		{
			$ad_type=0;
			$goods=array();
			if(is_array($info))
			{
				$goods['goods_id']=$info['goods_id'];
				$goods['goods_type']=(int)$info['goods_type'];
				$goods['goods_name']=$info['goods_name'];
				$goods['img']=IMG_URL.$info['goods_pic'];
				$goods['desc']=$info['goods_desc'];
				$goods['shop_name']=$info['shop_name'];
				$goods['ad_order']=(int)$rtl['ad_order'];
				$color=$goods['color']=$info['title_color'];
				$goods['user_img']=@fopen(IMG_URL.$cur_info['goods_pic'],'r')?IMG_URL.$cur_info['goods_pic']:'images/noimages/noproduct.jpg';
				$goods['user_apply']=$cur_info;
			}
		}
		else if($rtl['ad_type']==1)    //商家广告
		{
			$ad_type=1;
			$shop=array();
			if(is_array($info))
			{
				$shop['shop_id']=$info['member_uid'];
				$shop['shop_name']=$info['shop_name'];
				$shop['shop_desc']=$info['shop_desc'];
				$shop['logo']=IMG_URL.$info['shop_logo'];
				$shop['star']=$info['star'];
				$shop['ad_order']=(int)$rtl['ad_order'];
				$color=$shop['color']=$info['title_color'];
				$shop['user_img']=@fopen(IMG_URL.$cur_info['shop_logo'],'r')?$cur_info['shop_logo']:'images/noimages/noproduct.jpg';
				$shop['user_apply']=$cur_info;
			}
		}
		else if($rtl['ad_type']==2)    //其他广告
		{
			$ad_type=2;
			$ad=array();
			if(is_array($info))
			{
				$ad['title']=$info['title'];
				$ad['show_cat']=$info['show_cat'];
				$ad['pic']=$info['pic'];
				$ad['desc']=$info['desc'];
				$ad['url']=$info['url'];
				$ad['ad_order']=(int)$rtl['ad_order'];
				$color=$ad['color']=$info['title_color'];
				$ad['expire']=(int)$rtl['expire']<=$m_now_time?0:round(((int)$rtl['expire']-$m_now_time)/(3600*24),0);
			    
				$ad['user_img']=@fopen(IMG_URL.$cur_info['pic'],'r')?$cur_info['pic']:'images/noimages/noproduct.jpg';
			    $ad['user_apply']=$cur_info;
			}
		}
		else show_msg('记录类型不正确');
	}
	$ad['pic']=IMG_URL.$ad['pic'];
	$ad['user_img']=IMG_URL.$ad['user_img'];;
	require_once template('rcm_ad_add');
    footer();
}
else if($action=='del')
{
	if(is_array($uid))
	{
		if(sizeof($uid)<=0) show_msg('没有选择可以删除的记录');
		$str_id=implode(',',$uid);
		$q=$db->query("SELECT ad_type,info,module,pos,other_param FROM `{$tablepre}ad_table` WHERE uid IN ($str_id)");
		while($rtl=$db->fetch_array($q))
		{
			$info=unserialize($rtl['info']);
		    if(is_array($info))
		    {
		        if($rtl['ad_type']==0) $pic=$info['goods_pic'];
		        else if($rtl['ad_type']==1) $pic=$info['shop_logo'];
		        else if($rtl['ad_type']==2) $pic=$info['pic'];
		    }
		    if($pic && strstr($pic,'upload/ad_pic/')) file_unlink($pic,'bucket');
		    admin_log("删除广告：$rtl[ad_type] $rtl[module] $rtl[pos] $rtl[other_param]");
		}
		$db->free_result();
		$db->query("DELETE FROM `{$tablepre}ad_table` WHERE uid IN ($str_id)");
		$db->free_result();
		show_msg('记录删除成功','admincp.php?module=rcm_ad&action=list');
	}
	else 
	{
		$uid=(int)$uid;
		if($uid<=0) show_msg('删除的ID出错，请重新选择');
		$rtl=$db->get_one("SELECT ad_type,info,module,pos,other_param FROM `{$tablepre}ad_table` WHERE uid='$uid'");
		if(!$rtl) show_msg('查找不到可删除的记录');
		$info=unserialize($rtl['info']);
		if(is_array($info))
		{
		    if($rtl['ad_type']==0) $pic=$info['goods_pic'];
		    else if($rtl['ad_type']==1) $pic=$info['shop_logo'];
		    else if($rtl['ad_type']==2) $pic=$info['pic'];
		}
		if($pic && strstr($pic,'upload/ad_pic/')) file_unlink($pic,'bucket');
		admin_log("删除广告：$rtl[ad_type] $rtl[module] $rtl[pos] $rtl[other_param]");
		
		$db->query("DELETE FROM `{$tablepre}ad_table` WHERE uid='$uid'");
		$db->free_result();
		show_msg('记录删除成功',"admincp.php?module=rcm_ad&action=list&page=$page");
	}
}
else if($action=='clear_apply')
{
	$uid=(int)$uid;
	if($uid<=0) exit;
	
	$ad=$db->get_one("SELECT * FROM `{$tablepre}ad_table` WHERE uid='$uid' LIMIT 1");
	if(!$ad || $ad['cur_info']=='') exit;
	$ad_info=unserialize($ad['cur_info']);
	
	switch ($ad['ad_type'])
	{
		case 0:
		    file_unlink($ad_info['goods_pic'],'bucket');
		    break;
		case 1:
		    file_unlink($ad_info['shop_logo'],'bucket');
		    break;
		case 2:
		    file_unlink($ad_info['pic'],'bucket');
		    break;
	}
	
	admin_log("清空广告申请记录：$ad[ad_type] $ad[module] $ad[pos] $ad[other_param]");
    $db->query("UPDATE `{$tablepre}ad_table` SET 
                cur_info='',
                expire='0',
                m_id='',
                m_uid='0' 
                WHERE uid='$uid'");	
    $db->free_result();
    exit;
}
else if($action=='apply_list')
{
	require_once 'include/pager.class.php';
	
	$search_sql=" WHERE TRUE";
	if($act=='check') $search_sql.=" AND approval_date>10";
	else $search_sql.=" AND approval_date=0";
	
	$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}ad_apply` $search_sql");
	$total_count = $rtl['cnt'];
    $page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT aa.*, at.ad_type,at.module,at.pos,at.other_param,at.wh 
                     FROM `{$tablepre}ad_apply` aa 
	                 LEFT JOIN `{$tablepre}ad_table` at 
	                 ON aa.ad_uid=at.uid 
	                 $search_sql 
	                 ORDER BY aa.approval_date DESC 
	                 LIMIT $from_record,$list_num");
    
    while ($rtl=$db->fetch_array($q))
    {
    	if($rtl['info'])
    	{
    		$rtl['info']=$arr_tmp=unserialize($rtl['info']);
    		if(!is_array($arr_tmp)) unset($arr_tmp);
    	}
    	
    	if($arr_tmp)
    	{
    		if($rtl['ad_type']==0)
    		{
    			$rtl['title']=$arr_tmp['goods_name'];
    			$rtl['pic']=$arr_tmp['goods_pic'];
    	        $rtl['url']=GetBaseUrl('product',$arr_tmp['goods_id'],'action',$arr_tmp['supplier_id']);
    	        $rtl['shop_url']=GetBaseUrl('index','','',$arr_tmp['supplier_id']);
    		}
    		else if($rtl['ad_type']==1)
    		{
    			$rtl['title']=$arr_tmp['shop_name'];
    			$rtl['pic']=$arr_tmp['shop_logo'];
    			$rtl['shop_url']=GetBaseUrl('index','','',$arr_tmp['supplier_id']);
    	    }
    		else
    		{
    			$rtl['title']=$arr_tmp['title'];
    			$rtl['pic']=$arr_tmp['pic'];
    			$rtl['url']=$arr_tmp['url'];
    	    }
    	}
    	
    	if(!$rtl['pic'] || !@fopen(IMG_URL.$rtl['pic'],'r')) $rtl['pic']='images/noimages/noproduct.jpg';
    	$rtl['status']=$rtl['approval_date']>0?'<span class="orange">已审核</span>':'';
    	$rtl['pic']=IMG_URL.$rtl['pic'];
    	$ad_list[]=$rtl;
    }
    $db->free_result();

    $page_list = $rowset->link("admincp.php?module=rcm_ad&action=$action&act=$act&page=");
    
    require_once template('rcm_ad_apply');
    footer();
}
else if($action=='view')
{
    $uid=(int)$uid;
    $ad_apply=$db->get_one("SELECT * FROM `{$tablepre}ad_apply` WHERE uid='$uid' LIMIT 1");
    if(!$ad_apply) exit('检索不到指定的申请记录');
    $ad=$db->get_one("SELECT * FROM `{$tablepre}ad_table` WHERE uid='$ad_apply[ad_uid]' LIMIT 1");
    if(!$ad) exit('检索不到相关的广告记录');
    $member=$db->get_one("SELECT m_id,shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$ad_apply[m_uid]' LIMIT 1");
    if(!$member) exit('检索不到申请人记录');
    $ad_apply['reg_time']=date('Y-m-d H:i:s',$ad_apply['reg_time']);
    $ad_apply['status']=$ad_apply['approval_date']>0?'通过审核':'未通过审核';
    $info=unserialize($ad_apply['info']);
    $info['goods_pic']=IMG_URL.$info['goods_pic'];
    $info['shop_logo']=IMG_URL.$info['shop_logo'];
    $info['pic']=IMG_URL.$info['pic'];
    switch ($ad['ad_type'])
    {
        case 0:
            $info['url']=GetBaseUrl('product',$info['goods_id'],'action',$info['supplier_id']);
            break;
        case 1:
            $info['url']=GetBaseUrl('index','','',$info['supplier_id']);
            break;
        case 2:
            break;
    }
    
    require_once template('rcm_ad_apply_view');
    exit();
}
else if($action=='apply_del')
{
	$uid=(int)$uid;
	$back_reason=trim(dhtmlchars($back_reason));
	if(!$back_reason) $back_reason='无';
	
	$apply=$db->get_one("SELECT uid,info,m_id,m_uid,ad_uid,point_sess FROM `{$tablepre}ad_apply` WHERE uid='$uid' LIMIT 1");
	if(!$apply) exit('检索不到指定申请记录');
	$ad=$db->get_one("SELECT uid,ad_type,module,other_param FROM `{$tablepre}ad_table` WHERE uid='$apply[ad_uid]' LIMIT 1");
	if(!$ad) exit('检索不到指定广告记录');
	$point=$db->get_one("SELECT point_add FROM `{$tablepre}point_table` WHERE point_sess='$apply[point_sess]' LIMIT 1");
	if(!$point) exit('检索不到指定的积分记录，无法回退');
	admin_log("删除广告申请：$ad[ad_type] $ad[module] $ad[pos] $ad[other_param] 申请人：$apply[m_id]");
	
	$total_point=abs($point['point_add']);
	$db->query("UPDATE `{$tablepre}point_table` SET approval_date='-1' WHERE uid='$point[uid]'");
    add_score($apply['m_uid'],$total_point,'广告删除',"$ad[module]广告删除");
	
	$info=unserialize($apply['info']);
	if(isset($info['goods_pic'])) file_unlink($info['goods_pic'],'bucket');
	if(isset($info['shop_logo'])) file_unlink($info['shop_logo'],'bucket');
	if(isset($info['pic'])) file_unlink($info['pic'],'bucket');
	$db->query("DELETE FROM `{$tablepre}ad_apply` WHERE uid='$apply[uid]'");
	
	$msg="您申请的广告（流水号：$apply[point_sess]）已被删除，原因：$back_reason";
	$row=array(
	    'from_id'=>$m_check_id,
	    'to_id'=>$apply['m_id'],
	    'title'=>'广告申请删除',
	    'content'=>$msg,
	    'is_broadcast'=>0,
	    'reg_date'=>$m_now_time
	);
	$db->insert("`{$tablepre}sms`",$row);
	exit('退回成功，退回积分：'.$total_point.'分');
}
else if($action=='apply_check')
{
	$uid=(int)$uid;
	if($uid<=0) exit;
	$rtl=$db->get_one("SELECT ad_uid FROM `{$tablepre}ad_apply` WHERE uid='$uid' LIMIT 1");
	if(!$rtl) exit;
	$ad=$db->get_one("SELECT ad_type,module,pos,other_param FROM `{$tablepre}ad_table` WHERE uid='$rtl[ad_uid]' LIMIT 1");
    if($ad) admin_log("审核广告申请：$ad[ad_type] $ad[module] $ad[pos] $ad[other_param] 申请人：$rtl[m_id]");
    
	$db->query("UPDATE `{$tablepre}ad_apply` SET approval_date='$m_now_time' WHERE uid='$uid'");
	exit;
}
else if($action=='apply_back')
{
	$uid=(int)$uid;
	$back_reason=trim(dhtmlchars($back_reason));
	if(!$back_reason) $back_reason='无';
	
	$apply=$db->get_one("SELECT uid,info,m_id,m_uid,ad_uid,point_sess FROM `{$tablepre}ad_apply` WHERE uid='$uid' LIMIT 1");
	if(!$apply) exit('检索不到指定申请记录');
	$ad=$db->get_one("SELECT uid,ad_type,module,other_param FROM `{$tablepre}ad_table` WHERE uid='$apply[ad_uid]' LIMIT 1");
	if(!$ad) exit('检索不到指定广告记录');
	$point=$db->get_one("SELECT point_add FROM `{$tablepre}point_table` WHERE point_sess='$apply[point_sess]' LIMIT 1");
	if(!$point) exit('检索不到指定的积分记录，无法回退');
	admin_log("回退广告申请：$ad[ad_type] $ad[module] $ad[pos] $ad[other_param] 申请人：$apply[m_id]");
	
	$total_point=abs($point['point_add']);
	$db->query("UPDATE `{$tablepre}point_table` SET approval_date='-1' WHERE uid='$point[uid]'");
    add_score($apply['m_uid'],$total_point,'广告驳回',"$ad[module]广告驳回");
	
	$info=unserialize($apply['info']);
	if(isset($info['goods_pic'])) file_unlink($info['goods_pic'],'bucket');
	if(isset($info['shop_logo'])) file_unlink($info['shop_logo'],'bucket');
	if(isset($info['pic'])) file_unlink($info['pic'],'bucket');
	$db->query("DELETE FROM `{$tablepre}ad_apply` WHERE uid='$apply[uid]'");
	
	$msg="您申请的广告（流水号：$apply[point_sess]）已被退回，原因：$back_reason";
	$row=array(
	    'from_id'=>$m_check_id,
	    'to_id'=>$apply['m_id'],
	    'title'=>'广告申请退回',
	    'content'=>$msg,
	    'is_broadcast'=>0,
	    'reg_date'=>$m_now_time
	);
	$db->insert("`{$tablepre}sms`",$row);
	exit('退回成功，退回积分：'.$total_point.'分');
}
else if($action=='search_shop')
{
    $shop_name=trim($shop_name);
	$info=array();
	if(strlen($shop_name)<=0)
	{
		$info['error']='请输入要搜索的商铺关键字';
		echo json_encode($info);
		exit;
	}
	
	$info['shop_info']=array();
	$q=$db->query("SELECT m_uid,m_id,shop_name,shop_intro,up_logo FROM `{$tablepre}member_shop` WHERE shop_name LIKE '%$shop_name%' LIMIT 10");
	while($rtl=$db->fetch_array($q))
	{
        $rtl['shop_logo']='union/'.$rtl['up_logo'];
        unset($rtl['up_logo']);
		$info['shop_info'][$rtl['m_uid']]=$rtl;
	}
	
	if(sizeof($info['shop_info'])<=0)
	{
		unset($info['shop_info']);
		$info['error']='找不到相关商家';
	}
	echo json_encode($info);
}
else if($action=='search_goods')
{
	$goods_name=trim($goods_name);
	$goods_type=(int)$goods_type;
	$goods_table=goods_table($goods_type);
	$detail_table=goods_detail_table($goods_type);
	$info=array();
	if(strlen($goods_name)<=0)
	{
		$info['error']='请输入要搜索的商品关键字';
		echo json_encode($info);
		exit;
	}
	$info['goods_info']=array();
	$q=$db->query("SELECT uid,goods_name,goods_file1,supplier_id FROM `$goods_table`  
	               WHERE goods_name LIKE '%$goods_name%' 
	               LIMIT 10");
	while($rtl=$db->fetch_array($q))
	{
		//商品图片路径
		$rtl['photo']=ProcImgPath($rtl['goods_file1']);
		$g_detail=$db->get_one("SELECT goods_advance FROM `$detail_table` WHERE g_uid='$rtl[uid]' LIMIT 1");
		if($g_detail) $rtl=array_merge($rtl,$g_detail);
		$shop=$db->get_one("SELECT shop_name,m_id AS member_id FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
		if($shop) $rtl=array_merge($rtl,$shop);
        
		$info['goods_info'][$rtl['uid']]=$rtl;
	}
	$db->free_result();
	if(sizeof($info['goods_info'])<=0)
	{
		unset($info['goods_info']);
		$info['error']='找不到相关商品';
	}
	echo json_encode($info);
}