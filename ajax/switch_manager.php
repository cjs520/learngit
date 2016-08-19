<?php
$arr_level_name=array(1=>'二当家','2'=>'三当家','3'=>'四当家','4'=>'五当家');
$arr=array('err'=>'','shop_list'=>array());
if(!$m_check_id)
{
    $arr['err']='请先登录';
    exit(json_encode($arr));
}

array_push($arr['shop_list'],array('自家商铺','大当家','account.php?action=man_shop&cmd=un_auth'));
$q=$db->query("SELECT shop_m_uid,level FROM `{$tablepre}member_shop_manager` WHERE m_uid='$m_check_uid'");
while($rtl=$db->fetch_array($q))
{
    $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[shop_m_uid]' AND approval_date>10 LIMIT 1");
    if(!$shop) continue;
    array_push($arr['shop_list'],array($shop['shop_name'],$arr_level_name[$rtl['level']],"account.php?action=man_shop&cmd=auth&shop_uid=$rtl[shop_m_uid]"));
}
$db->free_result();

exit(json_encode($arr));