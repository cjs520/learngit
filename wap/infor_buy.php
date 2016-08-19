<?php
$arr_title=$arr_pic=$arr_url=array();
foreach ($AD->GetAd('wap_infor_buy',2,'','cycle') as $val)
{
    $arr_title[]=$val['title'];
    $arr_pic[]=$val['pic'];
    $arr_url[]=$val['url'];
}
$titles=implode('|',$arr_title);
$pics=implode('|',$arr_pic);
$urls=implode('|',$arr_url);