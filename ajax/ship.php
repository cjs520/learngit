<?php
$arr=array('err'=>'','ship_list'=>array());
$province=dhtmlchars(trim($province));
$city=dhtmlchars(trim($city));
$county=dhtmlchars(trim($county));
$filter_sql=" region LIKE '%全国%' ";
if($province) $filter_sql.=" OR region LIKE '%$province%'";
if($city) $filter_sql.=" OR region LIKE '%$city%'";
if($county) $filter_sql.=" OR region LIKE '%$county%'";

do
{
    if(!is_array($arr_kg) || !is_array($arr_price))
    {
        $arr['err']='传递参数错误，请联系管理员';
        break;
    }
    if(!$arr_kg || !$arr_price)
    {
        $arr['err']='传递参数错误，请联系管理员！';
        break;
    }
    if(sizeof($arr_kg)!=sizeof($arr_price))
    {
        $arr['err']='传递参数不匹配，请联系管理员';
        break;
    }
    
    $arr_ship=array();
    foreach ($arr_kg as $key=>$kg)
    {
        $kg=(int)$kg;
        $price=floatval($arr_price[$key]);
        $arr['ship_list'][$key]=array();
        
        $q=$db->query("SELECT ship_uid,config FROM `{$tablepre}area_table` WHERE supplier_id='$key' AND ($filter_sql)");
        while ($rtl=$db->fetch_array($q))
        {
            if(!isset($arr_ship[$rtl['ship_uid']]))
            {
                $arr_ship[$rtl['ship_uid']]=$db->get_one("SELECT uid,class_name,name 
                                                          FROM `{$tablepre}ship_table` WHERE uid='$rtl[ship_uid]' AND supplier_id='$key' 
                                                          LIMIT 1");
            }
            if(!$arr_ship[$rtl['ship_uid']]) continue;
            $cls_name=$arr_ship[$rtl['ship_uid']]['class_name'];
            $cls_path="include/shipping/$cls_name.class.php";
            if(!file_exists($cls_path)) continue;
            require_once $cls_path;
            $o_ship=new $cls_name(unserialize($rtl['config']));
            $ship_price=$kg<=0?0:$o_ship->exes($kg,$price);
            
            $arr['ship_list'][$key][]=array(
                'uid'=>$arr_ship[$rtl['ship_uid']]['uid'],
                'name'=>$arr_ship[$rtl['ship_uid']]['name'],
                'price'=>$ship_price
            );
        }
        
    }
}while (0);

echo json_encode($arr);
exit;