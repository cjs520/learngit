<?php
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';
include 'data/malldata/category.config.php';

$str_sel='';
$cat_id=(int)$_GET['cat_id'];
if($cat_id<=0) $cat_id=0;

$no_select=false;
$parents=array();
if($cat_id==0 || !isset($uid_2_pid[$cat_id])) $no_select=true;
else
{
    $parents=get_parents($cat_id,$uid_2_pid);
    if(!$parents || $parents[0]!=0) $no_select=true;
}

if($no_select)
{
    $arr_data=array(0=>'--请选择--');
    $q=$db->query("SELECT uid,category_name FROM `{$tablepre}category` WHERE category_id='0' AND supplier_id='0'");
    while($rtl=$db->fetch_array($q)) $arr_data[$rtl['uid']]=$rtl['category_name'];
    $str_sel=drop_menu($arr_data,'goods_cat[]');
}
else
{
    $parents_length=sizeof($parents);
    $node=null;
    for($i=0;$i<$parents_length;$i++)
    {
        if($i==0) $node=$cat[0];
        else $node=$node['child'][$parents[$i]];

        $arr_data=array(0=>'--请选择--');
        foreach ($node['child'] as $key=>$val)
        {
            $arr_data[$val['data']['uid']]=$val['data']['category_name'];
        }
        if(sizeof($arr_data)>1) $str_sel.=' '.drop_menu($arr_data,'goods_cat[]',$parents[$i+1]);
    }
}
exit($str_sel);