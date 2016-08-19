<?php
$cat_id=(int)$cat_id;
$arr_data=array(0=>'--请选择--');
$str_sel='';
$q=$db->query("SELECT uid,category_name FROM `{$tablepre}category` WHERE category_id='$cat_id' AND supplier_id='0'");
while($rtl=$db->fetch_array($q)) $arr_data[$rtl['uid']]=$rtl['category_name'];
if(sizeof($arr_data)>1)
{
    $str_sel=drop_menu($arr_data,'goods_cat[]');
}
echo ' ',$str_sel;
exit;