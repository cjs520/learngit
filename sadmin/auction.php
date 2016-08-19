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
 * $Id: auction.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/pic.class.php';
if($action=='list')
{
	require_once 'include/pager.class.php';
    $search_sql = "WHERE `supplier_id`='$page_member_id' ";
    $order_sql = " ORDER BY start_date DESC";
    
    if ($ps_subject)    //商品简单搜索
    {
    	$ps_subject=dhtmlchars($ps_subject);
    	$search_sql .= " AND goods_name LIKE '%$ps_subject%'";
    }
    if($cat_menu)
    {
        $cat_menu=(int)$cat_menu;
        $supplier_cat=$cat_menu;
        $search_sql.=" AND supplier_cat='$cat_menu'";
    }
    
    $total_count = $db->counter("{$tablepre}goods_auction",$search_sql);
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,goods_name,goods_file1,supplier_id,start_price,end_price,start_date,end_date,approval,reject,is_complete 
                     FROM `{$tablepre}goods_auction` 
                     $search_sql $order_sql 
                     LIMIT $from_record, $list_num");
    while ($rt = $db->fetch_array($q))
    {
        $rt['url'] = GetBaseUrl('auction_detail',$rt['uid'],'action',$rt['supplier_id']);
        $rt['logo']=ProcImgPath($rt['goods_file1']);
        $rt['start_date'] = date('Y-m-d',$rt['start_date']);
        $rt['end_date'] = date('Y-m-d',$rt['end_date']);
        $rt['start_price']=currency($rt['start_price']);
        $rt['end_price']=currency($rt['end_price']);
        
        $rt['status']=$arr_status[$rt['approval']];
        $rt['rehandout']=$rt['is_complete']>0;
        if($rt['is_complete']==1) $rt['status']='已完成（保证金结算中）';
        else if($rt['is_complete']==2) $rt['status']='已完成';
        
        $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_auction_join` WHERE g_uid='$rt[uid]'");
        $rt['join_times']=$rtl['cnt'];
        
        $goods_rt[] = $rt;
    }
    $db->free_result();
    //分类菜单
    $cat_data=$ucache->get_cache('tree',$page_member_id);
    $cat_menu_move = cat_menu('cat_menu_move',0,$page_member_id,$cat_data);
    $cat_menu = cat_menu('cat_menu',(int)$cat_menu,'',true,$page_member_id,$cat_data);
    $ps_subject_txt=$ps_subject;
    $ps_subject = urlencode($ps_subject);
    $page_list = $rowset->link("sadmin.php?module=$module&action=$action&ps_subject=$ps_subject&cat_menu=$supplier_cat&page=");
    
	include template('sadmin_goods_auction');
}
else if($action=='join_list')
{
    $uid=(int)$uid;
    
    $arr_join=array();
    $q=$db->query("SELECT m_id,price,register_date FROM `{$tablepre}goods_auction_join` WHERE g_uid='$uid' ORDER BY register_date DESC");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['price']=currency($rtl['price']);
        $rtl['register_date']=date('Y-m-d H:i:s',$rtl['register_date']);
        $arr_join[]=$rtl;
    }
    $db->free_result();
    
    include template('sadmin_goods_auction_join');
    exit;
}
else if($action=='add' || $action=='edit')
{
	if($action=='add')    //未审核时查看一下商品
	{
		if($shop_file['isSupplier']<=2) show_msg('您的商铺还未通过审核，无法申请拍卖活动');
		if($mvm_member['member_point']<floatval($mm_auction_point)) show_msg('您的积分不足以发布拍卖活动，请先充值');
	}
	
	$uid=(int)$uid;
	
    if ($_POST && (int)$step==1)
    {
        if(getSupplierFolderSize($page_member_id)>=$allow_upload_size[$shop_file['shop_level']]) show_msg('您的图片空间已经占满，请及时清理');
        if($action=='edit')
        {
            $product=$db->get_one("SELECT goods_file1,approval FROM `{$tablepre}goods_auction` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
            if(!$product) show_msg('检索不到指定的商品');
            if($product['approval']>0) show_msg('本拍卖活动已通过审核，无法修改');
            $tmp_rtl=$db->get_one("SELECT goods_file2 FROM `{$tablepre}goods_auction_detail` WHERE g_uid='$uid' LIMIT 1");
            if($tmp_rtl) $product=array_merge($product,$tmp_rtl);
            $db->free_result();
        }
        
        $goods_category_value=0;
		foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $goods_category_value=$val;
        }
        
        require_once 'include/supplier_upfile.class.php';
        require_once 'include/watermark.class.php';
        $f = new upfile('gif,jpg,png,jpeg','',512*1024,0,$page_member_id);
        
        //上传详细大图
        $goods_file2=$product['goods_file2'];
        $goods_file1=$product['goods_file1'];
        if($_FILES['goods_file2']['name']!='')
        {
            file_unlink(ProcImgPath($product['goods_file2']));
            $goods_file2 = $f->upload('goods_file2');
            
            if($_FILES['goods_file1']['name']=='')    //没上传小图，大图直接压缩
            {
                file_unlink(ProcImgPath($product['goods_file1']));
                $goods_file1=pic::PicZoom($goods_file2,370,370,array(255,255,255),false);
            }
            
            if($ucfg['mm_big_wate']) $goods_file2=Watermark::SetWaterMark($goods_file2,$ucfg,true);
            if($ucfg['mm_thumb_wate']) $goods_file1=Watermark::SetWaterMark($goods_file1,$ucfg,true);
        }
        
        //有小图上传
        if ($_FILES['goods_file1']['name']!='')    
        {
            file_unlink(ProcImgPath($product['goods_file1']));
            $goods_file1 = $f->upload('goods_file1');
            if($ucfg['mm_thumb_wate']) $goods_file1=Watermark::SetWaterMark($goods_file1,$ucfg,true);
        }
        
        //商品显示
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
        
        //入库
        $row = array(
            'goods_status' => $i_goods_status,
            'goods_category' => $goods_category_value,
            'supplier_cat' => (int)$supplier_cat,
            'goods_code' => $goods_code,
            'goods_name' => $goods_name,
            'goods_brand' => $goods_brand,
            'start_price' => floatval($start_price),
            'type'=>7,
            'end_price'=>floatval($end_price),
            'bid_add'=>floatval($bid_add),
            'assure'=>floatval($assure),
            'goods_file1' => str_replace('union/','',$goods_file1),
            'supplier_id' => $page_member_id,
            'filter_attr'=>$str_att,
            'start_date'=>strtotime($start_date),
            'end_date'=>strtotime($end_date)+24*3600-1,
            'approval'=>0
        );
        
        $detail_row=array(
            'goods_key'=>dhtmlchars($goods_key),
            'goods_advance'=>dhtmlchars($goods_advance),
            'goods_cost'=>floatval($goods_cost),
            'goods_market_price'=>floatval($goods_market_price),
            'goods_kg'=>floatval($goods_kg),
            'goods_file2'=>str_replace('union/','',$goods_file2),
            'goods_main'=>$goods_main,
        );
        
        if ($action=='add')
        {
        	$goods_id = $db->insert("{$tablepre}goods_auction",$row);
        	$detail_row['g_uid']=$goods_id;
        	$db->insert("`{$tablepre}goods_auction_detail`",$detail_row);
        	add_score($m_check_uid,floatval($mm_auction_point)*-1,'拍卖申请',$row['goods_name'].' 拍卖申请');
        }
        else if($action=='edit' && is_numeric($uid))
        {
        	$goods_id = $uid = (int)$uid;
            $db->update("{$tablepre}goods_auction",$row,$where="uid='$uid'");
            $detail_row['g_uid']=$goods_id;
        	$db->replace("`{$tablepre}goods_auction_detail`",$detail_row);
        }
        $db->free_result();
        
        //上传商品像册，并入库
        $f = new upfile('gif,jpg,png,bmp','union/shopimg/gallery/'.$page_member_id.'/',512*1024);
        if($_FILES['gallery']['name'][0])
        {
            $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}auction_gallery` WHERE goods_id='$goods_id'");
            $gallery_size=(int)$rtl['cnt'];
        	foreach ($_FILES['gallery']['name'] as $key => $val)
            {
                if(!$_FILES['gallery']['name'][$key]) continue;
            	if($gallery_size>=5) break;
                $gallery_size++;
            	
                $upload = array(
                    'name' => $_FILES['gallery']['name'][$key],
                    'type' => $_FILES['gallery']['type'][$key],
                    'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                    'error' => $_FILES['gallery']['error'][$key],
                    'size' => $_FILES['gallery']['size'][$key],
                );
                $imgbig = $f->upload($upload);
                $thumb = pic::PicZoom($imgbig,370,370,array(255,255,255),false);
            	
            	$imgbig=str_replace('union/','',$imgbig);
            	$thumb=str_replace('union/','',$thumb);
        	    $db->query("INSERT INTO `{$tablepre}auction_gallery` SET 
        	                goods_id='$goods_id',
        	                imgbig='$imgbig',
        	                thumb='$thumb',
        	                supplier_id='$page_member_id'");
            }
        }

        show_msg('操作成功',"sadmin.php?module=$module&action=list");
    }//end post

    $uid=(int)$uid;
    if($action='edit' && $uid>0)
    {
        //商品信息
    	$product = $db->get_one("SELECT * FROM `{$tablepre}goods_auction` WHERE uid='$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
    	if($product)
    	{
    	    $product_detail=$db->get_one("SELECT * FROM `{$tablepre}goods_auction_detail` WHERE g_uid='$uid' LIMIT 1");
    	    if($product_detail) $product=array_merge($product,$product_detail);
    	}
    	@extract($product,EXTR_OVERWRITE);
    	$db->free_result();
    	
    	//相册
        $photo = $db->get_all("SELECT imgid,thumb FROM `{$tablepre}auction_gallery` 
                               WHERE goods_id='$uid' AND `supplier_id`='$page_member_id' LIMIT 5");
        foreach ($photo as $key=>$val)
        {
            $photo[$key]['thumb'] = ProcImgPath($photo[$key]['thumb']);
        }
        $db->free_result();
        
        //商品筛选属性
        $category=$db->get_one("SELECT rate,att_list FROM `{$tablepre}category` WHERE uid='$product[goods_category]' LIMIT 1");
        $category['rate']*=10;    //转为百分数
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
        
        $url=GetBaseUrl('auction',$uid,'action',$supplier_id);
        
        $hot_checked=($goods_status&1)?'checked':'';
        $best_checked=($goods_status&2)?'checked':'';
        $free_delivery_checked=($goods_status&4)?'checked':'';
        $goods_file1=ProcImgPath($goods_file1);
        $goods_main=dhtmlchars($goods_main);
        $start_date=date('Y-m-d',$start_date);
        $end_date=date('Y-m-d',$end_date);

        //销售属性
        $arr_attr_val=SplitAttr($attr_val);
        
        $status=$arr_status[$approval];
    }
    else
    {
        $arr_attr_val=array();
        $status='未发布';
    }
    $goods_file1=ProcImgPath($goods_file1);
    
    $cat_data=$ucache->get_cache('tree',$page_member_id);
    $cat_menu = cat_menu('supplier_cat',$supplier_cat,$page_member_id,$cat_data);    //供应商分类选择
    
    //商品品牌
    $arr_mybrand=MyBrand();
    $brand_menu = drop_menu($arr_mybrand,'goods_brand',$goods_brand);
    $db->free_result();
    
    //获取属性图片分类
    $attr_category=get_dirinfo('images/attr/');
    
	include template('sadmin_goods_auction_add');
}
else if($action=='del')
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
    exit('OK:删除成功');
}
else if($action=='bat_move')
{
	if(is_array($uid_check) && $uid_check)
    {
    	foreach ($uid_check as $key=>$val)
    	{
    		$val=(int)$val;
    		if($val>0) $uid_check[$key]=$val;
    		else unset($uid_check[$key]);
    	}
    	$uid_check=array_unique($uid_check);
    	$str_uid=implode(',',$uid_check);
    	$cat_menu_move=(int)$cat_menu_move;
    	$rtl=$db->get_one("SELECT uid FROM `{$tablepre}category` WHERE uid='$cat_menu_move' AND supplier_id='$page_member_id' LIMIT 1");
    	if($uid_check && $rtl)
    	{
    	     $db->query("UPDATE `{$tablepre}goods_auction` SET supplier_cat='$cat_menu_move' WHERE uid IN ($str_uid) AND `supplier_id`='$page_member_id'");
    	}
    	$db->free_result();
    }
	exit('OK:成功更新商品分类');
}
else if($action=='ajax_edit')
{
	$f=dhtmlchars($f);
	$v=dhtmlchars($_GET['v']);
	$uid=(int)$uid;
	if($uid>0 && in_array($f,array('goods_name','goods_sale_price')))
	{
		$db->query("UPDATE `{$tablepre}goods_auction` SET `$f`='$v' WHERE uid='$uid' AND supplier_id='$page_member_id'");
	}
	exit('修改成功');
}
else if($action=='delimg')    //ajax删除单个相册图片
{
	$uid=(int)$uid;
	if($uid<=0) exit;
    $gallery = $db->get_one("SELECT * FROM `{$tablepre}auction_gallery` WHERE imgid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
    if(!$gallery) exit;
    file_unlink(ProcImgPath($gallery['imgbig']));
    file_unlink(ProcImgPath($gallery['thumb']));
    $db->query("DELETE FROM `{$tablepre}auction_gallery` WHERE imgid='$uid'"); 
    exit;
}
else if($action=='get_att_list')    //获取相关分类下的筛选属性
{
	$cid=(int)$cid;
	$arr_rtl=array('attr'=>array(),'rate'=>0);
	$rtl=$db->get_one("SELECT att_list,rate FROM `{$tablepre}category` WHERE uid='$cid' AND supplier_id='0' LIMIT 1");
	if($rtl['att_list'])
	{
	    $arr_rtl['attr']=unserialize($rtl['att_list']);
	    if(!$arr_rtl['attr']) $arr_rtl['attr']=array();
	}
	$arr_rtl['rate']=$rtl['rate']*100;
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
else if($action=='rehandout')
{
    if($mvm_member['member_point']<floatval($mm_auction_point)) exit('ERROR:您的积分不足以发布拍卖活动，请先充值');
    $uid=(int)$uid;
    $g=$db->get_one("SELECT * FROM `{$tablepre}goods_auction` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
    if(!$g) exit('ERROR:检索不到指定的拍卖活动');
    $g['rel_uid']=$g['uid'];
    $g['approval']=0;
    $g['reject']='';
    $g['start_date']=$g['end_date']=$m_now_time;
    unset($g['uid']);
    $insert_id=$db->insert("`{$tablepre}goods_auction`",$g);
    $db->free_result();
    
    $detail=$db->get_one("SELECT * FROM `{$tablepre}goods_auction_detail` WHERE g_uid='$uid' LIMIT 1");
    if($detail)
    {
        $detail['g_uid']=$insert_id;
        $db->replace("`{$tablepre}goods_auction_detail`",$detail);
    }
    $db->free_result();
    
    $q=$db->query("SELECT * FROM `{$tablepre}auction_gallery` WHERE goods_id='$uid'");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['goods_id']=$insert_id;
        unset($rtl['imgid']);
        $db->insert("`{$tablepre}auction_gallery`",$rtl);
    }
    $db->free_result();
    
    add_score($m_check_uid,floatval($mm_auction_point)*-1,'拍卖申请',"拍卖申请 $g[goods_name]");
    exit('OK:拍卖活动重发成功');
}

function MyBrand()
{
    global $page_member_id,$tablepre,$db;
    $mm_brand = array();
    $q = $db->query("SELECT id,brandname FROM `{$tablepre}brand_table` WHERE isCheck='1' OR supplier_id='$page_member_id'");
    while($rt = $db->fetch_array($q))
    {
        $mm_brand[0] = '--';
        $mm_brand[$rt['id']] = $rt['brandname'];
    }
    return $mm_brand;
}

function del_goods($uid)
{
    global $db,$tablepre,$page_member_id;
    
    $uid=(int)$uid;
    if($uid<=0) return ;
    
    $rtl = $db->get_one("SELECT uid,goods_file1 FROM `{$tablepre}goods_auction` WHERE uid = '$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
    if(!$rtl) exit;
    $detail=$db->get_one("SELECT goods_file2 FROM `{$tablepre}goods_auction_detail` WHERE g_uid='$uid'");
    if($detail) $rtl=array_merge($rtl,$detail);
    
    $rel_rtl=$db->get_one("SELECT uid FROM `{$tablepre}goods_auction` WHERE supplier_id='$page_member_id' AND rel_uid='$uid' AND uid<>'$uid' LIMIT 1");
    
    if(!$rel_rtl)    //未关联再删除
    {
        file_unlink(ProcImgPath($rtl['goods_file1']));
        file_unlink(ProcImgPath($rtl['goods_file2']));
        $q=$db->query("SELECT * FROM `{$tablepre}auction_gallery` WHERE goods_id='$uid'");
        while($gallery=$db->fetch_array($q))
        {
            file_unlink(ProcImgPath($gallery['imgbig']));
            file_unlink(ProcImgPath($gallery['thumb']));
        }
    }
    
    $db->query("DELETE FROM `{$tablepre}auction_gallery` WHERE goods_id='$uid'");
    $db->query("DELETE FROM `{$tablepre}goods_auction_detail` WHERE g_uid='$uid'");
    $db->query("DELETE FROM `{$tablepre}goods_auction` WHERE uid='$uid'");
    $db->free_result();
}