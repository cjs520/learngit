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
        global $to_db,$to_tablepre;
        $supplier_id=(int)$supplier_id;
        if($supplier_id<=0) return;
        $member=$to_db->get_one("SELECT m_uid,province,city,county,shop_name,shop_address,sellshow 
	                          FROM `{$to_tablepre}member_shop` 
	                          WHERE m_uid='$supplier_id' 
	                          LIMIT 1");
        if(!$member) return ;
        $to_db->query("DELETE FROM `{$to_tablepre}config` WHERE supplier_id='$supplier_id'");
        $to_db->query("DELETE FROM `{$to_tablepre}badmin_table` WHERE supplier_id='$supplier_id'");
        $to_db->query("DELETE FROM `{$to_tablepre}cycle` WHERE supplier_id='$supplier_id'");
        $to_db->query("DELETE FROM `{$to_tablepre}nav` WHERE supplier_id='$supplier_id'");
        $to_db->query("DELETE FROM `{$to_tablepre}page_table` WHERE supplier_id='$supplier_id'");
        $to_db->query("DELETE FROM `{$to_tablepre}bmain` WHERE supplier_id='$supplier_id'");
        $to_db->query("DELETE FROM `{$to_tablepre}forumlinks_table` WHERE supplier_id='$supplier_id'");
        $to_db->query("DELETE FROM `{$to_tablepre}member_account` WHERE member_uid='$supplier_id'");
        $to_db->free_result();

        $q=$to_db->query("SELECT uid FROM `{$to_tablepre}ship_table` WHERE supplier_id='$supplier_id'");
        $ship_id_list=array();
        while($rtl=$to_db->fetch_array($q)) $ship_id_list[]=$rtl['uid'];
        if(sizeof($ship_id_list)>0)
        {
            $str_ship_id=implode(',',$ship_id_list);
            $to_db->query("DELETE FROM `{$to_tablepre}area_table` WHERE ship_uid IN ($str_ship_id)");
            $to_db->query("DELETE FROM `{$to_tablepre}ship_table` WHERE uid IN ($str_ship_id)");
        }
        $to_db->free_result();

        $to_db->query("DELETE FROM `{$to_tablepre}certi` WHERE supplier_id='$supplier_id'");
        $to_db->free_result();
        
        //删除商品
        $to_db->query("DELETE FROM `{$to_tablepre}goods_combine` WHERE g_uid IN (SELECT uid FROM `{$to_tablepre}goods_table` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}goods_combine` WHERE com_uid IN (SELECT uid FROM `{$to_tablepre}goods_table` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}goods_detail` WHERE g_uid IN (SELECT uid FROM `{$to_tablepre}goods_table` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}gallery` WHERE goods_id IN (SELECT uid FROM `{$to_tablepre}goods_table` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}goods_table` WHERE supplier_id='$supplier_id'");
        $to_db->free_result();
        
        $to_db->query("DELETE FROM `{$to_tablepre}goods_show_detail` WHERE g_uid IN (SELECT uid FROM `{$to_tablepre}goods_show` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}show_gallery` WHERE goods_id IN (SELECT uid FROM `{$to_tablepre}goods_show` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}goods_show` WHERE supplier_id='$supplier_id'");
        $to_db->free_result();
        
        $to_db->query("DELETE FROM `{$to_tablepre}goods_group_detail` WHERE g_uid IN (SELECT uid FROM `{$to_tablepre}goods_group` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}group_gallery` WHERE goods_id IN (SELECT uid FROM `{$to_tablepre}goods_group` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}goods_group` WHERE supplier_id='$supplier_id'");
        $to_db->free_result();
        
        $to_db->query("DELETE FROM `{$to_tablepre}goods_onsale_detail` WHERE g_uid IN (SELECT uid FROM `{$to_tablepre}goods_onsale` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}onsale_gallery` WHERE goods_id IN (SELECT uid FROM `{$to_tablepre}goods_onsale` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}goods_onsale` WHERE supplier_id='$supplier_id'");
        $to_db->free_result();
        
        $to_db->query("DELETE FROM `{$to_tablepre}goods_change_detail` WHERE g_uid IN (SELECT uid FROM `{$to_tablepre}goods_change` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}change_gallery` WHERE goods_id IN (SELECT uid FROM `{$to_tablepre}goods_change` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}goods_change` WHERE supplier_id='$supplier_id'");
        $to_db->free_result();
        
        $to_db->query("DELETE FROM `{$to_tablepre}goods_auction_detail` WHERE g_uid IN (SELECT uid FROM `{$to_tablepre}goods_auction` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}auction_gallery` WHERE goods_id IN (SELECT uid FROM `{$to_tablepre}goods_auction` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}goods_auction_join` WHERE g_uid IN (SELECT uid FROM `{$to_tablepre}goods_auction` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}goods_auction_assure` WHERE g_uid IN (SELECT uid FROM `{$to_tablepre}goods_auction` WHERE supplier_id='$supplier_id')");
        $to_db->query("DELETE FROM `{$to_tablepre}goods_auction` WHERE supplier_id='$supplier_id'");
        $to_db->free_result();
        
        DeleteDir(MVMMALL_ROOT.'/union/shopimg/gallery/'.$supplier_id.'/');
        DeleteDir(MVMMALL_ROOT.'/union/shopimg/user_img/'.$supplier_id.'/');
        
        if($is_del) return ;
        //读入SQL
        $sql_file=$member['sellshow']==2?MVMMALL_ROOT.'/data/sql/supplier_show_sql.sql':MVMMALL_ROOT.'/data/sql/supplier_sql.sql';
        $str_sql=file_get_contents($sql_file);
        $str_sql=str_replace('mvm_',$to_tablepre,$str_sql);
        $str_sql=str_replace('SUPPLIER_ID',strval($supplier_id),$str_sql);
        $arr_sql=explode(chr(13).chr(10).chr(13).chr(10),$str_sql);
        foreach ($arr_sql as $val) if($val!='') $to_db->query(trim($val));
        $to_db->free_result();

        $to_db->query("UPDATE `{$to_tablepre}config` SET cf_value='$member[shop_name]' WHERE cf_name='mm_mall_name' AND supplier_id='$supplier_id'");
        $to_db->query("UPDATE `{$to_tablepre}config` SET cf_value='$member[shop_name]' WHERE cf_name='mm_mall_title' AND supplier_id='$supplier_id'");
        $to_db->query("UPDATE `{$to_tablepre}config` SET cf_value='$member[province]$member[city]$member[couty]$member[shop_address]' WHERE cf_name='mm_mall_address' AND supplier_id='$supplier_id'");

        //删除用户缓存
        DeleteDir(MVMMALL_ROOT.'/union/data/cache/user_cache/'.$supplier_id.'/');

        //新建在线编辑器路径
        mkdir(MVMMALL_ROOT."/union/upload/$supplier_id/image/",0777,true);
        mkdir(MVMMALL_ROOT."/union/upload/$supplier_id/file/",0777,true);

        return;
    }
}//end class shop
?>