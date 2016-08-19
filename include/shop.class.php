<?php

/**
 * MVM_MALL 网上商店系统  文件上传类
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-03 $
 * $Id: shop.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
!defined('MVMMALL') && exit('Access Denied');
class shop
{
    public static function CreateSupplierFile($supplier_id,$is_del=false)    //生成会员资料
    {
        global $db,$tablepre,$_URL,$cache,$db_mem_cache;
        $supplier_id=(int)$supplier_id;
        if($supplier_id<=0) return;
        $member=$db->get_one("SELECT m_uid,province,city,county,shop_name,shop_address,sellshow 
	                          FROM `{$tablepre}member_shop` 
	                          WHERE m_uid='$supplier_id' 
	                          LIMIT 1");
        if(!$member) return ;
        $member=daddslashes($member);
        $db->query("DELETE FROM `{$tablepre}config` WHERE supplier_id='$supplier_id'");
        $db->query("DELETE FROM `{$tablepre}badmin_table` WHERE supplier_id='$supplier_id'");
        $db->query("DELETE FROM `{$tablepre}cycle` WHERE supplier_id='$supplier_id'");
        $db->query("DELETE FROM `{$tablepre}nav` WHERE supplier_id='$supplier_id'");
        $db->query("DELETE FROM `{$tablepre}page_table` WHERE supplier_id='$supplier_id'");
        $db->query("DELETE FROM `{$tablepre}bmain` WHERE supplier_id='$supplier_id'");
        $db->query("DELETE FROM `{$tablepre}forumlinks_table` WHERE supplier_id='$supplier_id'");
        $db->query("DELETE FROM `{$tablepre}member_account` WHERE member_uid='$supplier_id'");
        $db->query("DELETE FROM `{$tablepre}member_shop_manager` WHERE shop_m_uid='$supplier_id'");
        $db->free_result();

        $q=$db->query("SELECT uid FROM `{$tablepre}ship_table` WHERE supplier_id='$supplier_id'");
        $ship_id_list=array();
        while($rtl=$db->fetch_array($q)) $ship_id_list[]=$rtl['uid'];
        if(sizeof($ship_id_list)>0)
        {
            $str_ship_id=implode(',',$ship_id_list);
            $db->query("DELETE FROM `{$tablepre}area_table` WHERE ship_uid IN ($str_ship_id)");
            $db->query("DELETE FROM `{$tablepre}ship_table` WHERE uid IN ($str_ship_id)");
        }
        $db->free_result();

        $q=$db->query("SELECT b_img,s_img FROM `{$tablepre}certi` WHERE supplier_id='$supplier_id'");
        while($rtl=$db->fetch_array($q))
        {
            if($rtl['b_img']) file_unlink($rtl['b_img'],'buctket');
            if($rtl['s_img']) file_unlink($rtl['s_img'],'buctket');
        }
        $db->query("DELETE FROM `{$tablepre}certi` WHERE supplier_id='$supplier_id'");
        $db->free_result();
        $db_mem_cache->op(0,'Id2Domain',$supplier_id,'',db_memory_cache::DELETE );
        
        //删除商品
        $db->query("DELETE FROM `{$tablepre}goods_combine` WHERE g_uid IN (SELECT uid FROM `{$tablepre}goods_table` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}goods_combine` WHERE com_uid IN (SELECT uid FROM `{$tablepre}goods_table` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}goods_detail` WHERE g_uid IN (SELECT uid FROM `{$tablepre}goods_table` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}gallery` WHERE goods_id IN (SELECT uid FROM `{$tablepre}goods_table` WHERE supplier_id='$supplier_id')");

        $db->query("DELETE FROM `{$tablepre}goods_storage` WHERE goods_uid IN (SELECT uid FROM `{$tablepre}goods_table` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}goods_table` WHERE supplier_id='$supplier_id'");

        $db->free_result();
        
        $db->query("DELETE FROM `{$tablepre}goods_show_detail` WHERE g_uid IN (SELECT uid FROM `{$tablepre}goods_show` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}show_gallery` WHERE goods_id IN (SELECT uid FROM `{$tablepre}goods_show` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}goods_show` WHERE supplier_id='$supplier_id'");
        $db->free_result();
        
        $db->query("DELETE FROM `{$tablepre}goods_group_detail` WHERE g_uid IN (SELECT uid FROM `{$tablepre}goods_group` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}group_gallery` WHERE goods_id IN (SELECT uid FROM `{$tablepre}goods_group` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}goods_group` WHERE supplier_id='$supplier_id'");
        $db->free_result();
        
        $db->query("DELETE FROM `{$tablepre}goods_onsale_detail` WHERE g_uid IN (SELECT uid FROM `{$tablepre}goods_onsale` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}onsale_gallery` WHERE goods_id IN (SELECT uid FROM `{$tablepre}goods_onsale` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}goods_onsale` WHERE supplier_id='$supplier_id'");
        $db->free_result();
        
        $db->query("DELETE FROM `{$tablepre}goods_change_detail` WHERE g_uid IN (SELECT uid FROM `{$tablepre}goods_change` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}change_gallery` WHERE goods_id IN (SELECT uid FROM `{$tablepre}goods_change` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}goods_change` WHERE supplier_id='$supplier_id'");
        $db->free_result();
        
        $db->query("DELETE FROM `{$tablepre}goods_auction_detail` WHERE g_uid IN (SELECT uid FROM `{$tablepre}goods_auction` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}auction_gallery` WHERE goods_id IN (SELECT uid FROM `{$tablepre}goods_auction` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}goods_auction_join` WHERE g_uid IN (SELECT uid FROM `{$tablepre}goods_auction` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}goods_auction_assure` WHERE g_uid IN (SELECT uid FROM `{$tablepre}goods_auction` WHERE supplier_id='$supplier_id')");
        $db->query("DELETE FROM `{$tablepre}goods_auction` WHERE supplier_id='$supplier_id'");
        $db->free_result();
   

        //删除object
       $objects=array();
       require_once __DIR__ . '/oss-sdk/Common.php';
       $bucket = Common::getBucketName();
       $ossClient = Common::getOssClient();
       $objects=delAllObjects($ossClient,$bucket,'union/shopimg/gallery/'.$supplier_id.'/');
       $objects=delAllObjects($ossClient,$bucket,'union/shopimg/user_img/'.$supplier_id.'/');
       if(count($objects)>0) $ossClient->deleteObjects($bucket,$objects);
       //@end 
    
        if($is_del) return ;
        //读入SQL
        $sql_file=$member['sellshow']==2?'data/sql/supplier_show_sql.sql':'data/sql/supplier_sql.sql';
        $str_sql=file_get_contents($sql_file);
        $str_sql=str_replace('mvm_',$tablepre,$str_sql);
        $str_sql=str_replace('SUPPLIER_ID',strval($supplier_id),$str_sql);
        $arr_sql=explode(chr(13).chr(10).chr(13).chr(10),$str_sql);
        foreach ($arr_sql as $val) if($val!='') $db->query(trim($val));
        $db->free_result();

        $db->query("UPDATE `{$tablepre}config` SET cf_value='$member[shop_name]' WHERE cf_name='mm_mall_name' AND supplier_id='$supplier_id'");
        $db->query("UPDATE `{$tablepre}config` SET cf_value='$member[shop_name]' WHERE cf_name='mm_mall_title' AND supplier_id='$supplier_id'");
        $db->query("UPDATE `{$tablepre}config` SET cf_value='$member[province]$member[city]$member[couty]$member[shop_address]' WHERE cf_name='mm_mall_address' AND supplier_id='$supplier_id'");

        //删除用户缓存
        DeleteDir('union/data/cache/user_cache/'.$supplier_id.'/');

        //新建在线编辑器路径

        //mkdir("union/upload/$supplier_id/image/",0777,true);
        ///mkdir("union/upload/$supplier_id/file/",0777,true);

        return;
    }
}//end class shop
function delAllObjects($ossClient,$bucket,$prefix)
{
    global $objects;

    $delimiter = '/';
    $nextMarker = '';
    $maxkeys = 1000;
    while (true) {
        $options = array(
            'delimiter' => $delimiter,
            'prefix' => $prefix,
            'max-keys' => $maxkeys,
            'marker' => $nextMarker,
        );
        try {
            $listObjectInfo = $ossClient->listObjects($bucket,$options);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        // 得到nextMarker，从上一次listObjects读到的最后一个文件的下一个文件开始继续获取文件列表
        $nextMarker = $listObjectInfo->getNextMarker();
        $listObject = $listObjectInfo->getObjectList();
        $listPrefix = $listObjectInfo->getPrefixList();
         
        //递归获得
        $prefixlist=$listObjectInfo->getPrefixList();
        $nums= count($prefixlist);
        if($nums>0){
            for ($i=0;$i<$nums;$i++){
                $rt=array_values((array)$prefixlist[$i]);
                // echo "$rt[0]<br/>";
                 $objects[]=$rt[0];
                delAllObjects($ossClient,$bucket,$rt[0]);//往上递归
            
            }
        }
        //@end
     
        $total_count = count($listObject);
        for($i=0;$i<$total_count;$i++){
            $rs=array_values((array)$listObject[$i]);
             $objects[]=$rs[0];
            //$ossClient->deleteObject($bucket,$rs[0]);//删除对象
            //echo "$rs[0]</br>";
        }
        if ($nextMarker === '') {
            break;
        }
    }
   return  $objects;
}

    ?>