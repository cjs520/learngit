<?php
if($wap_user_index || $force_user_index)
{
    require 'wap/include/header.inc.php';
    $save_type=2;
    if($preview) $save_type=1;
    
    $arr_index_data=array();
    $q=$db->query("SELECT level,content FROM `{$tablepre}wap_user_index` WHERE supplier_id='0' AND script='$script' AND save_type='$save_type' ORDER BY level");
    while($rtl=$db->fetch_array($q))
    {
        if($rtl['level']>0 && substr($rtl['content'],0,6)=='cycle:')
        {
            $rtl['content']="<div rel='adcycle_data'>$rtl[content]</div>";
        }
        $arr_index_data[]=$rtl;
    }
    $db->free_result();
    
    if(!$arr_index_data) $arr_index_data[0]['content']=$mm_mall_title;
    
    include template('user_index');
    footer(false);
}

$arr_title=$arr_pic=$arr_url=array();
foreach ($AD->GetAd('banner',2,'',$mm_skin_name) as $val)
{
    $arr_title[]=$val['title'];
    $arr_pic[]=$val['pic'];
    $arr_url[]=$val['url'];
}
$titles=implode('|',$arr_title);
$pics=implode('|',$arr_pic);
$urls=implode('|',$arr_url);

//限时抢购
$arr_ms=array();
$cache_file="data/cache/index_ms.php";
$arr_ms=$cache->read_cache($cache_file,'get_ms_goods',array('limit'=>2),'arr_ms');

function get_ms_goods($param)
{
    global $db,$tablepre,$m_now_time;
    $param['limit']=(int)$param['limit'];
    if($param['limit']<=0) $param['limit']=2;
    
    $arr_ms=array();
    $q=$db->query("SELECT go.uid,go.goods_name,go.goods_sale_price,go.goods_file1,go.goods_status,go.goods_stock,go.supplier_id,go.goods_hit,go.start_date,go.end_date,
                          god.goods_market_price 
                   FROM `{$tablepre}goods_onsale` go 
                   LEFT JOIN `{$tablepre}member_shop` ms 
                   ON go.supplier_id=ms.m_uid 
                   LEFT JOIN `{$tablepre}goods_onsale_detail` god 
                   ON god.g_uid=go.uid 
                   WHERE ms.isSupplier=3 AND go.start_date<=$m_now_time AND go.end_date>=$m_now_time 
                   ORDER BY go.start_date ASC 
                   LIMIT $param[limit]");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['goods_market_price']=$rtl['goods_market_price'];

        $rtl['sale_price']=$rtl['goods_sale_price'];
        $rtl['left_time']=$rtl['end_date']-$m_now_time;
        $rtl['shop_url']=GetBaseUrl('index','','',$rtl['supplier_id']);

        if($rtl['goods_stock']<=0) $rtl['sold_out']='sold_out';
        else if($rtl['start_date']>$m_now_time) $rtl['sold_out']='sold_begin';
        else if($rtl['end_date']<$m_now_time) $rtl['sold_out']='sold_over';
        if($rtl['sold_out']) $rtl['btn_cls']='but_gray';

        $rtl=goods_array($rtl);
        $rtl['url']=GetBaseUrl('salegd_detail',$rtl['uid'],'action',$rtl['supplier_id']);

        $rtl['goods_file1']=ProcImgPath($rtl['goods_file1']);
        $arr_ms[]=$rtl;
    }
    $db->free_result();

    return $arr_ms;
}

