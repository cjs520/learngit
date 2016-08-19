<?php
require_once 'include/cart.class.php';
$attr=dhtmlchars($attr);
$g_uid=(int)$g_uid;
$goods_table=dhtmlchars($gt);
$module=dhtmlchars($module);
$ps_num=(int)$ps_num;
$refer_g_uid=(int)$refer_g_uid;    //关联商品
$err='';
$info=array();

$cart=new cart();
if($refer_g_uid>0)    //关联商品处理
{
    do
    {
        if(!is_array($arr_g_uid) || !is_array($arr_ps_num) || !is_array($arr_attr))
        {
            $err='数据格式错误，请联系管理员';
            break;
        }
        if(sizeof($arr_g_uid)!=sizeof($arr_ps_num) || sizeof($arr_g_uid)!=sizeof($arr_attr))
        {
            $err='数据数量错误，请联系管理员';
            break;
        }
        if(sizeof($arr_g_uid)<2)
        {
            $err='组合数量错误，请联系管理员';
            break;
        }
        if(!in_array($refer_g_uid,$arr_g_uid))
        {
            $err='未选择主商品';
            break;
        }
        
        foreach ($arr_g_uid as $key=>$val)
        {
            $rtl=$cart->add($val,$goods_table,(int)$arr_ps_num[$key],dhtmlchars($arr_attr[$key]),$module,$refer_g_uid);
            if(!$rtl)
            {
                $err=$cart->get_last_error();
                break;
            }
        }
        if(!$err) $info=$cart->get_simple_info();
    }while (0);
}
else if($step=='bat')
{
    do
    {
        if(!is_array($arr_ps_num))
        {
            $err='数据格式错误，请联系管理员';
            break;
        }
        if(sizeof($arr_ps_num)<1)
        {
            $err='请选择要批发的商品';
            break;
        }
        if(is_array($arr_attr) && sizeof($arr_ps_num)!=sizeof($arr_attr))
        {
            $err='数据格式错误，请联系管理员';
            break;
        }
        $rtl=$cart->add_bat_cart($g_uid,$goods_table,$arr_ps_num,$arr_attr,$module);
        if(!$rtl) $err=$cart->get_last_error();
        else $info=$cart->get_simple_info();
    }while (0);
}
else
{
    $rtl=$cart->add($g_uid,$goods_table,$ps_num,$attr,$module,0,$preorder);
    if(!$rtl) $err=$cart->get_last_error();
    else $info=$cart->get_simple_info();
}

echo json_encode(array('err'=>$err,'info'=>$info));