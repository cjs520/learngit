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
 * $Id: banner.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

$banner_config=array();
$banner_config_path=$shop_file['sellshow']==1?'union/templates/':'union/show_templates/';
$banner_config_path.="$ucfg[mm_skin_name]/banner_config.php";
if(file_exists($banner_config_path)) include($banner_config_path);

if($action=='list')
{
	require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}banner_table","`supplier_id`='$page_member_id'");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,banner_point,banner_width,banner_height,banner_class,banner_subject,banner_file1 
                     FROM `{$tablepre}banner_table` 
                     WHERE `supplier_id`='$page_member_id'
                     ORDER BY banner_point 
                     LIMIT $from_record,$list_num ");
    while ($rt = $db->fetch_array($q))
    {
    	$rt['logo']=ProcImgPath($rt['banner_file1']);
        $rt['edit'] = "admincp.php?module=$module&action=edit&uid=$rt[uid]&page=$page";
        $rt['banner_width']=$banner_config[$rt['banner_point']]['w'];
        $rt['banner_height']=$banner_config[$rt['banner_point']]['h'];
        $rt['banner_type'] = $rt['banner_class']==0 ? '图片广告' : 'Flash广告';
        $ad_rt[] = $rt;
    }
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&page=");
    
    include template('sadmin_banner');
}
else if($action=='add')
{
	if($_POST && (int)$step==1)
	{
    	if($_FILES['banner_file1']['name']!='')
    	{
    		require_once 'include/upfile.class.php';
    		$f = new upfile('gif,jpg,jpeg,png,bmp,swf','union/images/banner/');
    		$banner_file1_text = $f->upload('banner_file1');
    		$banner_file1_text = str_replace('union/','',$banner_file1_text);
    	}

        $row = array(
            'banner_point' => $banner_point,
            'banner_class' => $banner_class,
            'banner_weight' => $banner_weight,
            'banner_subject' => $banner_subject,
            'banner_width' => $banner_width,
            'banner_height' => $banner_height,
            'banner_file1' => $banner_file1_text,
            'banner_url' => $banner_url,
            'supplier_id' => $page_member_id
        );
        $db->insert("{$tablepre}banner_table",$row);
        $cache->delete('banner_array',$page_member_id);
        move_page(base64_decode($p_url));
    }
    $bannner_class_p = 'checked';
    $ad_menu = drop_banner_menu($banner_config,'ad_select');
    include template('sadmin_banner_add');
	exit;
}
else if($action=='edit')
{
	if($_POST && (int)$step==1)
	{
        if ($_FILES['banner_file1']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,jpeg,png,bmp,swf','union/images/banner/');
            file_unlink('union/'.$banner_file1_text,'bucket');
            $banner_file1_text = $f->upload('banner_file1');
        }
        
        $row = array(
            'banner_point' => $banner_point,
            'banner_class' => $banner_class,
            'banner_weight' => $banner_weight,
            'banner_subject' => $banner_subject,
            'banner_width' => $banner_width,
            'banner_height' => $banner_height,
            'banner_file1' => $banner_file1_text,
            'banner_url' => $banner_url,
        );
        $db->update("{$tablepre}banner_table",$row,"uid='$uid' AND `supplier_id`='$page_member_id'");
        $cache->delete('banner_array',$page_member_id);
        move_page(base64_decode($p_url));
    }
    
    $rt = $db->get_one("SELECT * FROM `{$tablepre}banner_table` WHERE uid = '$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
    @extract($rt,EXTR_OVERWRITE);
    $banner_class==0 ? $bannner_class_p = 'checked':$bannner_class_f = 'checked';
    $ad_menu = drop_banner_menu($banner_config,'ad_select',$banner_point);
    include template('sadmin_banner_add');
	exit;
}
else if($action=='del')
{
	$rt = $db->get_one("SELECT uid,banner_file1 FROM {$tablepre}banner_table WHERE uid = '$uid' AND `supplier_id`='$page_member_id'");
	if(!$rt) exit;
    $rt['banner_file1'] &&  file_unlink('union/'.$rt['banner_file1'],'bucket');;
    $db->query("DELETE FROM {$tablepre}banner_table WHERE uid='$rt[uid]'");
    $db->free_result();
    $cache->delete('banner_array',$page_member_id);
    exit;
}
else if($action=='all_delete')
{
	do
	{
	    if(!is_array($uid_check)) break;	
	    foreach ($uid_check as $key=>$val)
	    {
	    	$val=(int)$val;
	    	if($val<=0) unset($uid_check[$key]);
	    }
	    $uid_check = array_unique($uid_check);
	    if(!$uid_check) break;
	    $str_uid=implode(',',$uid_check);
	    
	    $uids=array();
	    $q=$db->query("SELECT uid,banner_file1 FROM `{$tablepre}banner_table` WHERE uid IN ($str_uid) AND supplier_id='$page_member_id'");
	    while($rtl=$db->fetch_array($q))
	    {
	    	if($rtl['banner_file1']) file_unlink('union/'.$rtl['banner_file1'],'bucket');
	    	$uids[]=$rtl['uid'];
	    }
	    if(!$uids) break;
	    $str_uid=implode(',',$uids);
	    $db->query("DELETE FROM `{$tablepre}banner_table` WHERE uid IN ($str_uid)");
	    
	}while(0);
	$cache->delete('banner_array',$page_member_id);
	show_msg('删除成功',"sadmin.php?module=$module&action=list");
}

function drop_banner_menu($date,$name,$id='')
{
    $html="<select name='$name' id='$name'>";
    foreach ($date as $key=>$val)
    {
        $selected=$id==$key?'selected':'';
        $html.="<option value='$key' w='$val[w]' h='$val[h]' $selected>$val[desc]</option>";
    }
    $html.="</select>";
    return $html;
}