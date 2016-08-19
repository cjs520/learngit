<?php
if(!$m_check_uid) exit('ERR:您还未登录，请先登录');

$uid=(int)$uid;
$product=$db->get_one("SELECT uid,assure,goods_name FROM `{$tablepre}goods_auction` WHERE uid='$uid' AND supplier_id='$page_member_id' AND approval=1 LIMIT 1");
if(!$product) exit('ERR:检索不到指定的竞拍商品');
$assure=$db->get_one("SELECT money FROM `{$tablepre}goods_auction_assure` WHERE m_uid='$m_check_uid' AND g_uid='$product[uid]' LIMIT 1");
if($assure) exit('ERR:您已经缴交过保证金，无需重新支付');
if($mvm_member['member_money']<$product['assure']) exit('ERR:您的预付款余额，不足以缴纳保证金，请先充值');

$db->query("REPLACE INTO `{$tablepre}goods_auction_assure` (m_uid,g_uid,money,reg_date) 
            VALUES ('$m_check_uid','$product[uid]','$product[assure]','$m_now_time')");
add_money($m_check_uid,-$product['assure'],'保证金',"参与{$product[goods_name]}竞拍");

echo "OK:保证金充值成功";
exit;