<?php
require_once 'include/common.inc.php';

if(!$m_check_id) exit('未登录');
if((int)$_SESSION['user']['man_shop']>0) $page_member_id=(int)$_SESSION['user']['man_shop'];
else $page_member_id=$m_check_uid;
$shop=$db->get_one("SELECT isSupplier FROM `{$tablepre}member_shop` WHERE m_uid='$page_member_id' LIMIT 1");
if(!$shop) exit('商铺不存在');
if($shop['isSupplier']<3) exit('商铺未通过审核');

require_once 'include/upfile.class.php';

$img_dir='union/shopimg/user_img/'.strval($page_member_id).'/taobao/';
if(!is_dir($img_dir)) mkdir($img_dir,0777);

if($_FILES["userfile"]['name'])
{
    $rowset = new upfile('tbi,jpg,jpeg,gif',$img_dir);
    $new_name = str_replace('.tbi','.jpg',$_FILES["userfile"]['name']);
    $tbi = $rowset->upload('userfile',$new_name);
}
echo 'success';
?>