<?php

/**
 * MVM_MALL 网上商店系统 首页文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: wap_user_index.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

$arr_page=array(
    'index'=>'首页',
    'page'=>'自定义页面'
);

if(!in_array(BrowserType(),array('firefox','chrome')))
{
    include template('sadmin_browser_tip');
}
else if($action=='page_list')
{
    $rtl=$db->get_one("SELECT script FROM `{$tablepre}wap_user_index_list` WHERE supplier_id='$page_member_id' LIMIT 1");
    $arr_script=array();
    if(!$rtl)
    {
        $arr_script=array('index');
        $row=array(
            'supplier_id'=>$page_member_id,
            'script'=>serialize($arr_script)
        );
        $db->insert("`{$tablepre}wap_user_index_list`",$row);
    }
    else
    {
        $arr_script=unserialize($rtl['script']);
    }
    
    $arr_rtl=array();
    foreach ($arr_script as $val)
    {
        $rtl=array(0=>$val);
        if(strpos($val,'page')===0)
        {
            $rtl[1]=$arr_page['page'];
            $arr_tmp=explode('|',$val);
            $rtl[2]=$arr_tmp[0].'.php?action='.$arr_tmp[1];
        }
        else
        {
            $rtl[1]=$arr_page[$val];
            $rtl[2]=$val.'.php';
        }
        
        
        $arr_rtl[]=$rtl;
    }
    
    $sel_page=drop_menu($arr_page,'user_page');
    
    require_once template('sadmin_wap_user_index_list');
    footer();
}
else if($action=='add_page')
{
    $page_name=dhtmlchars($page_name);
    $page_param=dhtmlchars($page_param);
    if(!key_exists($page_name,$arr_page)) exit('ERR:添加的页面出错，请联系管理员');
    if($page_name=='page' && !preg_match('/^[a-zA-Z0-9]+$/',$page_param)) exit('ERR:请填写正确的页面参数');
    if($page_param) $page_name.='|'.$page_param;
    
    $rtl=$db->get_one("SELECT script FROM `{$tablepre}wap_user_index_list` WHERE supplier_id='$page_member_id' LIMIT 1");
    if(!$rtl) exit('ERR:检索不到指定记录，无法添加');
    $arr_script=unserialize($rtl['script']);
    if(!is_array($arr_script)) $arr_script=array();
    if(sizeof($arr_script)>=20) exit('ERR:每个人自定义页面只能有20个，无法添加');
    $key=array_search($page_name,$arr_script,true);
    if($key!==false) exit('ERR:您指定的页面已存在，无法添加');
    array_push($arr_script,$page_name);
    $row=array(
        'script'=>serialize($arr_script)
    );
    $db->update("`{$tablepre}wap_user_index_list`",$row," supplier_id='$page_member_id' ");
    exit('OK:添加成功');
}
else if($action=='del_page')
{
    $uid=dhtmlchars($uid);
    if($uid=='index') exit('ERR:自定义首页无法删除');
    $rtl=$db->get_one("SELECT script FROM `{$tablepre}wap_user_index_list` WHERE supplier_id='$page_member_id' LIMIT 1");
    if(!$rtl) exit('ERR:检索不到指定记录，无法删除');
    $arr_script=unserialize($rtl['script']);
    $key=array_search($uid,$arr_script,true);
    if($key===false) exit('ERR:检索不到指定记录，无法删除!');
    
    unset($arr_script[$key]);
    $row=array(
        'script'=>serialize($arr_script)
    );
    $db->update("`{$tablepre}wap_user_index_list`",$row," supplier_id='$page_member_id' ");
    exit('OK:删除成功');
}
else if($action=='list')
{
    $shop_url=GetBaseUrl('index','','',$page_member_id);
    $pos=strrpos($shop_url,'index');
    $shop_url=substr($shop_url,0,$pos);
    $ucfg['mm_wx_shop_logo']=ProcImgPath($ucfg['mm_wx_shop_logo'],'logo');
    
    $uid=dhtmlchars($uid);
    $q=$db->query("SELECT level,content FROM `{$tablepre}wap_user_index` WHERE supplier_id='$page_member_id' AND script='$uid' AND save_type='2' ORDER BY level");
    while($rtl=$db->fetch_array($q))
    {
        if($rtl['level']>0 && substr($rtl['content'],0,6)=='cycle:')
        {
            $rtl['content']="<div rel='adcycle_data'>$rtl[content]</div>";
        }
        $arr_index_data[]=$rtl;
    }
    $db->free_result();
    
    if(!$arr_index_data) $arr_index_data[0]['content']=$shop_file['shop_name'];
    $cat_menu = cat_menu('cat_uid',0,$page_member_id);
    
	include template('sadmin_wap_user_index');
}
else if($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $script=dhtmlchars($_POST['script']);
        $save_type=(int)$save_type;
        $db->query("DELETE FROM `{$tablepre}wap_user_index` WHERE supplier_id='$page_member_id' AND script='$script' AND save_type='$save_type'");
        foreach ($arr_fields as $key=>$val)
        {
            $key=(int)$key;
            $row=array(
                'supplier_id'=>$page_member_id,
                'script'=>$script,
                'level'=>$key,
                'content'=>$val,
                'save_type'=>$save_type
            );
            $db->insert("`{$tablepre}wap_user_index`",$row);
        }
    }
    exit('OK:保存成功');
}
else if($action=='upload')
{
    require_once 'include/upfile.class.php';
    $rtl=0;
    $file_path='';
    if($_FILES["pic"]['name'])
    {
        $rowset = new upfile('jpg,jpeg,gif,png',"union/shopimg/user_img/$page_member_id/image/",350*1024);
        $file_path = $rowset->upload('pic');
        $file_path=ProcImgPath($file_path);
        $dir_name=dirname($file_path);
        $pic_name=basename($file_path);
        
        $new_file_path=$dir_name.'/wap_'.$pic_name;
        rename($file_path,$new_file_path);
        $file_path=$new_file_path;
        $rtl=1;
    }
    echo CreateUploadResult($rtl,$file_path);
    exit;
}
else if($action=='list_img')
{
    //union\shopimg\user_img\1\image
    $arr_files=glob("union/shopimg/user_img/$page_member_id/image/wap_*.*");
    $arr_files=array_reverse($arr_files);
    $size=sizeof($arr_files);
    if($size>50)
    {
        for($i=50;$i<$size;$i++) unset($arr_files[$i]);
    }
    
    exit(json_encode($arr_files));
}
else if($action=='search_goods')
{
    $goods_type=(int)$goods_type;
    $keywords=dhtmlchars($keywords);
    $goods_table=goods_table($goods_type);
    $detail_script=goods_detail_script($goods_type);
    
    $arr_goods=array();
    $price_fields=$goods_type==7?'start_price AS goods_sale_price':'goods_sale_price';
    $q=$db->query("SELECT uid,goods_name,goods_file1,supplier_id,$price_fields 
                   FROM `$goods_table` 
                   WHERE goods_name LIKE '%$keywords%' AND supplier_id='$page_member_id'
                   ORDER BY uid DESC 
                   LIMIT 20");
    while($rtl=$db->fetch_array($q))
    {
        $rtl['url']=GetBaseUrl($detail_script,$rtl['uid'],'action',$rtl['supplier_id']);
        $rtl['goods_sale_price']=currency($rtl['goods_sale_price']);
        if($goods_type==8) $rtl['goods_sale_price']='展示商品';
        $rtl['goods_file1']=ProcImgPath($rtl['goods_file1']);
        unset($rtl['uid']);
        unset($rtl['supplier_id']);
        $arr_goods[]=$rtl;
    }
    $db->free_result();
    
    echo json_encode($arr_goods);
    exit;
}

function CreateUploadResult($result,$file_path)
{
    $xml="<upload><result>$result</result><path>$file_path</path></upload>";
    return $xml;
}
