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
 * $Id: taobao.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if($action=='list')
{
	if($_POST && (int)$step==1)
	{
		include_once 'include/phpzip.class.php';
        $rowset = new PHPZip;
        $excel_save = "商品名称,商品类目,商铺类目,新旧程度,省,城市,出售方式,商品价格,加价幅度,商品数量,有效期,运费承担,平邮,EMS,快递,付款方式,支付宝,发票,保修,自动重发,放入仓库,橱窗推荐,发布时间,心情故事,商品描述,商品图片,商品属性,团购价,最小团购件数,邮费模版ID,会员打折,修改时间,上传状态,图片状态,返点比例,新图片,销售属性组合,用户输入ID串,用户输入名-值对";
        $excel_save = explode(',',$excel_save);
        echo count($excel_save);
        $excel_save = implode("\t",$excel_save) . "\n";
        $use_vat = $mm_use_vat==2 ? 0:1;

        $supplier_cat=(int)$supplier_cat;
        $search_sql="WHERE supplier_id='$page_member_id'";
        if($supplier_cat>0)
        {
            require_once 'include/category_tree.class.php';
    	    $tree = new tree();
    	    $tree->arr_tree = $ucache->get_cache('tree');
    	    
    	    $search_sql .= " AND supplier_cat='$supplier_cat'";
        }
       
        
        $province=dhtmlchars($province);
        $city=dhtmlchars($city);
        $result=$db->query("SELECT * FROM `{$tablepre}goods_table` $search_sql");
        while($rt = $db->fetch_array($result))
        {
            $rt['goods_file2'] = stripslashes($rt['goods_file2']);
            $rt['goods_main'] = '"' . str_replace('"', '""', $rt['goods_main']) . '"';
            $rt['goods_name'] = strip_tags($rt['goods_name']);
            $goods_value = "$rt[goods_name],$baobei_id,0,5,$province,$city,b,$rt[goods_sale_price],0,$rt[goods_stock],14,1,$post_price,$ems_price,$express_price,2,1,$use_vat,0,1,0,0,1980-1-1 0:00:00,,$rt[goods_main],$rt[goods_file2],,,,0,0,,0,,0,,,,";
            $goods_value = explode(',',$goods_value);
            $excel_save .= implode("\t",$goods_value) . "\n";
            //压缩图片
            /*
            if (!empty($rt['goods_file2']) && is_file(MVMMALL_ROOT . $rt['goods_file2']))
                $rowset->addFile(file_get_contents(MVMMALL_ROOT . $rt['goods_file2']), $rt['goods_file2']);
            */
        }
        header("Content-Type: application/vnd.ms-excel;");
        $rowset->addFile("\xFF\xFE" . utf8_str($excel_save), "mvmmall_goods_taobao($goods_category).csv");
        header("Content-Disposition: attachment; filename=mvmmall_goods_taobao($supplier_cat).zip");
        header("Content-Type: application/unknown");
        exit($rowset->file());
	}
	
	$cat_menu = cat_menu('supplier_cat',0,$page_member_id);
	
	include template('sadmin_taobao');
}
else if($action=='add')
{
    $goods_table=$shop_file['sellshow']==1?"{$tablepre}goods_table":"{$tablepre}goods_show";
    $detail_table=$shop_file['sellshow']==1?"{$tablepre}goods_detail":"{$tablepre}goods_show_detail";
    $gallery_table=$shop_file['sellshow']==1?"{$tablepre}gallery":"{$tablepre}show_gallery";
    $type=$shop_file['sellshow']==1?0:8;
    
	if($_POST && (int)$step==1)
	{
		$goods_category_value=0;
        $category_id = 0;
        foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $goods_category_value=$val;
        }
        
        $supplier_cat=(int)$supplier_cat;
        
        $i_goods_status=0;
        foreach ($goods_status as $val)
        {
            $val=(int)$val;
            $i_goods_status|=$val;
        }
        
        if(!$_FILES['csv_file']) show_msg('请上传淘宝数据文件');
        $img_dir='union/shopimg/user_img/'.strval($page_member_id).'/taobao/';
        if(!is_dir($img_dir)) mkdir($img_dir,0777);
        $img_dir=str_replace('union/','',$img_dir);
        
        $row=0;
        $handle = fopen($_FILES['csv_file']['tmp_name'],'r');
        while ($csv_arr = fgetcsv($handle,1024, "\t"))
        {
        	if($row++<=2) continue;
        	if(!strip_tags($csv_arr[0])) continue;
        	
        	for($i=0;$i<sizeof($csv_arr);$i++)
        	{
        		$csv_arr[$i]=daddslashes($csv_arr[$i]);    //入库之前转义
        		$csv_arr[$i]=mb_convert_encoding($csv_arr[$i],'UTF-8','GBK');
        	}
        	
        	$tmp_arr = explode(";",$csv_arr[28]);    //图片名称
        	$img_arr=array();
        	foreach ($tmp_arr as $val)
        	{
        	    $tmp_arr2=explode(':',$val);
        	    if(!$tmp_arr2[0]) continue;
        	    $img_arr[]=$tmp_arr2[0];
        	}
        	$img_arr=array_unique($img_arr);
        	$img_name=strval($img_arr[0]);
			if($img_name) $goods_file2 = $img_dir.$img_name.'.jpg';    //图片路径
        	
        	$row = array(
        	    'supplier_id' => $page_member_id,
        	    'type'=>$type,
        	    'goods_status' => $i_goods_status,
        	    'goods_category' => $goods_category_value,
        	    'supplier_cat' => $supplier_cat,
        	    'goods_name' => strip_tags($csv_arr[0]),
        	    'goods_brand' => $goods_brand,
        	    'goods_stock' => $csv_arr[9],
        	    'goods_status'=>$i_goods_status,
        	    'goods_sale_price' => $csv_arr[7],
        	    'goods_file1' => $goods_file2,
        	    'register_date' => $m_now_time,
        	);
        	$insert_id=$db->insert("`$goods_table`",$row);
        	$db->free_result();
        	
        	$goods_detail=array(
        	    'g_uid'=>$insert_id,
        	    'goods_key'=>$row['goods_name'],
        	    'goods_advance'=>$row['goods_name'],
        	    'goods_cost'=>$row['goods_sale_price'],
        	    'goods_market_price'=>$row['goods_sale_price'],
        	    'goods_kg'=>0,
        	    'goods_file2'=>$row['goods_file1'],
        	    'goods_main'=>$csv_arr[20],
        	);
        	$db->insert("`$detail_table`",$goods_detail);
        	$db->free_result();
        	
        	foreach ($img_arr as $key=>$val)
        	{
        	    if($key<=0) continue;
        	    $gallery_img = $img_dir.$val.'.jpg';
        	    $row=array(
        	        'goods_id'=>$insert_id,
        	        'imgbig'=>$gallery_img,
        	        'thumb'=>$gallery_img,
        	        'supplier_id'=>$page_member_id
        	    );
        	    $db->insert("`$gallery_table`",$row);
        	    $db->free_result();
        	}
        }
        show_msg('导入成功',"sadmin.php?module=$module&action=$action");
	}
	
	$cat_menu = cat_menu('supplier_cat',0,$page_member_id);
	
	//商品品牌
    $arr_mybrand=MyBrand();
    $arr_mybrand[0]='N/A';
    $brand_menu = drop_menu($arr_mybrand,'goods_brand',$goods_brand);
    
	include template('sadmin_taobao_add');
}

function utf8_str($str)
{
    $str_len = strlen($str);
    $start_len = 0;
    $text_str = '';
    if ($str_len == 0){
        return $text_str;
    }
    while ($start_len < $str_len){
        $nums = ord($str{$start_len});
        if ($nums < 127){
            $text_str .= chr($nums) . chr($nums >> 8);
            $start_len += 1;
        }else{
            if ($nums < 192){
                $start_len ++;
            }elseif ($nums < 224){
                if ($start_len + 1 <  $str_len){
                    $nums = (ord($str{$start_len}) & 0x3f) << 6;
                    $nums += ord($str{$start_len+1}) & 0x3f;
                    $text_str .=   chr($nums & 0xff) . chr($nums >> 8) ;
                }
                $start_len += 2;
            }
            elseif ($nums < 240){
                if ($start_len + 2 <  $str_len){
                    $nums = (ord($str{$start_len}) & 0x1f) << 12;
                    $nums += (ord($str{$start_len+1}) & 0x3f) << 6;
                    $nums += ord($str{$start_len+2}) & 0x3f;
                    $text_str .=   chr($nums & 0xff) . chr($nums >> 8) ;
                }
                $start_len += 3;
            }elseif ($nums < 248){
                if ($start_len + 3 <  $str_len){
                    $nums = (ord($str{$start_len}) & 0x0f) << 18;
                    $nums += (ord($str{$start_len+1}) & 0x3f) << 12;
                    $nums += (ord($str{$start_len+2}) & 0x3f) << 6;
                    $nums += ord($str{$start_len+3}) & 0x3f;
                    $text_str .= chr($nums & 0xff) . chr($nums >> 8) . chr($nums >>16);
                }
                $start_len += 4;
            }elseif ($nums < 252){
                if ($start_len + 4 <  $str_len){
                }
                $start_len += 5;
            }else{
                if ($start_len + 5 <  $str_len){
                }
                $start_len += 6;
            }
        }

    }

    return $text_str;
}

function MyBrand()
{
    global $page_member_id,$tablepre,$db;
    $mm_brand = array();
    $q = $db->query("SELECT id,brandname FROM `{$tablepre}brand_table` WHERE isCheck='1' OR supplier_id='$page_member_id' ORDER BY `id` DESC");
    while($rt = $db->fetch_array($q))
    {
        $mm_brand[0] = '--';
        $mm_brand[$rt['id']] = $rt['brandname'];
    }
    return $mm_brand;
}