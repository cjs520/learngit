<?php
require_once 'header.php';
require_once 'include/pager.class.php';
$auction_list=array();

$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_auction_assure` WHERE m_uid='$m_check_uid'");
$total_count = (int)$rtl['cnt'];
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q=$db->query("SELECT g_uid,success 
               FROM `{$tablepre}goods_auction_assure` 
               WHERE m_uid='$m_check_uid' 
               ORDER BY g_uid DESC 
               LIMIT $from_record,$list_num");
while ($rtl=$db->fetch_array($q))
{
    $g=$db->get_one("SELECT uid,goods_name,goods_file1,supplier_id,is_complete,end_date,start_date 
                     FROM `{$tablepre}goods_auction` 
                     WHERE uid='$rtl[g_uid]' 
                     LIMIT 1");
    if(!$g) continue;
    
    $join=$db->get_one("SELECT m_uid,price,m_id,register_date FROM `{$tablepre}goods_auction_join` WHERE g_uid='$g[uid]' ORDER BY register_date DESC LIMIT 1");
    $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$g[supplier_id]' LIMIT 1");
    
    $g['goods_file1']=ProcImgPath($g['goods_file1']);
    $g['url']=GetBaseUrl('auction_detail',$g['uid'],'action',$g['supplier_id']);
    $g['cur_price']=$join?currency($join['price']):'暂无出价';
    $g['buyer']=$join?$join['m_id']:'无';
    $g['cur_date']=$join?date('Y-m-d H:i:s',$join['register_date']):'无';
    $g['shop_name']=$shop['shop_name'];
    $g['shop_url']=GetBaseUrl('index','','',$g['supplier_id']);
    
    $g['status']='';
    if($g['is_complete']==2) $g['status']='已结束';
    else if($m_now_time<$g['start_date']) $g['status']='未开始';
    else if($m_now_time>=$g['end_date'] || $g['is_complete']==1) $g['status']='结算中';
    
    if($g['is_complete'] && $join && $join['m_uid']==$m_check_uid) $g['status'].='<font color="red">(中拍)</font>';
    
    $auction_list[]=$g;
}
$page_list=$rowset->link("member.php?action=$action&page=");

require_once template('member_pai');