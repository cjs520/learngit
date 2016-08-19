<?php
$uid=(int)$uid;
$sellshow=(int)$sellshow;
$arr_rtl=array('err'=>'','attr_val'=>array(),'attr_store'=>array());
if(isset($g_type))
{
    $g_type=(int)$g_type;
    $goods_table=goods_table($g_type);
    $detail_table=goods_detail_table($g_type);
}
else
{
    $goods_table=$sellshow==1?"{$tablepre}goods_table":"{$tablepre}goods_show";
    $detail_table=$sellshow==1?"{$tablepre}goods_detail":"{$tablepre}goods_show_detail";
}

$detail=$db->get_one("SELECT attr_val,attr_store FROM `$detail_table` WHERE g_uid='$uid' LIMIT 1");
if($detail && $detail['attr_val'])
{
    $arr_tmp=SplitAttr($detail['attr_val']);
    if($arr_tmp && is_array($arr_tmp))
    {
        $arr_rtl['attr_val']=$arr_tmp;
        $arr_rtl['attr_store']=SplitAttrStore($detail['attr_store']);
    }
}
else $arr_rtl['err']='无属性';

exit(json_encode($arr_rtl));