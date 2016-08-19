<?php
$expire=$today_timestamp-24*3600;
$q=$db->query("SELECT uid FROM `{$tablepre}order_info` WHERE addtime>=$expire");
while ($rtl=$db->fetch_array($q))
{
    $arr_rel=array();
    $q_tmp=$db->query("SELECT g_uid,module,goods_table FROM `{$tablepre}order_goods` WHERE order_id=$rtl[uid]");
    while ($rtl_tmp=$db->fetch_array($q_tmp))
    {
        $idx=$rtl_tmp['g_uid'].'|'.$rtl_tmp['goods_table'].'|'.$rtl_tmp['module'];
        if(!isset($arr_rel[$idx])) $arr_rel[$idx]=array();
        
        foreach ($arr_rel as $key=>$val)
        {
            if($key==$idx) continue;
            $arr_rel[$key][]=array(
                'rel_g_uid'=>$rtl_tmp['g_uid'],
                'rel_module'=>$rtl_tmp['module'],
                'rel_goods_table'=>$rtl_tmp['goods_table']
            );
        }
        unset($rtl_tmp);
    }
    
    foreach ($arr_rel as $key=>$val)
    {
        $arr_g=explode('|',$key);
        if(sizeof($arr_g)!=3) continue;
        foreach ($val as $v)
        {
            $db->query("REPLACE INTO `{$tablepre}order_relation_statistics` (g_uid,goods_table,module,rel_g_uid,rel_goods_table,rel_module,reg_date) 
                        VALUES ('$arr_g[0]','$arr_g[1]','$arr_g[2]','$v[rel_g_uid]','$v[rel_goods_table]','$v[rel_module]','$m_now_time')");
        }
        
        unset($arr_g);
    }
    unset($rtl);
    unset($arr_rel);
}


//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>