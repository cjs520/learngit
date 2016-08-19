<?php
do
{
    !$action && $action='index';
    if($action!='index') break;
    if(!$wap_user_index && !$force_user_index) break;
    $save_type=2;
    if($preview) $save_type=1;
    
    if($save_type==2)
    {
        $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}wap_user_index` WHERE supplier_id='0' AND script='$script'");
        if($rtl['cnt']<=0) break;
    }
    
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
    
}while(false);
