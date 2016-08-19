<?php
$arr_goods=array();
if($m_check_id)
{
    $q=$db->query("SELECT f_uid,goods_table,module FROM `{$tablepre}favorite` WHERE m_uid='$m_check_uid' AND t='1' LIMIT 6");
    while ($rtl=$db->fetch_array($q))
    {
        $g=$db->get_one("SELECT goods_name,goods_file1,supplier_id,goods_sale_price,type,goods_stock FROM `$rtl[goods_table]` WHERE uid='$rtl[f_uid]' LIMIT 1");
        if(!$g) continue;
        $detail_table=goods_detail_table($g['type']);
        $detail=$db->get_one("SELECT attr_val,attr_store FROM `$detail_table` WHERE g_uid='$rtl[f_uid]' LIMIT 1");
        
        $arr_goods[]=array(
            'uid'=>$rtl['f_uid'],
            'gt'=>$rtl['goods_table'],
            'goods_file1'=>ProcImgPath($g['goods_file1']),
            'url'=>GetBaseUrl($rtl['module'],$rtl['f_uid'],'action',$g['supplier_id']),
            'goods_name'=>$g['goods_name'],
            'goods_sale_price'=>currency($g['goods_sale_price']),
            'attr_val'=>$detail['attr_val'],
            'attr_store'=>$detail['attr_store'],
            'goods_stock'=>$g['goods_stock'],
            'module'=>$rtl['module'],
            'goods_table'=>$rtl['goods_table']
        );
    }
    $db->free_result();
}
else
{
    $q=$db->query("SELECT uid,goods_name,goods_file1,supplier_id,goods_sale_price,type,goods_stock 
                   FROM `{$tablepre}goods_table` 
                   ORDER BY uid DESC LIMIT 6");
    while ($rtl=$db->fetch_array($q))
    {
        $detail_table=goods_detail_table($rtl['type']);
        $detail=$db->get_one("SELECT attr_val,attr_store FROM `$detail_table` WHERE g_uid='$rtl[uid]' LIMIT 1");
        
        $rtl['attr_val']=$detail['attr_val'];
        $rtl['attr_store']=$detail['attr_store'];
        $rtl['gt']="{$tablepre}goods_table";
        $rtl['goods_file1']=ProcImgPath($rtl['goods_file1']);
        $rtl['url']=GetBaseUrl('product',$rtl['uid'],'action',$rtl['supplier_id']);
        $rtl['goods_sale_price']=currency($rtl['goods_sale_price']);
        $rtl['module']='product';
        $rtl['goods_table']="{$tablepre}goods_table";
        unset($rtl['supplier_id']);
        unset($rtl['type']);
        $arr_goods[]=$rtl;
    }
    $db->free_result();
}

exit(json_encode($arr_goods));