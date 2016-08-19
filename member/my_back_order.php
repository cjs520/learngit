<?php
require_once 'include/pager.class.php';
$arr_status=array(-1=>'拒绝',1=>'等待卖家审核',2=>'等待买家填写',3=>'等待卖家审核',4=>'完成');

if($step==1)
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT info1,info2 FROM `{$tablepre}order_back` WHERE uid='$uid' AND m_id='$m_check_id' LIMIT 1");
    do
    {
        if(!$rtl) break;
        $rtl['info1']=unserialize($rtl['info1']);
        $rtl['info2']=unserialize($rtl['info2']);
        if($rtl['info1']) file_unlink($rtl['info1']['img']);
        if($rtl['info2']) file_unlink($rtl['info2']['img']);
        
        $db->query("DELETE FROM `{$tablepre}order_back` WHERE uid='$uid' AND m_id='$m_check_id'");
        $db->free_result();
    }while (0);
    
    exit;
}

$arr_back=array();
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_back` WHERE m_id='$m_check_id'");
$total_count = (int)$rtl['cnt'];
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$favorite_list=array();
$q=$db->query("SELECT uid,ordersn,og_uid,g_uid,module,goods_table,status,supplier_id,reject,info1,back_address 
               FROM `{$tablepre}order_back` 
               WHERE m_id='$m_check_id' 
               ORDER BY register_date DESC 
               LIMIT $from_record,$list_num");
while($rtl=$db->fetch_array($q))
{
    $g=$db->get_one("SELECT goods_name,goods_file1 
                     FROM `$rtl[goods_table]` 
                     WHERE uid='$rtl[g_uid]' 
                     LIMIT 1");
    $g['goods_file1']=ProcImgPath($g['goods_file1']);
    $g['goods_url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
    
    $rtl=array_merge($g,$rtl);
    $rtl['info1']=unserialize($rtl['info1']);
    $rtl['info1']['money']=currency($rtl['info1']['money']);
    $rtl['status_txt']=$arr_status[$rtl['status']];
    !$rtl['back_address'] && $rtl['back_address']='无';
    $arr_back[]=$rtl;
}
$page_list=$rowset->link("member.php?action=$action&page=");
$db->free_result();

require 'header.php';
include_once template('member_my_order_back');