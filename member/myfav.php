<?php
$t=(int)$t;
$arr_fav=array();
$f_uid=(int)$f_uid;
if($act=='del')
{
    $db->query("DELETE FROM `{$tablepre}favorite` WHERE m_uid='$m_check_uid' AND t='$t' AND f_uid='$f_uid'");
    exit;
}


require_once 'include/pager.class.php';
$rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}favorite` WHERE m_uid='$m_check_uid' AND t='$t'");
$total_count = (int)$rtl['cnt'];
$list_num = 10;
$rowset = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$q=$db->query("SELECT f_uid,module,goods_table,t 
               FROM `{$tablepre}favorite` FORCE INDEX (`m_uid`)
               WHERE m_uid='$m_check_uid' AND t='$t' 
               ORDER BY f_uid DESC 
               LIMIT $from_record,$list_num");
while($rtl=$db->fetch_array($q))
{
    if($t==0)
    {
        $shop=$db->get_one("SELECT shop_name,up_logo FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[f_uid]' LIMIT 1");
        if(!$shop) continue;
        $shop['url']=GetBaseUrl('index','','',$rtl['f_uid']);
        $shop['up_logo']=ProcImgPath($shop['up_logo'],'logo');
        
        $rtl=array_merge($rtl,$shop);
    }
    else if($t==1)
    {
        $goods=$db->get_one("SELECT goods_name,goods_sale_price,goods_file1,supplier_id FROM `$rtl[goods_table]` WHERE uid='$rtl[f_uid]' LIMIT 1");
        $goods['goods_file1']=ProcImgPath($goods['goods_file1']);
        $goods['goods_sale_price']=currency($goods['goods_sale_price']);
        $goods['url']=GetBaseUrl($rtl['module'],$rtl['f_uid'],'action',$goods['supplier_id']);
        
        $rtl=array_merge($rtl,$goods);
    }
    
    $arr_fav[]=$rtl;
}
$page_list=$rowset->link("member.php?action=$action&page=");

require_once 'header.php';
include_once template($t==0?'member_fav':'member_fav_g');