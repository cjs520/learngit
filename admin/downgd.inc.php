<?php

/**
 * MVM_MALL 网上商店系统  商品管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-27 $
 * $Id: goods.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if (!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/pic.class.php';

//商品管理列表
if($action=='list')
{
    require_once 'include/pager.class.php';
    $search_sql = "WHERE true ";
    $order_sql= 'ORDER BY uid DESC';
    if((int)$supplier_id>0)
    {
        $search_sql.=" AND supplier_id='$supplier_id' ";
        $order_sql=" ORDER BY register_date DESC";
    }
    
    if($ps_subject)
    {
        $search_sql.=" AND goods_name LIKE '%$ps_subject%'";
        $order_sql='';
        $ps_subject_txt=$ps_subject;
        $ps_subject=urlencode($ps_subject);
    }
    
    if($ps_code)
    {
        $search_sql.="AND goods_code='$ps_code'";
        $order_sql='';
        $ps_code_txt=$ps_code;
        $ps_code=urlencode($ps_code);
    }
    
    if($od=='price') $order_sql=' ORDER BY goods_sale_price DESC';
    else if($od=='time') $order_sql=' ORDER BY uid DESC';
    else if($od=='stock') $order_sql=' ORDER BY goods_stock DESC';
    
    $cat_ids=array();
    $cats=array();
    $total_count = $db->counter("{$tablepre}downgoods_table",$search_sql);
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,goods_name,goods_file1,goods_sale_price,goods_code,goods_category,goods_stock 
                     FROM `{$tablepre}downgoods_table` $search_sql $order_sql LIMIT $from_record, $list_num");
    while ($rt = $db->fetch_array($q))
    {
        $rt['edit'] = "admincp.php?module=$module&action=edit&uid=$rt[uid]&prev_url=$prev_url";
        $rt['goods_file1'] = ProcImgPath($rt['goods_file1']);
        
        $cat_ids[]=$rt['goods_category'];
        
        $goods_rt[] = $rt;
    }
    
    if($cat_ids)
    {
        $str_cat_ids=implode(',',$cat_ids);
        $q=$db->query("SELECT uid,category_name FROM `{$tablepre}category` WHERE uid IN ($str_cat_ids)");
        while($rtl=$db->fetch_array($q)) $cats[$rtl['uid']]=$rtl['category_name'];
    }
    
    
    foreach ($goods_rt as $key=>$val)
    {
        $goods_rt[$key]['goods_category']=$cats[$val['goods_category']];
    }
    
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&ps_subject=$ps_subject&ps_code=$ps_code&supplier_id=$supplier_id&od=$od&page=");
    require_once template('downgoods');
    footer();
}
else if($action=='edit')
{
	$goods_id=$uid=(int)$uid;
    $product = $db->get_one("SELECT * FROM `{$tablepre}downgoods_table` WHERE uid='$uid' LIMIT 1");
    if(!$product) show_msg('找不到您指定的商品');
    $product_detail=$db->get_one("SELECT * FROM `{$tablepre}goods_detail` WHERE g_uid='$uid' LIMIT 1");
    if($product_detail) $product=array_merge($product,$product_detail);
    $db->free_result();
    
    $goods_category=$product['goods_category'];
    
    if ($_POST && (int)$setp == 1)
    {
        $goods_category_value=0;
		foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $goods_category_value=$val;
        }
        
        require_once 'include/upfile.class.php';
        require_once 'include/watermark.class.php';
        $f = new upfile('gif,jpg,png,jpeg');
        $img = new Watermark();
        
        $goods_file1=$product['goods_file1'];
        $goods_file2=$product['goods_file2'];
        //上传详细大图
        if($_FILES['goods_file2']['name']!='')
        {
            file_unlink(ProcImgPath($goods_file2));
            $goods_file2 = $f->upload('goods_file2');
            
            if($_FILES['goods_file1']['name']=='')    //没上传小图，大图直接压缩
            {
                file_unlink(ProcImgPath($product['goods_file1']));
                $goods_file1=pic::PicZoom(ProcImgPath($goods_file2),370,370,array(255,255,255),false);
            }
        }

        if ($_FILES['goods_file1']['name']!='')
        {
        	file_unlink(ProcImgPath($goods_file1));
            $goods_file1 = $f->upload('goods_file1');
        }
        

        
        $i_goods_status=0;
        foreach ($goods_status as $val)
        {
            $val=(int)$val;
            $i_goods_status|=$val;
        }
        
        //筛选属性
        $arr_att=array();
        foreach ($filter_attr as $key=>$val)
        {
            $arr_att[]=base64_encode($key.':'.$val);
        }
        $str_att=implode('|',$arr_att);
        
        $type=(int)$type;
        if(!in_array($type,array(0,1,2,3))) $type=0;
        //批发设置
        do
        {
            if($type!=2) break;
            if(!is_array($wholesale) || !is_array($wholesale_price)) break;
            $size=sizeof($wholesale);
            $arr_wholesale=array();
            for($i=0;$i<$size-1;$i+=2)
            {
                $arr_wholesale[]=array((int)$wholesale[$i],(int)$wholesale[$i+1],floatval($wholesale_price[$i/2]));
            }
            $arr_wholesale[]=array((int)$wholesale[$size-1],-1,floatval($wholesale_price[($size-1)/2]));
            usort($arr_wholesale,'sort_wholesale');
            $str_wholesale=serialize($arr_wholesale);
        }while (0);
        
        //同步库存
        $goods_stock=(int)$goods_stock;
        $attr_store=dhtmlchars($attr_store);
        if($attr_store)
        {
            $goods_stock=0;
            $arr_attr_store=SplitAttrStore($attr_store);
            foreach ($arr_attr_store as $val) $goods_stock+=(int)$val[sizeof($val)-1];
            $goods_stock<0 && $goods_stock=0;
        }
        
        //入库
        $row = array(
            'goods_status' => $i_goods_status,
            'goods_category' => $goods_category_value,
            'type'=>$type,
            'goods_stock'=>$goods_stock,
            'goods_code' => $goods_code,
            'goods_name' => $goods_name,
            'goods_brand' => $goods_brand,
            'goods_sale_price' => $goods_sale_price,
            'goods_file1' => str_replace('union/','',$goods_file1),
            'register_date' => $m_now_time,
            'filter_attr'=>$str_att,
        );
        
        $detail_row=array(
            'goods_key'=>dhtmlchars($goods_key),
            'goods_advance'=>dhtmlchars($goods_advance),
            'goods_cost'=>floatval($goods_cost),
            'goods_market_price'=>floatval($goods_market_price),
            'goods_kg'=>floatval($goods_kg),
            'goods_file2'=>str_replace('union/','',$goods_file2),
            'goods_main'=>$goods_main,
            'wholesale_price'=>$str_wholesale,
            'attr_val'=>dhtmlchars($attr_val),
            'attr_store'=>$attr_store
        );
        $db->update("{$tablepre}downgoods_table",$row,$where="uid='$uid'");
        $detail_row['g_uid']=$goods_id;
        $db->replace("`{$tablepre}goods_detail`",$detail_row);
        
        //组合购买
        do
        {
            $db->query("DELETE FROM `{$tablepre}goods_combine` WHERE g_uid='$goods_id'");
            $db->free_result(1);
            if($type!=1) break;
            $row=array(
                'g_uid'=>$goods_id,
                'com_uid'=>$goods_id,
                'price'=>floatval($combine_price1)
            );
            $db->insert("`{$tablepre}goods_combine`",$row);
            for($i=0;$i<4;$i++)
            {
                $combine_uid[$i]=(int)$combine_uid[$i];
                if($combine_uid[$i]<=0) continue;
                if($combine_uid[$i]==$goods_id) continue;
                $row=array(
                    'g_uid'=>$goods_id,
                    'com_uid'=>$combine_uid[$i],
                    'price'=>floatval($combine_price[$i])
                );
                $db->insert("`{$tablepre}goods_combine`",$row);
            }
        }while (0);
        admin_log("编辑下架商品：$goods_name");
        
        show_msg('success',base64_decode($_POST['prev_url']));
    } 
    
    $mm_brand = $cache->get_cache('brand');
    
    $photo = $db->get_all("SELECT imgid,thumb FROM `{$tablepre}gallery` WHERE goods_id='$uid'");//相册数据
    foreach ($photo as $key=>$val)
    {
        $photo[$key]['thumb']=ProcImgPath($photo[$key]['thumb']);
    }
    @extract($product,EXTR_OVERWRITE);
    
    $goods_main=dhtmlchars($goods_main);
    $hot_checked=($goods_status&1)?'checked':'';
    $best_checked=($goods_status&2)?'checked':'';
    $free_delivery_checked=($goods_status&4)?'checked':'';
    
    //商品筛选属性
    $category=$db->get_one("SELECT att_list FROM `{$tablepre}category` WHERE uid='$product[goods_category]' LIMIT 1");
    if($category['att_list'])
    {
        $arr_filter_attr=unserialize($category['att_list']);
        $product_filter_attr=SplitFilterAttr($product['filter_attr']);
        $filter_html='';
        foreach ($arr_filter_attr as $key=>$val)
        {
            $filter_html.="<div class='attr_goods'>";
            $filter_html.="<span>$key</span>";
            $filter_html.="<select name='filter_attr[$key]'>";
            foreach ($val as $k=>$v)
            {
                $selected=$v==$product_filter_attr[$key]?'selected':'';
                $filter_html.="<option value='$v' $selected>$v</option>";
            }
            $filter_html.="</select>";
            $filter_html.="</div>";
        }
    }
    $db->free_result();
    
    $type=(int)$type;
    $str_tmp="type_{$type}_checked";
    $$str_tmp='checked';

    $arr_wholesale=array();
    if($type==2) $arr_wholesale=unserialize($wholesale_price);    //批发
    if($type==1)    //组合购买
    {
        $arr_combine=array();
        $q=$db->query("SELECT com_uid,price FROM `{$tablepre}goods_combine` WHERE g_uid='$product[uid]' LIMIT 5");
        while ($rtl=$db->fetch_array($q))
        {
            if($rtl['com_uid']==$uid) $combine_price1=$rtl['price'];
            else
            {
                $g=$db->get_one("SELECT goods_name,goods_file1 FROM `{$tablepre}downgoods_table` WHERE uid='$rtl[com_uid]' LIMIT 1");
                if(!$g) continue;
                $g['goods_file1']=ProcImgPath($g['goods_file1']);
                $rtl=array_merge($rtl,$g);
                $arr_combine[]=$rtl;
            }
        }
    }
    $db->free_result();
    
    //商品品牌
    $brand_menu = drop_menu($cache->get_cache('brand'),'goods_brand',$goods_brand);
    
    //销售属性
    $arr_attr_val=SplitAttr($attr_val);
    $arr_attr_store=SplitAttrStore($attr_store);
    
    $combine_img=$goods_file1=ProcImgPath($goods_file1);
    
    //获取属性图片分类
    $attr_category=get_dirinfo('images/attr/');
    
    require_once template('downgoods_add');
    footer();
}
else if ($action=='delimg')    //ajax删除单个相册图片
{
    do
    {
        $uid=(int)$uid;
        $gallery = $db->get_one("SELECT * FROM `{$tablepre}gallery` WHERE imgid='$uid' LIMIT 1");
        if(!$gallery) break;
        $g=$db->get_one("SELECT goods_name FROM `{$tablepre}downgoods_change` WHERE uid='$gallery[goods_id]' LIMIT 1");
        if($g) admin_log("删除下架商品相册：$g[goods_name]");
        
        $thumb=ProcImgPath($gallery['thumb']);
        $imgbig=ProcImgPath($gallery['imgbig']);
        file_unlink($thumb);
        file_unlink($imgbig);
    
        $db->query("DELETE FROM `{$tablepre}gallery` WHERE imgid='$uid'"); 
    }while (0);
	exit('OK:删除成功');
}
else if($action=='del')    //删除商品
{
    if (is_numeric($uid))
 	{
 		del_goods($uid);
 	}
    else if (is_array($uid_check) && $uid_check)
 	{
 		foreach ($uid_check as $key=>$val)
 		{
 			$val=(int)$val;
 			if($val>0) $uid_check[$key]=$val;
 			else unset($uid_check[$key]);
 		}
 		$uid_check=array_unique($uid_check);
 		
        foreach ($uid_check as $uid) del_goods($uid);
    }
    exit('OK:批量删除成功');
}
else if($action=='bat_up')
{
    do
    {
        if(!is_array($uid_check)) break;
        if(!$uid_check) break;
        foreach ($uid_check as $key=>$val)
        {
            $val=(int)$val;
            if($val<=0) continue;
            
            $rtl=$db->get_one("SELECT * FROM `{$tablepre}downgoods_table` WHERE uid='$val' LIMIT 1");
    		if(!$rtl) continue;
    		$db->replace("`{$tablepre}goods_table`",$rtl);
    		$db->query("DELETE FROM `{$tablepre}downgoods_table` WHERE uid='$val'");
    		$db->free_result();
        }
        $db->free_result();
    }while (false);
    
    exit('OK：批量上架成功');
}
else if($action=='ajax_edit')
{
    $v=dhtmlchars($_POST['v']);
    $field=dhtmlchars($field);
    $uid=(int)$uid;
    if(in_array($field,array('goods_name','goods_code','goods_sale_price')) && $uid>0)
    {
        $db->query("UPDATE `{$tablepre}downgoods_table` SET `$field`='$v' WHERE uid='$uid'");
        $db->free_result();
    }
    exit;
}
else if($action=='get_att_list')    //获取相关分类下的筛选属性
{
	$cid=(int)$cid;
	$arr_rtl=array();
	$rtl=$db->get_one("SELECT att_list FROM `{$tablepre}category` WHERE uid='$cid' AND supplier_id='0' LIMIT 1");
	if($rtl['att_list']) $arr_rtl=unserialize($rtl['att_list']);
	$db->free_result();
	if(!$arr_rtl) $arr_rtl=array();
	echo json_encode($arr_rtl);
	exit;
}
else if($action=='search_combine')    //搜索商品，用于组合购买
{
    $arr_rtl=array();
    $uid=(int)$uid;
    do
    {
        if($uid<0) break;
        $product=$db->get_one("SELECT supplier_id FROM `{$tablepre}downgoods_table` WHERE uid='$uid' LIMIT 1");
        if(!$product) break;
        $sup_id=$product['supplier_id'];
        $key=dhtmlchars($_POST['key']);
        $q=$db->query("SELECT uid,goods_file1,goods_name,goods_sale_price 
                       FROM `{$tablepre}downgoods_table` WHERE goods_name LIKE '%$key%' AND supplier_id='$sup_id' AND type IN (0,1,3) AND uid<>'$uid' 
                       LIMIT 10");
        while ($rtl=$db->fetch_array($q))
        {
            $rtl['goods_file1']=ProcImgPath($rtl['goods_file1']);
            $arr_rtl[]=$rtl;
        }
    }while (0);
    $db->free_result();
    
    echo json_encode($arr_rtl);
    exit;
}
else if($action=='get_att_pic')    //获取销售属性图片
{
    $att_cat=dhtmlchars($att_cat);
    $arr=get_dirinfo("images/attr/$att_cat/");
    foreach ($arr as $key=>$val) $arr[$key]="images/attr/$att_cat/".$val;
    echo json_encode($arr);
    exit;
}


function del_goods($uid)
{
    global $db,$tablepre;
    
    $uid=(int)$uid;
    if($uid<=0) return ;
    
    $rtl = $db->get_one("SELECT uid,goods_file1,goods_name FROM `{$tablepre}downgoods_table` WHERE uid = '$uid' LIMIT 1");
    if(!$rtl) exit;
    admin_log("删除下架商品：$rtl[goods_name]");
    
    $detail=$db->get_one("SELECT goods_file2 FROM `{$tablepre}goods_detail` WHERE g_uid='$uid'");
    if($detail) $rtl=array_merge($rtl,$detail);
    
    file_unlink(ProcImgPath($rtl['goods_file1']));
    file_unlink(ProcImgPath($rtl['goods_file2']));
    $q=$db->query("SELECT * FROM `{$tablepre}gallery` WHERE goods_id='$uid'");
    while($gallery=$db->fetch_array($q))
    {
        file_unlink(ProcImgPath($gallery['imgbig']));
        file_unlink(ProcImgPath($gallery['thumb']));
    }
    
    $db->query("DELETE FROM `{$tablepre}gallery` WHERE goods_id='$uid'");
    $db->query("DELETE FROM `{$tablepre}goods_detail` WHERE g_uid='$uid'");
    $db->query("DELETE FROM `{$tablepre}downgoods_table` WHERE uid='$uid'");
    $db->query("DELETE FROM `{$tablepre}goods_combine` WHERE g_uid='$uid' OR com_uid='$uid'");
    $db->free_result();
}

function sort_wholesale($arr1,$arr2)
{
    return $arr1[0]>$arr2[0];
}