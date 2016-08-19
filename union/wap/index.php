<?php
if($wap_user_index || $force_user_index)
{
    include 'wap/include/header.inc.php';
    $save_type=2;
    if($preview) $save_type=1;
    
    $arr_index_data=array();
    $q=$db->query("SELECT level,content FROM `{$tablepre}wap_user_index` 
                   WHERE supplier_id='$page_member_id' AND script='$script' AND save_type='$save_type' 
                   ORDER BY level");
    while($rtl=$db->fetch_array($q))
    {
        if($rtl['level']>0 && substr($rtl['content'],0,6)=='cycle:')
        {
            $rtl['content']="<div rel='adcycle_data'>$rtl[content]</div>";
        }
        else
        {
            $rtl['content']=filter_editor_img($rtl['content']);
            $rtl['content']=str_replace(array('url(&quot;union/'),array('url(&quot;'),$rtl['content']);
        }
        $arr_index_data[]=$rtl;
    }
    $db->free_result();
    
    if(!$arr_index_data) $arr_index_data[0]['content']=$shop_file['shop_name'];
    
    include template('user_index');
    footer(false);
}

$banner_array=$cache->get_cache('banner_array',$page_member_id);

$arr_title=$arr_pic=$arr_url=array();
for($i=0;$i<6;$i++)
{
    $banner=$banner_array['wap_banner'][$i];
    if(!$banner) break;
    if(!$banner['banner_file1'] || !file_exists($banner['banner_file1'])) $banner['banner_file1']='images/noimages/noproduct.jpg';
    $arr_title[]='';
    $arr_pic[]=$banner['banner_file1'];
    $arr_url[]=$banner['url'];
}

$titles=implode('|',$arr_title);
$pics=implode('|',$arr_pic);
$urls=implode('|',$arr_url);