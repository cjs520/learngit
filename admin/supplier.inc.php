<?php

/**
 * MVM_MALL 网上商店系统  会员管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-11 $
 * $Id: supplier.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/shop.class.php';
//require_once 'include/pic.class.php';

if($action=='list')
{
	require_once 'include/pager.class.php';
	$order_sql=" ORDER BY register_date DESC";
	$search_sql = "WHERE TRUE ";
    $start_time && $search_sql.=" AND register_date>".strtotime($start_time);
    $end_time && $search_sql.=" AND register_date<=".strtotime($end_time);
	$shop_name && $search_sql .= " AND shop_name LIKE '".dhtmlchars($shop_name)."%'";
	$ps_member && $search_sql .= " AND m_id = '".dhtmlchars($ps_member)."'";
	$province && $search_sql .= "  AND province = '".dhtmlchars($province)."'";
	$city && $search_sql .= " AND city = '".dhtmlchars($city)."'";
	$county && $search_sql .= " AND county = '".dhtmlchars($county)."'";
	if(isset($shop_level) && is_numeric($shop_level) && $shop_level>=0) $search_sql.=" AND shop_level='$shop_level'";
	
	$total_count = $db->counter("{$tablepre}member_shop",$search_sql);
	$page = $page ? (int)$page:1;
	$list_num = 25;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$q = $db->query("SELECT m_uid,m_id,shop_name,run_product,shop_level,approval_date,supplier_cat,sellshow 
	                 FROM `{$tablepre}member_shop` 
	                 $search_sql 
	                 $order_sql 
	                 LIMIT $from_record, $list_num");
	while($rt = $db->fetch_array($q))
	{
	    $m=$db->get_one("SELECT isSupplier,member_name FROM `{$tablepre}member_table` WHERE uid='$rt[m_uid]' LIMIT 1");
	    if(!$m) continue;
	    $rt=array_merge($rt,$m);
	    
		$rt['isSupplier'] == 1 && $rt['tag']='<span style="color:#f00;">未认证</span>';
		$rt['isSupplier'] == 2 && $rt['tag']='<span style="color:#f00;">已认证</span>';
		$rt['isSupplier'] == 3 && $rt['tag']='<span style="color:#f00;">已审核</span>';
		$rt['shop_level_name'] = $lang['shop_level'][$rt['shop_level']];
		$rt['sellshow']=$rt['sellshow']==1?'<span style="color:#36c;">销售型</span>':'展示型';
		$rt['url'] = GetBaseUrl('index','','',$rt['m_uid']);
		$cat_ids[]=$rt['supplier_cat'];
		
		$member_rt[] = $rt;
	}
	$db->free_result();
	
	if($cat_ids)
    {
        $str_cat_ids=implode(',',$cat_ids);
        $q=$db->query("SELECT uid,category_name FROM `{$tablepre}category` WHERE uid IN ($str_cat_ids)");
        while($rtl=$db->fetch_array($q)) $cats[$rtl['uid']]=$rtl['category_name'];
    }
    
    foreach ($member_rt as $key=>$val)
    {
        $member_rt[$key]['supplier_cat']=$cats[$val['supplier_cat']];
    }
	
	$page_list = $rowset->link("admincp.php?module=$module&action=$action&ps_member=$ps_member&province=".urlencode($province)."&city=".urlencode($city)."&county=".urlencode($county)."&shop_level=$shop_level&page=");
	require_once template('supplier');
	footer();
}
else if ($action=='edit')
{
    $uid=(int)$uid;
    $user_rt = $rt_member = $db->get_one("SELECT * FROM `{$tablepre}member_table` WHERE uid='$uid' LIMIT 1");
    $member_account=$db->get_one("SELECT * FROM `{$tablepre}member_account` WHERE member_uid='$uid' LIMIT 1");
    if(!$user_rt) show_msg('检索不到指定会员');
    $member_shop=$db->get_one("SELECT * FROM `{$tablepre}member_shop` WHERE m_uid='$uid' LIMIT 1");
    
    if($setp==1 && $_POST)
    {
    	$new_pass = $new_email = null;
    	$i_certified_type=0;
    	if(is_array($certified_type))
    	{
    		foreach ($certified_type as $val) $i_certified_type|=$val;
    	}
    	
    	$pass1 = $rt_member['member_pass'];
        $base_pass=$rt_member['base_pass'];
    	$pay_pass1=$rt_member['pay_pass'];
        if ($pass)
    	{
    	    $pass1=md5($pass);
    	    $base_pass=base64_encode($pass);
    	}
    	if($pay_pass) $pay_pass1=md5($pay_pass);
    	
    	//头像
        $member_file_text = $rt_member['member_image'];
        if ($_FILES['member_file']['name']!='')
        {
        	require_once 'include/upfile.class.php';
            file_unlink('union/'.$member_file_text,'bucket');
            $rowset = new upfile('gif,jpg,png,bmp','union/images/member/');
			$member_file_text = $rowset->upload('member_file');
			//$member_file_text=pic::PicZoom(ProcImgPath($member_file_text),79,79);
			$member_file_text=str_replace('union/','',$member_file_text);
        }
    	
    	//验证二级域名
		$homepage=trim(strtolower($homepage));
		if(!$homepage) $homepage='mvm'.$m_now_time;
		if(!preg_match('/^[a-z0-9]{1,}$/',$homepage)) sadmin_show_msg('二级域名格式不对',$p_url);
		$rtl=$db->get_one("SELECT m_uid FROM `{$tablepre}member_shop` WHERE member_homepage='$homepage' AND m_uid<>'$uid'");
		if($rtl) sadmin_show_msg('这个二级域名已经有人占用，请更换一个',$p_url);
    	
    	
        $rows = array(
		    'member_class' => $mclass,
		    'member_pass' => $pass1,
		    'base_pass' => $base_pass,
		    'pay_pass'=>$pay_pass1,
		    'member_name' => $name,
		    'member_sex' => $sex,
		    'member_birthday' => mktime(0,0,0,(int)$birth_mm,(int)$birth_dd,(int)$birth_yy),
		    'member_tel1' => $tel1,
		    'member_tel2' => $tel2,
		    'member_email' => $email,
		    'member_zip' => $zip1,
		    'qq' => $qq,
		    'taobao' => $taobao,
		    'isSupplier'=>(int)$isSupplier,
		    'member_image' => $member_file_text,
		);
		$db->update("{$tablepre}member_table",dhtmlchars($rows),"uid='$uid'");
		
		//添加新积分、新预付款
		add_score($uid,$new_point,'管理员设置','管理员设置');
		add_money($uid,$new_money,'管理员设置','管理员设置');
		
		//提现账号
		$account_row=array();
		if((int)$account_type==0)
		{
		    $account_row['member_uid']=$uid;
		    $account_row['type']=0;
		    $account_row['member_name']=dhtmlchars($taobao_name);
		    $account_row['account']=dhtmlchars($taobao_account);
		}
		else if((int)$account_type==1)
		{
		    $account_row['member_uid']=$uid;
		    $account_row['type']=1;
		    $account_row['member_name']=dhtmlchars($bank_name);
		    $account_row['account']=dhtmlchars($bank_account);
		    $account_row['bank']=dhtmlchars($bank);
		}
		if($account_row) $db->replace("`{$tablepre}member_account`",$account_row);
		
		$member_shop=daddslashes($member_shop);
		$member_shop['shop_name']=dhtmlchars($shop_name);
		$goods_category_value=0;
		foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $goods_category_value=$val;
        }
        $member_shop['supplier_cat']=$goods_category_value;
        $member_shop['member_homepage']=$homepage;
        $member_shop['run_product']=dhtmlchars($run_product);
        $member_shop['shop_intro']=dhtmlchars($intro);
        $member_shop['shop_level']=(int)$shop_level;
        $member_shop['shop_expire']=strtotime($shop_expire);
        $member_shop['province']=$province;
		$member_shop['city']=$city;
		$member_shop['county']=$county;
		$member_shop['shop_address']=$address1;
		$member_shop['member_homepage']=$homepage;
		$member_shop['isSupplier']=(int)$isSupplier;
        $cer_type=0;
        foreach ($certified_type as $val) $cer_type|=(int)$val;
        $member_shop['certified_type']=$cer_type;
        $db->replace("`{$tablepre}member_shop`",$member_shop," m_uid='$uid' ");
        $db_mem_cache->op(0,'Id2Domain',$uid,'',db_memory_cache::DELETE );
        
        if((int)$is_recreate==1) shop::CreateSupplierFile($uid);
        
        admin_log("编辑商铺：$user_rt[member_id]");
        move_page(base64_decode($p_url));
    }
    
    @extract($user_rt,EXTR_OVERWRITE);
    
    //会员资料
    $birth_yy=date('Y',$member_birthday);
    $birth_mm=date('m',$member_birthday);
    $birth_dd=date('d',$member_birthday);
    $member_image=ProcImgPath($member_image).'@!member_icon';
    $member_sex?$member_sex_y='checked':$member_sex_n='checked';
    $grade_select = drop_menu($m_class_array,'mclass',$member_class);
    
    
    //是否供应商
    $isSupplier==0 && $sup0_checked='checked';
    $isSupplier==1 && $sup1_checked='checked';
    $isSupplier==2 && $sup2_checked='checked';
    $isSupplier==3 && $sup3_checked='checked';
    
    //商铺资料
    $member_shop['shop_expire']=date('Y-m-d',$member_shop['shop_expire']);
    if($member_shop['certified_type'] & 1) $cer1_checked='checked';
    if($member_shop['certified_type'] & 2) $cer2_checked='checked';
    if($member_shop['certified_type'] & 4) $cer3_checked='checked';
    $shop_level=drop_menu($main_lang['shop_level'],'shop_level',$member_shop['shop_level']);    //商铺等级
    
    //证件地址
    $member_shop['up_id_card'] && $id_card="<a href='".ProcImgPath($member_shop['up_id_card'])."' target='_blank'>身份证</a>";
    $member_shop['up_licence'] && $licence="<a href='".ProcImgPath($member_shop['up_licence'])."' target='_blank'>营业执照</a>";
    $member_shop['up_logo'] && $logo="<a href='".ProcImgPath($member_shop['up_logo'])."' target='_blank'>商铺logo</a>";
    
    //提现账号
    $member_account['type']==0?$account_type_0_checked='checked':$account_type_1_checked='checked';
    do
    {
        if(!isset($member_account['member_uid'])) break;
        
        if($member_account['type']==0)    //支付宝
        {
            $member_account['taobao_account']=$member_account['account'];
            $member_account['taobao_name']=$member_account['member_name'];
        }
        else if($member_account['type']==1)    //银行账号
        {
            $member_account['bank_account']=$member_account['account'];
            $member_account['bank_name']=$member_account['member_name'];
        }
    }while (0);
    
    require_once template('supplier_add');
    exit;
    
}
else if ($action=='del')
{
	$uid  =(int)$uid;
	if($uid <= 1) exit('ERROR:指定的用户无法删除');
    $rtl = $db->get_one("SELECT uid,member_id FROM `{$tablepre}member_table` WHERE uid = '$uid' LIMIT 1");
    if(!$rtl) exit('ERROR:检索不到指定商铺用户');
    shop::CreateSupplierFile($uid,true);
    $db->query("DELETE FROM `{$tablepre}member_shop` WHERE m_uid='$uid'");
    $db->query("DELETE FROM `{$tablepre}member_table` WHERE uid='$uid'");
    $db->free_result();
    $db->query("DELETE FROM `{$tablepre}point_table` WHERE point_id = '$rtl[member_id]'");
    $db->query("DELETE FROM `{$tablepre}money_table` WHERE money_id = '$rtl[member_id]'");
    $db->query("DELETE FROM `{$tablepre}order_goods` WHERE order_id IN (SELECT uid FROM `{$tablepre}order_info` WHERE username='$rt_member[member_id]')");
    $db->query("DELETE FROM `{$tablepre}order_info` WHERE username='$rt_member[member_id]'");
    $db->query("DELETE FROM `{$tablepre}cart_table` WHERE m_id='$rt_member[member_id]'");
    $db->free_result();
    $db->query("DELETE FROM `{$tablepre}address` WHERE m_uid='$rtl[uid]'");
    $db->query("DELETE FROM `{$tablepre}member_statistics` WHERE m_uid='$rtl[uid]'");
    $db->free_result();
    admin_log("删除商铺：$rtl[member_id]");
    exit('OK:删除成功');
}
else if($action=='close_supplier')
{
	$uid=(int)$uid;
	$set_data=0;
	if($uid>0)
	{
		$rtl=$db->get_one("SELECT approval_date,shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$uid' LIMIT 1");
		$set_data=$rtl['approval_date']>10?0:$m_now_time;
		$db->query("UPDATE `{$tablepre}member_shop` SET approval_date='$set_data' WHERE m_uid='$uid'");
		admin_log($set_data>10?"开启商铺:$rtl[shop_name]":"关闭商铺:$rtl[shop_name]");
	}
	echo $set_data;
	exit;
}
else show_msg('pass_worng');
