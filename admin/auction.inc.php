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
 * $Id: auction.inc.php www.mvmmall.cn$
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
    
    if(in_array($approval,array('1','0','-1')))
    {
        $search_sql.="AND approval='$approval'";
        $order_sql='';
    }
    else $approval=-2;
    
    $cat_ids=array();
    $cats=array();
    $total_count = $db->counter("{$tablepre}goods_auction",$search_sql);
	$page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,goods_name,goods_file1,goods_code,start_date,end_date,approval,reject,start_price,end_price,is_complete,supplier_id 
                     FROM `{$tablepre}goods_auction` $search_sql $order_sql LIMIT $from_record, $list_num");
    while ($rt = $db->fetch_array($q))
    {
        $rt['edit'] = "admincp.php?module=$module&action=edit&uid=$rt[uid]&prev_url=$prev_url";
        $rt['goods_file1'] = ProcImgPath($rt['goods_file1']);
        
        $rt['goods_url'] = GetBaseUrl('auction_detail',$rt['uid'],'action',$rt['supplier_id']);
        $rt['start_date'] = date('Y-m-d',$rt['start_date']);
        $rt['end_date'] = date('Y-m-d',$rt['end_date']);
        $rt['start_price']=currency($rt['start_price']);
        $rt['end_price']=currency($rt['end_price']);
        $rt['status']=$arr_status[$rt['approval']];
        if($rt['is_complete']==1) $rt['status']='已完成（保证金结算中）';
        else if($rt['is_complete']==2) $rt['status']='已完成';
        
        $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}goods_auction_join` WHERE g_uid='$rt[uid]'");
        $rt['join_times']=$rtl['cnt'];
        
        $goods_rt[] = $rt;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&ps_subject=$ps_subject&ps_code=$ps_code&od=$od&approval=$approval&page=");
    $arr_status[-2]='--全部--';
    $sel_approval=drop_menu($arr_status,'approval',$approval);
    
    require_once template('goods_auction');
    footer();
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
    
    require_once template('goods_auction_join');
    exit;
}
else if($action=='edit')
{
	$goods_id=$uid=(int)$uid;
    $product = $db->get_one("SELECT * FROM `{$tablepre}goods_auction` WHERE uid='$uid' LIMIT 1");
    if(!$product) show_msg('找不到您指定的商品');
    $product_detail=$db->get_one("SELECT * FROM `{$tablepre}goods_auction_detail` WHERE g_uid='$uid' LIMIT 1");
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
        
        
        //入库
        $row = array(
            'goods_status' => $i_goods_status,
            'goods_category' => $goods_category_value,
            'goods_code' => $goods_code,
            'goods_name' => $goods_name,
            'goods_brand' => $goods_brand,
            'type'=>7,
            'start_price'=>floatval($start_price),
            'end_price'=>floatval($end_price),
            'assure'=>floatval($assure),
            'bid_add'=>floatval($bid_add),
            'goods_file1' => str_replace('union/','',$goods_file1),
            'filter_attr'=>$str_att,
            'start_date'=>strtotime($start_date),
            'end_date'=>strtotime($end_date)+24*3600-1
        );
        
        $detail_row=array(
            'goods_key'=>dhtmlchars($goods_key),
            'goods_advance'=>dhtmlchars($goods_advance),
            'goods_cost'=>floatval($goods_cost),
            'goods_market_price'=>floatval($goods_market_price),
            'goods_kg'=>floatval($goods_kg),
            'goods_file2'=>str_replace('union/','',$goods_file2),
            'goods_main'=>$goods_main
        );
        $db->update("{$tablepre}goods_auction",$row,$where="uid='$uid'");
        $detail_row['g_uid']=$goods_id;
        $db->replace("`{$tablepre}goods_auction_detail`",$detail_row);
        $db->free_result();
        
        admin_log("修改拍卖商品：$product[goods_name]");
        
        show_msg('success',base64_decode($_POST['prev_url']));
    } 
    
    $mm_brand = $cache->get_cache('brand');
    
    $photo = $db->get_all("SELECT imgid,thumb FROM `{$tablepre}auction_gallery` WHERE goods_id='$uid'");//相册数据
    foreach ($photo as $key=>$val)
    {
        $photo[$key]['thumb']=ProcImgPath($photo[$key]['thumb']);
    }
    @extract($product,EXTR_OVERWRITE);
    
    $goods_main=dhtmlchars($goods_main);
    $start_date=date('Y-m-d',$start_date);
    $end_date=date('Y-m-d',$end_date);
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
    
    //商品品牌
    $brand_menu = drop_menu($cache->get_cache('brand'),'goods_brand',$goods_brand);
    
    //获取属性图片分类
    $attr_category=get_dirinfo('images/attr/');
    
    require_once template('goods_auction_add');
    footer();
}
else if ($action=='delimg')    //ajax删除单个相册图片
{
    do
    {
        $uid=(int)$uid;
        $gallery = $db->get_one("SELECT * FROM `{$tablepre}auction_gallery` WHERE imgid='$uid' LIMIT 1");
        if(!$gallery) break;
        
        $product=$db->get_one("SELECT goods_name FROM `{$tablepre}goods_auction` WHERE uid='$gallery[goods_id]' LIMIT 1");
        if($product) admin_log("删除拍卖商品相册：$product[goods_name]");
        
        $thumb=ProcImgPath($gallery['thumb']);
        $imgbig=ProcImgPath($gallery['imgbig']);
        file_unlink($thumb);
        file_unlink($imgbig);
        $db->query("DELETE FROM `{$tablepre}auction_gallery` WHERE imgid='$uid'"); 
        $db->free_result();
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
else if($action=='ajax_edit')
{
    $v=dhtmlchars($_POST['v']);
    $field=dhtmlchars($field);
    $uid=(int)$uid;
    if(in_array($field,array('goods_name','goods_sale_price')) && $uid>0)
    {
        $db->query("UPDATE `{$tablepre}goods_auction` SET `$field`='$v' WHERE uid='$uid'");
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
else if($action=='get_att_pic')    //获取销售属性图片
{
    $att_cat=dhtmlchars($att_cat);
    $arr=get_dirinfo("images/attr/$att_cat/");
    foreach ($arr as $key=>$val) $arr[$key]="images/attr/$att_cat/".$val;
    echo json_encode($arr);
    exit;
}
else if($action=='approval')
{
    $uid=(int)$uid;
    $v=(int)$_POST['v'];
    if(!in_array($v,array(1,-1)) || $uid<=0) exit('ERROR:审核失败');
    $g=$db->get_one("SELECT goods_name,supplier_id FROM `{$tablepre}goods_auction` WHERE uid='$uid' LIMIT 1");
    if(!$g) exit('ERROR:检索不到指定的拍卖活动');
    
    $row=array(
        'approval'=>$v
    );
    if($v==-1) $row['reject']=dhtmlchars($reject);
    $db->update("`{$tablepre}goods_auction`",$row," uid='$uid' AND approval='0'");
    $db->free_result();
    if($v==-1)
    {
        admin_log("拒绝拍卖商品审核：$g[goods_name]");
        add_score($g['supplier_id'],floatval($mm_auction_point),'拒绝拍卖','拒绝:'.$g['goods_name']);
    }
    else admin_log("审核拍卖商品：$g[goods_name]");
    
    exit('OK:审核成功');
}

function del_goods($uid)
{
    global $db,$tablepre;
    
    $uid=(int)$uid;
    if($uid<=0) return ;
    
    $rtl = $db->get_one("SELECT uid,goods_file1,goods_name FROM `{$tablepre}goods_auction` WHERE uid = '$uid' LIMIT 1");
    if(!$rtl) return ;
    admin_log("删除拍卖商品：$rtl[goods_name]");
    
    $detail=$db->get_one("SELECT goods_file2 FROM `{$tablepre}goods_auction_detail` WHERE g_uid='$uid'");
    if($detail) $rtl=array_merge($rtl,$detail);
    
    file_unlink(ProcImgPath($rtl['goods_file1']));
    file_unlink(ProcImgPath($rtl['goods_file2']));
    $q=$db->query("SELECT * FROM `{$tablepre}auction_gallery` WHERE goods_id='$uid'");
    while($gallery=$db->fetch_array($q))
    {
        file_unlink(ProcImgPath($gallery['imgbig']));
        file_unlink(ProcImgPath($gallery['thumb']));
    }
    
    $db->query("DELETE FROM `{$tablepre}auction_gallery` WHERE goods_id='$uid'");
    $db->query("DELETE FROM `{$tablepre}goods_auction_detail` WHERE g_uid='$uid'");
    $db->query("DELETE FROM `{$tablepre}goods_auction` WHERE uid='$uid'");
    $db->free_result();
}