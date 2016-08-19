<?php
if(!$m_check_uid) exit('ERR:您还未登录，请先登录');
$uid=(int)$uid;
$coupon=$db->get_one("SELECT uid,name,start_date,end_date,discount,price_lbound,handout_type,sale_price
                      FROM `{$tablepre}coupon_cat` WHERE uid='$uid' AND supplier_id='$page_member_id' 
                      LIMIT 1");
if(!$coupon) exit('ERR:检索不到指定的优惠券');
if(!in_array($coupon['handout_type'],array(0,1))) exit('ERR:您指定的优惠券无法直接获取');

$rtl=$db->get_one("SELECT uid FROM `{$tablepre}coupon` WHERE cc_uid='$uid' AND m_uid='$m_check_uid' LIMIT 1");
if($rtl && $coupon['handout_type']==0) exit("优惠券\"{$coupon[name]}\"您已领取，请先使用后再来重新获取");

do    //用积分阅换
{
    if($coupon['handout_type']!=1) break;
    if($coupon['sale_price']<=0) break;
    if($mvm_member['member_point']<$coupon['sale_price']) exit('ERR:您的积分不足，请先充值');
    add_score($m_check_uid,-$coupon['sale_price'],'兑换优惠券',"兑换优惠券$coupon[name]");
    add_score($page_member_id,$coupon['sale_price'],'兑换优惠券',"{$m_check_id}兑换优惠券$coupon[name]");
}while (0);

$row=array(
    'm_uid'=>$m_check_uid,
    'supplier_id'=>$page_member_id,
    'cc_uid'=>$coupon['uid'],
    'name'=>$coupon['name'],
    'start_date'=>$coupon['start_date'],
    'end_date'=>$coupon['end_date'],
    'discount'=>$coupon['discount'],
    'price_lbound'=>$coupon['price_lbound'],
    'register_date'=>$m_now_time
);
$db->insert("`{$tablepre}coupon`",$row);

exit('OK:兑换成功，请进入会员中心查看');