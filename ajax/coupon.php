<?php
$arr_rtl=array();

$page=(int)$page;

do
{
    $handout_type=0;
    if($rel=='exchange_coupon') $handout_type=1;
    else if($rel=='free_coupon') $handout_type=0;
    else $handout_type=2;
    $search_sql=" od>10000 AND end_date>$m_now_time AND handout_type=$handout_type";
    
    require_once MVMMALL_ROOT.'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}coupon_cat",$search_sql);
    $list_num = 10;
    $total_page=ceil($total_count/$list_num);
    if($page<1) $page=1;
    if($page>$total_page) $page=$total_page;
    
    $arr_coupon=array();
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q=$db->query("SELECT uid,supplier_id,end_date,discount,coupon_img,price_lbound,handout_type,sale_price 
                   FROM `{$tablepre}coupon_cat` 
                   WHERE $search_sql
                   ORDER BY od DESC 
                   LIMIT $from_record,$list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['coupon_img']=ProcImgPath($rtl['coupon_img']);
        $rtl['url']=GetBaseUrl('coupon','list','action',$rtl['supplier_id']);
        $rtl['end_date']=date('Y年m月d日',$rtl['end_date']);
        $rtl['discount']=round($rtl['discount']);
        $rtl['sale_price']=(int)$rtl['sale_price'];

        $shop=$db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$rtl[supplier_id]' LIMIT 1");
        if($shop) $rtl=array_merge($rtl,$shop);

        $arr_coupon[]=$rtl;
    }
    $arr_rtl['coupon']=$arr_coupon;
    $arr_rtl['page']=$page;
    
}while (0);

echo json_encode($arr_rtl);
exit;