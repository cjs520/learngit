<?php
require_once 'include/pic.class.php';
if($mvm_member['isSupplier']>0) show_msg('您已经是商户会员，无需重新开店');
$shop_level=(int)$shop_level;
$money=0;
do
{
    if(!key_exists($shop_level,$lang['shop_level'])) show_msg('您选择的商铺等级错误');
    $time_end=$m_now_time-3*24*3600;
    if(file_exists('data/malldata/config.inc.php')) include 'data/malldata/config.inc.php';

    $money=floatval($update_money[$shop_level]);
    if($mvm_member['member_money']<$money) show_msg("您的预付款余额不足以申请{$lang['shop_level'][$shop_level]}，请先充值");
}while (0);

if ($_POST && (int)$step == 1)
{
    $name=dhtmlchars(trim($name));
    if(!$name) show_msg('请填写店主的真实姓名');
    $shop_name=trim($shop_name);
    if(strlen($shop_name)<=0) show_msg('请填写您的网店名称');
    $province=trim($province);
    $city=trim($city);
    $address=trim($address);
    if(!$province || !$city || !$address) show_msg('请将联系地址填写完整');
    if(!preg_match('/^[\d\-]{7,}$/',$tel)) show_msg('请将正确的联系电话');
    $email=dhtmlchars($email);

    $member_homepage='mvm'.strval($m_now_time).rand(100,999);
    $goods_category_value=0;
    foreach ($goods_cat as $val)
    {
        $val=(int)$val;
        if($val>0) $goods_category_value=$val;
    }
    
    //判断上传文件
    $shop_step=0;
    require_once 'include/upfile.class.php';
    if($_FILES['up_id_card']['name']!='')    //身份证
    {
        $f_id_card = new upfile('jpg,jpeg,JPG,JPEG,gif,GIF',"union/upload/id_card/");
        $up_id_card = $f_id_card->upload('up_id_card');

        $shop_step|=8;
    }
    
    $up_licence_thumb=$up_licence='images/noimages/uncertified';
    if($_FILES['up_licence']['name']!='')    //营业执照
    {
        $f_licence = new upfile('jpg,jpeg,png,gif',"union/upload/licence/");
        $up_licence = $f_licence->upload('up_licence');

        $proc_up_licence=ProcImgPath($up_licence);
        $up_licence_thumb=pic::PicZoom($proc_up_licence,191,147,array(255,255,255),false);
        if($up_licence_thumb==$proc_up_licence) $up_licence_thumb=$up_licence;
        else $up_licence_thumb=str_replace('union/','',$up_licence_thumb);

        $shop_step|=8;
    }
    

    $row=array(
        'member_name'=>$name,
        'member_tel1'=>$tel,
        'member_tel2'=>$tel2,
        'isSupplier'=>1
    );
    $db->update("`{$tablepre}member_table`",$row," uid='$m_check_uid'");
    $db->free_result();
    
    $shop_row=array(
        'm_id'=>$m_check_id,
        'm_uid'=>$m_check_uid,
        'shop_name'=>$shop_name,
        'map_title'=>$shop_name,
        'map_tip'=>"$province$city$county$address",
        'supplier_cat'=>$goods_category_value,
        'run_product'=>$run_product,
        'province'=>$province,
        'city'=>$city,
        'county'=>$county,
        'sellshow'=>(int)$sellshow,
        'shop_address'=>$address,
        'shop_level'=>(int)$shop_level,
        'member_homepage'=>$member_homepage,
        'up_id_card'=>$up_id_card,
        'up_licence'=>$up_licence,
        'up_licence_thumb'=>$up_licence_thumb,
        'register_date'=>$m_now_time,
        'approval_date'=>$m_now_time,
        'shop_expire'=>$m_now_time+365*24*3600,
        'shop_step'=>$shop_step,
        'isSupplier'=>1
    );
    $db->replace("`{$tablepre}member_shop`",$shop_row);
    $db->free_result();
    
    require_once 'include/shop.class.php';
    shop::CreateSupplierFile($m_check_uid);
    $db->free_result();
    $db->query("UPDATE `{$tablepre}config` SET cf_value='$tel' WHERE supplier_id='$m_check_uid' AND cf_name='mm_tel'");
    $db->query("UPDATE `{$tablepre}config` SET cf_value='$tel2' WHERE supplier_id='$m_check_uid' AND cf_name='mm_mobile'");
    $db->free_result();
    
    //扣钱
    add_money($m_check_uid,-$money,'商铺开张','商铺开张');
    
    //提现账号
    if(in_array((int)$account_type,array(0,1)))
    {
        $arr_member_account=array();
        $arr_member_account['member_uid']=$m_check_uid;
        $arr_member_account['member_name']=$name;
        $arr_member_account['type']=(int)$account_type;
        $arr_member_account['account']=(int)$account_type==0?dhtmlchars($taobao_account):dhtmlchars($bank_account);
        $arr_member_account['bank']=(int)$account_type==0?'':dhtmlchars($bank);
        $db->replace("`{$tablepre}member_account`",$arr_member_account);
        $db->free_result();
    }
    
    $db_mem_cache->op(0,'Id2Domain',$m_check_uid,'',db_memory_cache::DELETE );

    //发送邮件
    if((int)$mm_mail_shop_apply==1 && $mvm_member['member_email'])
    {
        smtp_mail($mvm_member['member_email'],
            "{$mm_mall_title}商铺申请成功",
            str_replace(array('{mall_title}','{shop_name}'),array($mm_mall_title,$shop_name),$mm_mail_shop_apply_cnt)
        );
    }

    show_msg('网店申请成功，请进入自己的网店后台添加商品','sadmin.php?module=index');
}

if($mvm_member['isSupplier']==1) show_msg('您已经提交过申请，请耐心等待审核通过');
if($mvm_member['isSupplier']>1) show_msg('您已经是正式商铺，现在跳转到您的网店中',GetBaseUrl('index','','',$m_check_uid));

//商铺类型
$sel_shop_level=drop_menu($lang['shop_level'],'shop_level');

require 'header.php';
include template('member_shopreg');