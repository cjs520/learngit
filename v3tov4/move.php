<?php
error_reporting(0);
define('MVMMALL','');
define('MVMMALL_ROOT',dirname(dirname(__FILE__)));
$start_time=time();
set_time_limit(0);
ini_set('memory_limit','-1');

//来源数据
$from_db_host = 'localhost';
$from_db_id   = '';
$from_db_pass = '';
$from_db_name = '';
$from_tablepre = 'mvm_';

//目的地数据
$to_db_host = 'localhost';
$to_db_id   = '';
$to_db_pass = '';
$to_db_name = '';
$to_tablepre = 'mvm_';

//要转换的数据表
require_once 'mysql_class.php';
require_once 'shop.class.php';

//来源数据库连接
$from_db=new dbmysql($from_db_host,$from_db_id,$from_db_pass,$from_db_name);

//目的数据库连接
$to_db=new dbmysql($to_db_host,$to_db_id,$to_db_pass,$to_db_name);

$start_time=time();
//开始进行转换
//member_table && member_shop
$to_db->query("TRUNCATE TABLE `{$to_tablepre}member_table`");
$to_db->query("TRUNCATE TABLE `{$to_tablepre}member_shop`");
$q=$from_db->query("SELECT uid,member_class,member_id,member_pass,base_pass,member_name,member_sex,member_tel1,member_tel2,member_email,qq,taobao,member_zip,province,city,county,
                           member_address,member_point,member_money,member_money_freeze,member_image,register_date,isSupplier,
                           map_title,map_tip,lng,lat,member_homepage,approval_date,shop_level,certified_type,shop_name,run_product,up_id_card,up_licence,up_licence_thumb,
                           up_logo,sellshow,video_code,promote_pic1,supplier_notice 
                    FROM `{$from_tablepre}member_table`");
while($rtl=$from_db->fetch_array($q))
{
    $member_row=array(
        'uid'=>$rtl['uid'],
        'member_class'=>$rtl['member_class'],
        'member_id'=>$rtl['member_id'],
        'member_pass'=>$rtl['member_pass'],
        'base_pass'=>$rtl['base_pass'],
        'pay_pass'=>$rtl['member_pass'],
        'member_name'=>$rtl['member_name'],
        'member_sex'=>$rtl['member_sex'],
        'member_tel1'=>$rtl['member_tel1'],
        'member_tel2'=>$rtl['member_tel2'],
        'member_email'=>$rtl['member_email'],
        'qq'=>$rtl['qq'],
        'taobao'=>$rtl['taobao'],
        'province'=>$rtl['province'],
        'city'=>$rtl['city'],
        'county'=>$rtl['county'],
        'member_address'=>$rtl['member_address'],
        'member_point'=>$rtl['member_point'],
        'member_point_acc'=>$rtl['member_point_acc'],
        'member_money'=>$rtl['member_money'],
        'member_money_freeze'=>$rtl['member_money_freeze'],
        'member_image'=>$rtl['member_image'],
        'register_date'=>$rtl['register_date'],
        'isSupplier'=>$rtl['isSupplier']
    );
    $to_db->insert("`{$to_tablepre}member_table`",$member_row);
    unset($member_row);
    
    if($rtl['isSupplier']>0)
    {
        $shop_row=array(
            'm_uid'=>$rtl['uid'],
            'm_id'=>$rtl['member_id'],
            'isSupplier'=>$rtl['isSupplier'],
            'province'=>$rtl['province'],
            'city'=>$rtl['city'],
            'county'=>$rtl['county'],
            'shop_address'=>$rtl['member_address'],
            'lng'=>$rtl['lng'],
            'lat'=>$rtl['lat'],
            'map_title'=>$rtl['map_title'],
            'member_homepage'=>$rtl['member_homepage']?$rtl['member_homepage']:'mvm'.$rtl['uid'],
            'register_date'=>$rtl['register_date'],
            'approval_date'=>$rtl['approval_date'],
            'shop_level'=>$rtl['shop_level'],
            'certified_type'=>$rtl['certified_type'],
            'shop_name'=>$rtl['shop_name'],
            'run_product'=>$rtl['run_product'],
            'up_id_card'=>$rtl['up_id_card'],
            'up_licence'=>$rtl['up_licence'],
            'up_licence_thumb'=>$rtl['up_licence_thumb'],
            'up_logo'=>$rtl['up_logo'],
            'sellshow'=>$rtl['sellshow']==1?2:1,
            'video_code'=>$rtl['video_code'],
            'promote_pic'=>$rtl['promote_pic1'],
            'supplier_notice'=>$rtl['supplier_notice'],
            'shop_expire'=>$start_time+365*24*3600,
        );
        $to_db->insert("`{$to_tablepre}member_shop`",$shop_row);
        unset($shop_row);
        shop::CreateSupplierFile($rtl['uid']);
    }
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();


//表address
$to_db->query("TRUNCATE TABLE `{$to_tablepre}address`");
$q=$from_db->query("SELECT is_buy,consignee,address,zipcode,tel,mobile,province,city,county,member_id FROM `{$from_tablepre}address`");
while($rtl=$from_db->fetch_array($q))
{
    $rtl['m_uid']=$rtl['member_id'];
    $rtl['mobile']=$rtl['tel'];
    unset($rtl['member_id']);
    unset($rtl['tel']);
    $to_db->insert("`{$to_tablepre}address`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//ship table && area_table
$to_db->query("TRUNCATE TABLE `{$to_tablepre}ship_table`");
$to_db->query("TRUNCATE TABLE `{$to_tablepre}area_table`");
$id_2_supplier=array();
$q=$from_db->query("SELECT id,class_name,name,supplier_id FROM `{$from_tablepre}ship_table`");
while($rtl=$from_db->fetch_array($q))
{
    $id_2_supplier[$rtl['id']]=$rtl['supplier_id'];
    $rtl['uid']=$rtl['id'];
    unset($rtl['id']);
    $to_db->insert("`{$to_tablepre}ship_table`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

$q=$from_db->query("SELECT * FROM `{$from_tablepre}area_table`");
while($rtl=$from_db->fetch_array($q))
{
    $rtl['uid']=$rtl['area_id'];
    $rtl['ship_uid']=$rtl['ship_id'];
    unset($rtl['area_id']);
    unset($rtl['ship_id']);
    $rtl['supplier_id']=$id_2_supplier[$rtl['ship_uid']];
    $to_db->insert("`{$to_tablepre}area_table`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();
unset($id_2_supplier);

//ask_order
$to_db->query("TRUNCATE TABLE `{$to_tablepre}ask_order`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}ask_order`");
while($rtl=$from_db->fetch_array($q))
{
    $rtl['msg']=mb_substr($rtl['msg'],0,150,'UTF-8');
    $to_db->insert("`{$to_tablepre}ask_order`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//badmin_table && bmain
$to_db->query("TRUNCATE TABLE `{$to_tablepre}badmin_table`");
$q=$from_db->query("SELECT uid,board_name_code,board_title,register_date,od,supplier_id FROM `{$from_tablepre}badmin_table`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}badmin_table`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

$to_db->query("TRUNCATE TABLE `{$to_tablepre}bmain`");
$q=$from_db->query("SELECT uid,ps_name,board_name,cover,board_subject,board_hit,register_date,board_body,supplier_id FROM `{$from_tablepre}bmain`");
while($rtl=$from_db->fetch_array($q))
{
    $rtl['author']=$rtl['board_name'];
    unset($rtl['board_name']);
    $rtl['od']=$rtl['register_date'];
    $to_db->insert("`{$to_tablepre}bmain`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//banner_table
$to_db->query("TRUNCATE TABLE `{$to_tablepre}banner_table`");
$q=$from_db->query("SELECT uid,banner_point,banner_class,banner_weight,banner_subject,banner_width,banner_height,banner_file1,banner_url,supplier_id FROM `{$from_tablepre}banner_table`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}banner_table`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();


//brand_table
$to_db->query("TRUNCATE TABLE `{$to_tablepre}brand_table`");
$q=$from_db->query("SELECT id,brandname,logo,keywords,brief,weburl,train,category_id,isCheck,supplier_id FROM `{$from_tablepre}brand_table`");
while($rtl=$from_db->fetch_array($q))
{
    $rtl['brief']=mb_substr($rtl['brief'],0,150,'UTF-8');
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//category
$to_db->query("TRUNCATE TABLE `{$to_tablepre}category`");
$q=$from_db->query("SELECT uid,category_id,supplier_id,category_name,category_key,category_desc,category_file1,category_rank,att_list FROM `{$from_tablepre}category`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}category`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//ceriti
$to_db->query("TRUNCATE TABLE `{$to_tablepre}certi`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}certi`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}certi`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//comment_allow
$to_db->query("TRUNCATE TABLE `{$to_tablepre}comment_allow`");
$q=$from_db->query("SELECT uid,from_id,to_id,expire,ordersn,shop_name,supplier_id,roll FROM `{$from_tablepre}comment_allow`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}comment_allow`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//config
$to_db->query("TRUNCATE TABLE `{$to_tablepre}config`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}config`");
while($rtl=$from_db->fetch_array($q))
{
    daddslashes($rtl);
    $to_db->insert("`{$to_tablepre}config`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//cycle
$to_db->query("TRUNCATE TABLE `{$to_tablepre}cycle`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}cycle`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}cycle`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//favorite
$to_db->query("TRUNCATE TABLE `{$to_tablepre}favorite`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}favorite`");
while($rtl=$from_db->fetch_array($q))
{
    $row=array(
        'm_uid'=>$rtl['member_uid'],
        'f_uid'=>$rtl['shop_uid'],
        't'=>'0'
    );
    $to_db->insert("`{$to_tablepre}favorite`",$row);
    unset($row);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//forumlinks_table
$to_db->query("TRUNCATE TABLE `{$to_tablepre}forumlinks_table`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}forumlinks_table`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}forumlinks_table`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//friend
$to_db->query("TRUNCATE TABLE `{$to_tablepre}friend`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}friend`");
while($rtl=$from_db->fetch_array($q))
{
    $rtl['register_date']=$start_time;
    $to_db->insert("`{$to_tablepre}friend`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//goods_storage
$to_db->query("TRUNCATE TABLE `{$to_tablepre}goods_storage`");
$q=$from_db->query("SELECT uid,goods_uid,goods_name,approval FROM `{$from_tablepre}goods_storage`");
while($rtl=$from_db->fetch_array($q))
{
    $rtl['approval_date']=$rtl['approval']<=0?0:$start_time;
    unset($rtl['approval']);
    $to_db->insert("`{$to_tablepre}goods_storage`",$rtl);
}
$from_db->free_result();
$to_db->free_result();

//tld
$to_db->query("TRUNCATE TABLE `{$to_tablepre}tld`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}tld`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}tld`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//sms
$to_db->query("TRUNCATE TABLE `{$to_tablepre}sms`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}sms`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}sms`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//ss_comment
$to_db->query("TRUNCATE TABLE `{$to_tablepre}ss_comment`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}ss_comment`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}ss_comment`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//point_table
$to_db->query("TRUNCATE TABLE `{$to_tablepre}point_table`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}point_table`");
while($rtl=$from_db->fetch_array($q))
{
    unset($rtl['supplier_id']);
    $to_db->insert("`{$to_tablepre}point_table`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//payment_table
$to_db->query("TRUNCATE TABLE `{$to_tablepre}payment_table`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}payment_table` WHERE class_name<>'advance'");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}payment_table`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//member_account
$to_db->query("TRUNCATE TABLE `{$to_tablepre}member_account`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}member_account`");
while($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}member_account`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//grade_table
$to_db->query("TRUNCATE TABLE `{$to_tablepre}grade_table`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}grade_table`");
while ($rtl=$from_db->fetch_array($q))
{
    $to_db->insert("`{$to_tablepre}grade_table`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//gcomment_table
$to_db->query("TRUNCATE TABLE `{$to_tablepre}gcomment_table`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}gcomment_table`");
while($rtl=$from_db->fetch_array($q))
{
    $rtl['m_id']=$rtl['comment_name'];
    $rtl['approval_date']=$rtl['approval_date']>0?$start_time:0;
    unset($rtl['comment_name']);
    unset($rtl['comment_id']);
    unset($rtl['comment_ip']);
    $rtl['module']='product';
    $to_db->insert("`{$to_tablepre}gcomment_table`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//money_table
$to_db->query("TRUNCATE TABLE `{$to_tablepre}money_table`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}money_table`");
while($rtl=$from_db->fetch_array($q))
{
    unset($rtl['to_id']);
    unset($rtl['supplier_id']);
    $to_db->insert("`{$to_tablepre}money_table`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//goods_table
$to_db->query("TRUNCATE TABLE `{$to_tablepre}goods_table`");
$to_db->query("TRUNCATE TABLE `{$to_tablepre}goods_detail`");
$to_db->query("TRUNCATE TABLE `{$to_tablepre}gallery`");
$to_db->query("TRUNCATE TABLE `{$to_tablepre}goods_show`");
$to_db->query("TRUNCATE TABLE `{$to_tablepre}goods_show_detail`");
$to_db->query("TRUNCATE TABLE `{$to_tablepre}show_gallery`");
$q=$from_db->query("SELECT uid,supplier_cat,supplier_cat2,supplier_cat3,goods_category,goods_code,goods_name,goods_brand,goods_stock,goods_sale_price,goods_file_mid,goods_addoption1,
                           goods_addoption2,goods_free_delivery,goods_hit,register_date,sellshow,
                           goods_key,goods_advance,goods_cost,goods_market_price,goods_kg,goods_file2,goods_main,supplier_id,
                           fax,address,contact,goods_url 
                    FROM `{$from_tablepre}goods_table`");
while($rtl=$from_db->fetch_array($q))
{
    $goods_status=0;
    if($rtl['goods_addoption1']==1) $goods_status|=1;
    if($rtl['goods_addoption2']==1) $goods_status|=2;
    if($rtl['goods_free_delivery']==1) $goods_status|=4;
    
    $goods_row=array(
        'uid'=>$rtl['uid'],
        'supplier_cat'=>$rtl['supplier_cat'],
        'supplier_cat2'=>$rtl['supplier_cat2'],
        'supplier_cat3'=>$rtl['supplier_cat3'],
        'goods_status'=>$rtl['goods_status'],
        'goods_category'=>$rtl['goods_category'],
        'goods_code'=>$rtl['goods_code'],
        'goods_name'=>$rtl['goods_name'],
        'goods_brand'=>$rtl['goods_brand'],
        'goods_stock'=>$rtl['goods_stock'],
        'goods_sale_price'=>$rtl['goods_sale_price'],
        'goods_file1'=>$rtl['goods_file_mid'],
        'goods_hit'=>$rtl['goods_hit'],
        'register_date'=>$rtl['register_date'],
        'supplier_id'=>$rtl['supplier_id']
    );
    
    $detail_row=array(
        'g_uid'=>$rtl['uid'],
        'goods_key'=>$rtl['goods_key'],
        'goods_advance'=>$rtl['goods_advance'],
        'goods_cost'=>$rtl['goods_cost'],
        'goods_market_price'=>$rtl['goods_market_price'],
        'goods_kg'=>$rtl['goods_kg']*1000,
        'goods_file2'=>$rtl['goods_file2'],
        'goods_main'=>daddslashes($rtl['goods_main']),
    );
    if($rtl['sellshow']==0)    //销售型
    {
        $to_db->insert("`{$to_tablepre}goods_table`",$goods_row);
        $to_db->insert("`{$to_tablepre}goods_detail`",$detail_row);
    }
    else if($rtl['sellshow']==1)    //展示型
    {
        $to_db->insert("`{$to_tablepre}goods_show`",$goods_row);
        $detail_row['goods_url']=$rtl['goods_url'];
        $detail_row['tel']=$rtl['tel'];
        $detail_row['address']=$rtl['address'];
        $to_db->insert("`{$to_tablepre}goods_show_detail`",$detail_row);
    }
    
    
    $gallery_q=$from_db->query("SELECT imgid,goods_id,imgbig,thumb,supplier_id FROM `{$from_tablepre}gallery` WHERE goods_id='$rtl[uid]'");
    while($rtl_gallery=$from_db->fetch_array($gallery_q))
    {
        if($rtl['sellshow']==0)
        {
            $to_db->insert("`{$to_tablepre}gallery`",$rtl_gallery);
        }
        else if($rtl['sellshow']==1)
        {
            $to_db->insert("`{$to_tablepre}show_gallery`",$rtl_gallery);
        }
        unset($rtl_gallery);
    }
    $from_db->free_result(1);
    
    unset($goods_row);
    unset($detail_row);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//order_info
$to_db->query("TRUNCATE TABLE `{$to_tablepre}order_info`");
$arr_order_uid_2_supplierid=array();
$q=$from_db->query("SELECT uid,ordersn,supplier_id,username,addtime,checktime,status,consignee,address,zipcode,mobile,sh_id,sh_exes,pay_id,pay_name,goods_amount,invoice,remark,
                           delivery_code,mark,admin_memo 
                    FROM `{$from_tablepre}order_info`");
while($rtl=$from_db->fetch_array($q))
{
    $arr_order_uid_2_supplierid[$rtl['uid']]=$rtl['supplier_id'];
    
    $rtl['sh_uid']=$rtl['sh_id'];
    $rtl['sh_price']=$rtl['sh_exes'];
    unset($rtl['sh_id']);
    unset($rtl['sh_exes']);
    $to_db->insert("`{$to_tablepre}order_info`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();

//order_goods
$to_db->query("TRUNCATE TABLE `{$to_tablepre}order_goods`");
$q=$from_db->query("SELECT uid,order_id,goods_id,goods_name,buy_number,buy_price,goods_attr,register_date FROM `{$from_tablepre}order_goods`");
while($rtl=$from_db->fetch_array($q))
{
    $rtl['g_uid']=$rtl['goods_id'];
    $rtl['goods_table']="{$to_tablepre}goods_table";
    $rtl['module']='product';
    $rtl['supplier_id']=$arr_order_uid_2_supplierid[$rtl['order_id']];
    unset($rtl['goods_id']);
    
    $to_db->insert("`{$to_tablepre}order_goods`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();
unset($arr_order_uid_2_supplierid);

//money_apply
$to_db->query("TRUNCATE TABLE `{$to_tablepre}money_apply`");
$q=$from_db->query("SELECT * FROM `{$from_tablepre}money_apply`");
while($rtl=$from_db->fetch_array($q))
{
    $m=$to_db->get_one("SELECT member_name FROM `{$to_tablepre}member_table` WHERE uid='$rtl[supplier_id]' LIMIT 1");
    $rtl['member_name']=$m['member_name'];
    $rtl['real_money']=$rtl['money'];
    
    $to_db->insert("`{$to_tablepre}money_apply`",$rtl);
    unset($rtl);
}
$from_db->free_result();
$to_db->free_result();



$end_time=time();
echo 'Success, total '.strval($end_time-$start_time).' seconds total';


function daddslashes(&$string, $force = 0)
{
	if(!get_magic_quotes_gpc() || $force)
	{
		if(is_array($string))
		{
			foreach($string as $key => $val) $string[$key] = daddslashes($val, $force);
		}
		else $string = addslashes($string);
	}
	return $string;
}

function DeleteDir($dir)
{
    if(!is_dir($dir)) return ;
	$dh=opendir($dir);
	while ($file=readdir($dh))
	{
		if($file=='.' || $file=='..') continue;
		$fullpath=$dir.$file;
		file_unlink($fullpath);
		if(is_dir($fullpath))
		{
			$fullpath.='/';
			DeleteDir($fullpath);
			rmdir($fullpath);
		}
	}
	closedir($dh);
}
?>