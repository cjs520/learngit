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
 * $Id: member.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/pic.class.php';

if($action=='list')
{
    $search_sql.=" WHERE isSupplier='0' ";
	require_once 'include/pager.class.php';
	if($start_time) $search_sql.=" AND register_date>=".strtotime($start_time);
	if($end_time) $search_sql.=" AND register_date<=".strtotime($end_time);
	$ps_member && $search_sql .= " AND member_id LIKE '".dhtmlchars($ps_member)."%'";
	$grade=(int)$grade;
	if($grade>0) $search_sql.=" AND member_class='$grade'";

	$total_count = $db->counter("{$tablepre}member_table",$search_sql);
	$arr_grade = $m_class_array;//会员等级
	$page = $page ? (int)$page:1;
	$list_num = 25;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
	$q = $db->query("SELECT uid,member_id,member_money,member_point,member_sex,member_name,member_class,member_tel1,member_tel2,province,city,county,member_address,
	                        register_date 
	                 FROM `{$tablepre}member_table` FORCE INDEX (isSupplier) 
	                 $search_sql 
	                 ORDER BY register_date DESC 
	                 LIMIT $from_record, $list_num");
	while($rt = $db->fetch_array($q))
	{
		$rt['register_date'] = date('Y-m-d',$rt['register_date']);
		$rt['member_money'] = currency($rt['member_money']);
		$rt['member_class'] = $arr_grade[$rt['member_class']];
		$rt['member_sex'] = $rt['member_sex']==1 ? '先生' : '女士';
		!$rt['member_name'] && $rt['member_name']='未填写';
		
		$rt['last_login']='还未登录';
		$statistics=$db->get_one("SELECT login_time FROM `{$tablepre}member_statistics` WHERE m_uid='$rt[uid]' LIMIT 1");
		if($statistics['login_time']) $rt['last_login']=date('Y-m-d H:i',$statistics['login_time']);
		
		$member_rt[] = $rt;
	}
	$db->free_result();
	$page_list = $rowset->link("admincp.php?module=$module&action=$action&amode=$amode&ps_member=$ps_member&grade=$grade&page=");
	
	$grade_sel=drop_menu($arr_grade,'grade',$grade);
	
	require_once template('member');
	footer();
}
else if ($action=='edit')
{
    $uid=(int)$uid;
    $user_rt=$rt_member = $db->get_one("SELECT * FROM `{$tablepre}member_table` WHERE uid='$uid' LIMIT 1");
    if(!$user_rt) show_msg('检索不到指定会员');
    
    if($_POST && (int)$step==1)
    {
    	$new_pass = $new_email = '';
    	$pass1 = $rt_member['member_pass'];
    	$base_pass=$rt_member['base_pass'];
    	$pay_pass1=$rt_member['pay_pass'];
    	if ($pass)
    	{
    		$pass1 = md5($pass);
    		$base_pass=base64_encode($pass);
    	}
    	if($pay_pass) $pay_pass1=md5($pay_pass);
    	
    	
        //头像
        $member_file_text = $rt_member['member_image'];
        if ($_FILES['member_file']['name']!='')
        {
        	require_once MVMMALL_ROOT.'include/upfile.class.php';
            file_unlink('union/'.$member_file_text,'bucket');
            $rowset = new upfile('gif,jpg,png,bmp','union/images/member/');
			$member_file_text = $rowset->upload('member_file');
			//$member_file_text=pic::PicZoom(ProcImgPath($member_file_text),79,79);
			$member_file_text=str_replace('union/','',$member_file_text);
        }
        
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
		    'province' => $province,
		    'city' => $city,
		    'county' => $county,
		    'member_address' => $address1,
		    'qq' => $qq,
		    'taobao' => $taobao,
		    'member_image' => $member_file_text,
		);
		$db->update("{$tablepre}member_table",dhtmlchars($rows),"uid='$uid'");
		
		add_score($uid,$new_point,'管理员设置','管理员设置');
		add_money($uid,$new_money,'管理员设置','管理员设置');
		admin_log("编辑会员资料：$user_rt[member_id]");
		
        move_page(base64_decode($p_url));
    }
    
    $uid = (int)$uid;
    @extract($user_rt,EXTR_OVERWRITE);
    $member_sex?$member_sex_y='checked':$member_sex_n='checked';
    $member_image=ProcImgPath($member_image).'@!member_icon';;
    $birth_yy=date('Y',$member_birthday);
    $birth_mm=date('m',$member_birthday);
    $birth_dd=date('d',$member_birthday);
    $grade_select = drop_menu($m_class_array,'mclass',$member_class);    //等级下拉菜单；
    require_once template('member_add');
    exit;
    
}
else if ($action=='del')
{
	$uid  =(int)$uid;
	if($uid <= 1) exit('ERROR:超级管理员，禁止删除');
    $rt_member = $db->get_one("SELECT uid,member_image,member_id FROM `{$tablepre}member_table` WHERE uid = '$uid' LIMIT 1");
    if(!$rt_member) exit('ERROR:检索不到指定会员');
    admin_log("删除会员资料：$rt_member[member_id]");
    file_unlink('union/'.$rt_member['member_image'],'bucket');
    
    $db->query("DELETE FROM `{$tablepre}member_table` WHERE uid = '$uid'");
    $db->query("DELETE FROM `{$tablepre}point_table` WHERE point_id = '$rt_member[member_id]'");
    $db->query("DELETE FROM `{$tablepre}money_table` WHERE money_id = '$rt_member[member_id]'");
    $db->query("DELETE FROM `{$tablepre}address` WHERE m_uid='$rt_member[uid]'");
    $db->query("DELETE FROM `{$tablepre}member_statistics` WHERE m_uid='$rt_member[uid]'");
    $db->query("DELETE FROM `{$tablepre}order_goods` WHERE order_id IN (SELECT uid FROM `{$tablepre}order_info` WHERE username='$rt_member[member_id]')");
    $db->query("DELETE FROM `{$tablepre}order_info` WHERE username='$rt_member[member_id]'");
    $db->query("DELETE FROM `{$tablepre}cart_table` WHERE m_id='$rt_member[member_id]'");
    $db->free_result();
    exit('OK:删除成功');
}
else show_msg('pass_worng');
