<?php
require_once 'include/pic.class.php';
$list = $db->get_one("SELECT * FROM `{$tablepre}member_table` WHERE uid = '$m_check_uid' LIMIT 1");

if($cmd=='email_code')
{
    if($m_now_time-(int)$_SESSION['email_code_time']<90) exit('ERR:您发送的频率太高了，请'.strval(90-($m_now_time-(int)$_SESSION['email_code_time'])).'秒后再发送');
    if(!preg_match('/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/',$member_email)) exit('ERR:请输入正确的邮箱地址');
    if($member_email==$list['member_email']) exit('ERR:新的邮箱地址和当前的邮箱地址一样，无需更换');
    
    $rnd =substr(md5(strval($m_now_time).strval(mt_rand(1000,9999))),0,8);
	$row = array(
		'user_id' => $list['member_id'],
		'lost_type'=>'2',
		'lost_str' => $rnd,
		'info'=>$member_email,
		'lost_time' => $m_now_time,
	);
	$db->replace("{$tablepre}lostpass",$row);
	$db->free_result();
	
	smtp_mail($member_email,"{$mm_mall_title}邮箱修改验证码","您的邮箱修改验证码是：$rnd<br />请将此验证码填入表格内，并提交");
	
    $_SESSION['email_code_time']=$m_now_time;
    exit('OK：验证码已发送到您指定邮箱，请及时查收');
}

if ($_POST && $ps_mode=='modify')
{
    $base_pass=$list['base_pass'];
    $insert_pass = $list['member_pass'];
    if ($old_pass && $pass1)
    {
        $old_pass = md5($old_pass);
        if($old_pass != $list['member_pass']) show_msg('原始用户密码填写错误');
        if($pass1 != $pass2) show_msg('两次用户密码填写不一致');
        
        $new_pass=$pass1;    //新密码明文
        $base_pass=base64_encode($new_pass);
        $insert_pass = md5($pass1);
    }
    
    $pay_pass=$list['pay_pass'];
    if($pay_pass1)
    {
        if($list['pay_pass'])
        {
            $old_pay_pass=md5($old_pay_pass);
            if($old_pay_pass!=$list['pay_pass']) show_msg('原始支付密码填写错误');
        }
        if($pay_pass1!=$pay_pass2) show_msg('两次支付密码填写不一致');
        $pay_pass=md5($pay_pass1);
    }
    
    $birthday=mktime(0,0,0,$birth_mm,$birth_dd,$birth_yy);
    $member_email=$list['member_email'];
    $email_code=dhtmlchars($email_code);
    do    //通过邮箱验证码获取新邮箱地址
    {
        if(!$email_code) break;
        $rtl=$db->get_one("SELECT info FROM `{$tablepre}lostpass` WHERE user_id='$m_check_id' AND lost_type='2' AND lost_str='$email_code' LIMIT 1");
        if(!$rtl) break;
        if(!preg_match('/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/',$rtl['info'])) break;
        $member_email=$rtl['info'];
        $db->query("DELETE FROM `{$tablepre}lostpass` WHERE user_id='$m_check_id' AND lost_type='2'");
        $db->free_result(1);
    }while (0);
    
    
    $member_file_text=$list['member_image'];
    if ($_FILES['member_file']['name']!='')
    {
        require_once 'include/upfile.class.php';
        file_unlink(ProcImgPath($member_file_text));
        $rowset = new upfile('gif,jpg,png,bmp','union/images/member/');
        $member_file_text = $rowset->upload('member_file');
        //$member_file_text=pic::PicZoom(ProcImgPath($member_file_text),79,79);
        $member_file_text=str_replace('union/','',$member_file_text);
    }

    $rows = array(
        'member_pass' => $insert_pass,
        'base_pass' => $base_pass,
        'pay_pass'=>$pay_pass,
        'member_name' => $name,
        'member_sex' => $sex,
        'member_birthday' => $birthday,
        'member_tel1' => $tel1,
        'member_tel2' => $tel2,
        'member_email' => $member_email,
        'member_zip' => $zip1,
        'province' => $province,
        'city' => $city,
        'county' => $county,
        'member_address' => $address1,
        'qq' => $qq,
        'taobao' => $taobao,
        'member_image' => $member_file_text,
    );
    $rows = dhtmlchars($rows);    //数据过滤
    $db->update("{$tablepre}member_table",$rows,"member_id = '$m_check_id'");

    show_msg('会员资料编辑成功',"account.php?action=$action");
}

$zip1 = substr($list['member_zip'],0,6);
@extract($list);
$birth_yy=date('Y',$member_birthday);
$birth_mm=date('m',$member_birthday);
$birth_dd=date('d',$member_birthday);

$list['member_sex']==1?$sex_1_checked='checked':$sex_2_checked='checked';
$member_image=ProcImgPath($member_image);
if(!$member_email) $member_email='还未填写';

require_once 'header.php';
require_once template('member_profile');