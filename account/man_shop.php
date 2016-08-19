<?php
if($cmd=='auth')
{
    $shop_uid=(int)$shop_uid;
    if($shop_uid<=0) show_msg('商铺参数指定错误');
    $shop=$db->get_one("SELECT m_uid,approval_date FROM `{$tablepre}member_shop` WHERE m_uid='$shop_uid' LIMIT 1");
    if(!$shop) show_msg('检索不到指定的商铺');
    if($shop['approval_date']<10) show_msg('商铺已被超级管理员强制关闭，无法进入');
    $manager=$db->get_one("SELECT shop_m_uid FROM `{$tablepre}member_shop_manager` WHERE shop_m_uid='$shop_uid' AND m_uid='$m_check_uid' LIMIT 1");
    if(!$manager) show_msg('您不是商铺管理员，无法进入');

    //管理进入授权
    $_SESSION['user']['man_shop']=$shop_uid;
    move_page('sadmin.php?module=index');
}
else if($cmd=='un_auth')
{
    unset($_SESSION['user']['man_shop']);
    move_page('sadmin.php?module=index');
}