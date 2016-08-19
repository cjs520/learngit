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
 * $Id: rcm_ad.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if($action=='list')
{
    $q = $db->query("SELECT * FROM `{$tablepre}ad_table` WHERE m_uid='$page_member_id'");
    while ($rtl=$db->fetch_array($q))
    {
    	$arr_info=unserialize($rtl['cur_info']);
    	if($rtl['ad_type']==0) $rtl['title']=$arr_info['goods_name'];
    	else if($rtl['ad_type']==1) $rtl['title']=$arr_info['shop_name'];
    	else $rtl['title']=$arr_info['title'];
    	
    	if($rtl['ad_type']==0) $rtl['pic']=$arr_info['goods_pic'];
    	else if($rtl['ad_type']==1) $rtl['pic']=$arr_info['shop_logo'];
    	else $rtl['pic']=$arr_info['pic'];
    	$rtl['pic']=IMG_URL.$rtl['pic'];
    	if($rtl['ad_type']==0) $rtl['url']=GetBaseUrl('product',$arr_info['goods_id'],'action',$arr_info['supplier_id']);
    	else if($rtl['ad_type']==1) $rtl['url']=GetBaseUrl('index','','action',$arr_info['member_uid']);
    	else $rtl['url']=$arr_info['url'];
    	
    	$rtl['status']='展示中';
    	
    	$rtl['expire']=date('Y-m-d',$rtl['expire']);
    	$rtl['ad_type']=$ad_config['ad_type'][$rtl['ad_type']];
    	$rtl['price']=$rtl['price'];
    	$ad_list[]=$rtl;
    }
    $db->free_result();
    
    $q = $db->query("SELECT at.ad_type,at.module,at.pos,at.other_param,at.wh,
                            aa.* 
                     FROM `{$tablepre}ad_apply` aa 
                     LEFT JOIN `{$tablepre}ad_table` at 
                     ON aa.ad_uid=at.uid
                     WHERE aa.m_uid='$page_member_id'");
    while ($rtl=$db->fetch_array($q))
    {
    	$arr_info=unserialize($rtl['info']);
    	if($rtl['ad_type']==0) $rtl['title']=$arr_info['goods_name'];
    	else if($rtl['ad_type']==1) $rtl['title']=$arr_info['shop_name'];
    	else $rtl['title']=$arr_info['title'];
    	
    	if($rtl['ad_type']==0) $rtl['pic']=$arr_info['goods_pic'];
    	else if($rtl['ad_type']==1) $rtl['pic']=$arr_info['shop_logo'];
    	else $rtl['pic']=$arr_info['pic'];
    	$rtl['pic']=IMG_URL.$rtl['pic'];
    	if($rtl['ad_type']==0) $rtl['url']=GetBaseUrl('product',$arr_info['goods_id'],'action',$arr_info['supplier_id']);
    	else if($rtl['ad_type']==1) $rtl['url']=GetBaseUrl('index','','action',$arr_info['member_uid']);
    	else $rtl['url']=$arr_info['url'];
    	
    	$rtl['status']=$rtl['approval_date']>0?'已审核':'等待审核';
    	
    	$rtl['expire']='等待中，展示'.$rtl['days'].'天';
    	$rtl['ad_type']=$ad_config['ad_type'][$rtl['ad_type']];
    	$rtl['price']=$rtl['price'];
    	$ad_list[]=$rtl;
    }
    $db->free_result();
	
    include template('sadmin_rcm_ad');
}
else if($action=='apply')
{
	$m=dhtmlchars($m);
	$p=dhtmlchars($p);
	$op=dhtmlchars($op);
	$t=(int)$t;
	
	if(!$m || !$p || !in_array($t,array(0,1,2))) show_msg('广告检索参数错误');
	
	$arr_p=explode('|',$p);
	foreach ($arr_p as $key=>$val)
	{
		$val=trim($val);
		if(!$val) unset($arr_p[$key]);
	}
	$p="'".implode("','",$arr_p)."'";
	
	$apply_list=array();
	$sql="SELECT * FROM `{$tablepre}ad_table` WHERE module='$m' AND pos IN ($p) AND ad_type='$t' ";
	if($op) $sql.=" AND other_param='$op'";
	$q=$db->query($sql);
	while ($rtl=$db->fetch_array($q))
	{
	    $info=false;
	    if($rtl['cur_info']) $info=unserialize($rtl['cur_info']);
		if(!$info) $info=unserialize($rtl['info']);
		
		if($rtl['ad_type']==0) $rtl['title']=$info['goods_name'];
		else if($rtl['ad_type']==1) $rtl['title']=$info['shop_name'];
		else $rtl['title']=$info['title'];
		
		if($rtl['ad_type']==0) $rtl['pic']=$info['goods_pic'];
		else if($rtl['ad_type']==1) $rtl['pic']=$info['shop_logo'];
		else $rtl['pic']=$info['pic'];
		
		//if(!$rtl['pic'] || !file_exists($rtl['pic'])) $rtl['pic']='images/noimages/noproduct.jpg';
		$rtl['pic']=IMG_URL.$rtl['pic'];
		$apply_rtl=$db->get_one("SELECT COUNT(*) AS cnt,SUM(days) AS tdays FROM `{$tablepre}ad_apply` WHERE ad_uid='$rtl[uid]'");
		$rtl['apply_num']=$apply_rtl['cnt'];
		$time_minus=intval(($rtl['expire']-$m_now_time)/3600/24);
		$time_minus<0 && $time_minus=0;
		$rtl['days']=$apply_rtl['tdays']+$time_minus;
		
		$apply_list[]=$rtl;
	}
	$db->free_result();
	
	include template('sadmin_rcm_ad_apply');
}
else if($action=='add')
{
	$arr_days=array(7=>'7天',15=>'15天',30=>'一个月',90=>'一季度',180=>'六个月',365=>'一年');
	$uid=(int)$uid;
	$ad=$db->get_one("SELECT * FROM `{$tablepre}ad_table` WHERE uid='$uid' LIMIT 1");
	if(!$ad) exit('找不到可申请的广告');
	$ad_info=unserialize($ad['info']);
	
	if($_POST && (int)$step==1) //数据提交
	{
		$point_sess='AD'.date('YmdHis').strval(rand(10,99));
		$days=(int)$days;
	    if(!isset($arr_days[$days])) sadmin_show_msg('请选择合法的申请天数',$p_url);
	    $total_point=$ad['price']*$days;
	    if($total_point>$mvm_member['member_point']) show_msg('您的积分不足以申请这个广告');
	    
		if($ad['ad_type']==0)    //处理商品广告
		{
			$goods_id=(int)$goods_id;
			$goods_name=trim($goods_name);
			$goods_type=(int)$goods_type;
			if($goods_name=='') sadmin_show_msg('请把商品名称和商品链接地址填写完整',$p_url);
			//预先的工作
			if($goods_id<=0) sadmin_show_msg('请正确选择商品ID 或 自己填写一个正确的ID',$p_url);
			$goods_table=goods_table($goods_type);
			$goods_rtl=$db->get_one("SELECT goods_sale_price,supplier_id 
			                         FROM `$goods_table` 
			                         WHERE uid='$goods_id' AND supplier_id='$page_member_id' 
			                         LIMIT 1");
			!$goods_rtl && sadmin_show_msg('请正确选择商品ID 或 自己填写一个正确的ID!!!',$p_url);
			$goods_price=currency($goods_rtl['goods_sale_price']);
			$supplier_id=$goods_rtl['supplier_id'];
			if($_FILES['goods_img']['name']!='')    //商品图片
			{
			    require_once 'include/upfile.class.php';
			    $f = new upfile('jpg,jpeg,gif',"upload/ad_pic/");
                $ad_pic = $f->upload('goods_img');
			}
			else sadmin_show_msg('请上传一张商品的广告图片',$p_url);
			
			$goods_info=array(
			    'goods_id'=>$goods_id,
			    'goods_name'=>$goods_name,
			    'goods_desc'=>$goods_desc,
			    'goods_price'=>$goods_price,
			    'goods_pic'=>$ad_pic,
			    'supplier_id'=>$supplier_id,
			    'shop_name'=>$goods_shop_name,
			    'title_color'=>$ad_info['title_color'],
			    'goods_type'=>$goods_type
			);
			
			$row=array(
			    'm_id'=>$m_check_id,
			    'm_uid'=>$m_check_uid,
			    'ad_uid'=>$ad['uid'],
			    'info'=>serialize($goods_info),
			    'reg_time'=>$m_now_time,
			    'days'=>$days,
			    'approval_date'=>0,
			    'point_sess'=>$point_sess
			);
			$db->insert("`{$tablepre}ad_apply`",$row);
			
		}
		else if($ad['ad_type']==1)    //处理商家广告
		{
			$shop_name=trim($shop_name);
			$shop_url=trim($shop_url);
			if($shop_name=='') sadmin_show_msg('请把商家名称和链接地址填写完整',$p_url);
			if($shop_id==0) sadmin_show_msg('请正确选择商家ID 或 自己填写一个正确的ID',$p_url);
			if($_FILES['shop_logo']['name']!='')    //商家logo
			{
			    require_once 'include/upfile.class.php';
			    $f = new upfile('jpg,jpeg,gif',"upload/ad_pic/");
                $ad_pic = $f->upload('shop_logo');
			}
			else sadmin_show_msg('请上传一张商铺的推广logo',$p_url);
			
			$shop_info=array(
			    'shop_name'=>$shop_name,
			    'shop_logo'=>$ad_pic,
			    'member_uid'=>$shop_id,
			    'shop_desc'=>$shop_desc,
			    'star'=>(int)$ad_info['star'],
			    'title_color'=>$ad_info['title_color'],
			    'supplier_id'=>$shop_id
			);
			
			$row=array(
			    'm_id'=>$m_check_id,
			    'm_uid'=>$m_check_uid,
			    'ad_uid'=>$ad['uid'],
			    'info'=>serialize($shop_info),
			    'reg_time'=>$m_now_time,
			    'days'=>$days,
			    'approval_date'=>0,
			    'point_sess'=>$point_sess
			);
			$db->insert("`{$tablepre}ad_apply`",$row);
		}
		else if($ad['ad_type']==2)    //处理其它类型的广告
		{
			$ad_title=trim($ad_title);
			$ad_url=trim($ad_url);
			if($ad_title=='' || $ad_url=='') sadmin_show_msg('请把广告标题和广告链接地址填写完整',$p_url);
			$ad_pic='';
			if($_FILES['ad_pic']['name']!='')    //广告图片
			{
			    require_once 'include/upfile.class.php';
			    $f = new upfile('jpg,jpeg,gif',"upload/ad_pic/");
                $ad_pic = $f->upload('ad_pic');
			}
			
			$other_info=array(
			    'title'=>$ad_title,
			    'show_cat'=>$show_cat,
			    'pic'=>$ad_pic,
			    'url'=>$_POST['ad_url'],
			    'desc'=>$desc,
			    'title_color'=>$ad_info['title_color']
			);
			
			$row=array(
			    'm_id'=>$m_check_id,
			    'm_uid'=>$m_check_uid,
			    'ad_uid'=>$ad['uid'],
			    'info'=>serialize($other_info),
			    'reg_time'=>$m_now_time,
			    'days'=>$days,
			    'approval_date'=>0,
			    'point_sess'=>$point_sess
			);
			$db->insert("`{$tablepre}ad_apply`",$row);
		}
		else sadmin_show_msg('类型选择出错',$p_url);
		
		$point_left=$mvm_member['member_point']-$total_point;
		add_score($m_check_uid,-$total_point,'申请广告位',"申请广告 $ad[module] $ad[pos]",$point_sess);
		$db->query("UPDATE `{$tablepre}member_table` SET member_point='$point_left' WHERE uid='$m_check_uid'");
		
		sadmin_show_msg('广告申请成功，管理员会在3天之内与您联系',$p_url);
		//include template('sadmin_payment_add');
	}
	///////////////end 提交结束//////////////////////
	
	$sel_days=drop_menu($arr_days,'days');
	
	if($action=='add')    //添加的数据预处理
	{   
	    $arr=unserialize($ad['info']);
	    switch ($ad['ad_type'])
	    {
	    	case 0:
	    	    $ad['type']='商品广告';
	    	    $ad['shop_name']=$mvm_shop['shop_name'];
	    	    break;
	    	case 1:
	    	    $ad['type']='商铺广告';
	    	    $ad['shop_id']=$page_member_id;
	    	    $ad['shop_name']=$mvm_shop['shop_name'];
	    	    break;
	    	case 2:
	    	    $ad['type']='其他广告';
	    	    break;
	    	default:
	    	    show_msg('错误的广告类型');
	    	    break;
	    }
	}
	include template('sadmin_rcm_ad_add');
	exit;
}
else if($action=='search_goods')
{   
	$goods_name=trim($goods_name);
	$goods_type=(int)$goods_type;
	$goods_table=goods_table($goods_type);
	$info=array();
	if(strlen($goods_name)<=0)
	{
		$info['error']='请输入要搜索的商品关键字';
		echo json_encode($info);
		exit;
	}
	$info['goods_info']=array();
	
	$q=$db->query("SELECT gt.uid,gt.goods_name,gt.goods_file1,gt.supplier_id  
	               FROM `$goods_table` gt 
	               WHERE gt.supplier_id='$page_member_id' AND gt.goods_name LIKE '%$goods_name%'");
	while($rtl=$db->fetch_array($q))
	{
		$info['goods_info'][$rtl['uid']]=$rtl;
	}
	if(sizeof($info['goods_info'])<=0)
	{
		unset($info['goods_info']);
		$info['error']='找不到相关商品';
	}
	exit(json_encode($info));
}