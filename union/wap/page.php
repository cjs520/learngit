<?php
if($wap_user_index || $force_user_index)
{
    $save_type=2;
    if($preview) $save_type=1;
    
    $arr_index_data=array();
    $q=$db->query("SELECT level,content FROM `{$tablepre}wap_user_index` WHERE supplier_id='$page_member_id' AND script='{$script}|{$action}' AND save_type='$save_type' ORDER BY level");
    while($rtl=$db->fetch_array($q))
    {
        if($rtl['level']>0 && substr($rtl['content'],0,6)=='cycle:')
        {
            $rtl['content']="<div rel='adcycle_data'>$rtl[content]</div>";
        }
        else
        {
            $rtl['content']=str_replace('union/shopimg/','shopimg/',$rtl['content']);
        }
        $arr_index_data[]=$rtl;
    }
    $db->free_result();
    
    if(!$arr_index_data) $arr_index_data[0]['content']=$mm_mall_title;
    
    include template('user_index');
    footer(false);
}

