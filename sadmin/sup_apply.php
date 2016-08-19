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
 * $Id: sup_apply.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
define('MVMMALL_SCR','index');

if($action=='list')
{
	include template('sadmin_sup_apply');
}
else if($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $shop_apply=$db->get_one("SELECT m_uid,up_logo,banner FROM `{$tablepre}shop_apply` WHERE m_uid='$page_member_id' LIMIT 1");
        if($shop_apply)
        {
            file_unlink($shop_apply['up_logo']);
            file_unlink($shop_apply['banner']);
        }
        
        $shop_name=dhtmlchars($shop_name);
        $sellshow=(int)$sellshow==2?2:1;
        $goods_category_value=0;
        foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $goods_category_value=$val;
        }
        $run_product=dhtmlchars($run_product);
        $address=dhtmlchars($address);
        $name=dhtmlchars($name);
        $tel=dhtmlchars($tel);
        $qq=dhtmlchars($qq);
        $logo_tip=dhtmlchars($logo_tip);
        $banner_tip=dhtmlchars($banner_tip);
        $shop_desc=dhtmlchars($shop_desc);
        $msg=dhtmlchars($msg);
        $up_logo='';
        $banner='';
        
        if(!$shop_name) show_msg('请填写您的商铺名称');
        if(!$address) show_msg('请填写您的商铺地址');
        if(!$run_product) show_msg('请填写您的主营产品');
        
        if ($_FILES['up_logo']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','upload/acc/',150000);
            $up_logo = $rowset->upload('up_logo');
        }
        
        if ($_FILES['banner']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','upload/acc/',500000);
            $banner = $rowset->upload('banner');
        }
        
        $row=array(
            'm_uid'=>$page_member_id,
            'shop_name'=>$shop_name,
            'sellshow'=>$sellshow,
            'run_product'=>$run_product,
            'shop_cat'=>$goods_category_value,
            'address'=>$address,
            'tel'=>$tel,
            'name'=>$name,
            'qq'=>$qq,
            'up_logo'=>$up_logo,
            'logo_tip'=>$logo_tip,
            'banner'=>$banner,
            'banner_tip'=>$banner_tip,
            'shop_desc'=>$shop_desc,
            'msg'=>$msg,
            'reg_time'=>$m_now_time
        );
        $db->replace("`{$tablepre}shop_apply`",$row);
        $db->free_result();
    }
    show_msg('你的托管申请成功提交，管理员将在24小时内与您联系',"sadmin.php?module=$module&action=list");
}