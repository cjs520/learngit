<?php
//广告相关配置
$ad_config=array();    
$ad_config['ad_type']=array(0=>'商品广告',1=>'商铺广告',2=>'其它类型广告');    //广告类型：0:商品;1:商铺;2:其它
//index
$ad_config['index']=array('module'=>'index',
                          'pos'=>array('active1','flash_ad',
						  'floor1_links','floor1_goods','floor1_banner','floor1_brands',
						  'floor2_links','floor2_goods','floor2_banner','floor2_brands',
						  'floor3_links','floor3_goods','floor3_banner','floor3_brands',
						  'floor4_links','floor4_goods','floor4_banner','floor4_brands',
						  'floor5_links','floor5_goods','floor5_banner','floor5_brands',
						  'floor6_links','floor6_goods','floor6_banner','floor6_brands',
						  'floor7_links','floor7_ad1',
						  'floor8_links','floor8_ad1',
						  'floor9_links','floor9_ad1',
						  'floor10_links','floor10_ad1'));
//sub
$ad_config['sub']=array('module'=>'sub',
                        'other_param'=>array(),
                        'pos'=>array('banner','notice','notice_img','recom','brands',
									 'cat_left_ad1','cat_right_ad1','cat_news1','floor_1',
									 'cat_left_ad2','cat_right_ad2','cat_news2','floor_2',
									 'cat_left_ad3','cat_right_ad3','cat_news3','floor_3'));
//brand
$ad_config['brand']=array('module'=>'brand','other_param'=>array('list','view'),'pos'=>array('banner'));
//miaosha
$ad_config['miaosha']=array('module'=>'miaosha','pos'=>array('banner'));
//auction
$ad_config['auction']=array('module'=>'auction','pos'=>array('banner'));
//coupon
$ad_config['coupon']=array('module'=>'coupon','pos'=>array('banner'));
//preorder
$ad_config['preorder']=array('module'=>'preorder','pos'=>array('banner'));
//point
$ad_config['point']=array('module'=>'point','pos'=>array('banner'));
//group
$ad_config['group']=array('module'=>'group','pos'=>array('banner','recommend'));
//tv_shopping
$ad_config['tv_shopping']=array('module'=>'tv_shopping','pos'=>array('recommend'));
//tv_detail
$ad_config['tv_detail']=array('module'=>'tv_detail','pos'=>array('banner','recommend'));
//news
$ad_config['news']=array('module'=>'news','pos'=>array('left_ad','banner'));
//lucky
$ad_config['lucky']=array('module'=>'lucky','pos'=>array('banner','recom_goods','hot_brand'));
//topic
$ad_config['topics']=array('module'=>'topics','other_param'=>array('topic1','topic2','topic3','topic4','topic5'),'pos'=>array('floor1','floor2','floor3','floor4','floor5','floor6','floor7','bottom_ad'));
//life
$ad_config['life']=array('module'=>'life','pos'=>array('banner'));
//infor_buy
$ad_config['infor_buy']=array('module'=>'infor_buy','other_param'=>array(),'pos'=>array('banner','recommend','cat_ad'));
//infor_supply
$ad_config['infor_supply']=array('module'=>'infor_supply','other_param'=>array(),'pos'=>array('banner','recommend','cat_ad'));
//infor_buy_detail
$ad_config['infor_buy_detail']=array('module'=>'infor_buy_detail','pos'=>array('cat_ad'));
//infor_supply_detail
$ad_config['infor_supply_detail']=array('module'=>'infor_supply_detail','pos'=>array('cat_ad'));
//sadmin
$ad_config['sadmin']=array('module'=>'sadmin','other_param'=>array('index'),'pos'=>array('banner'));
//member
$ad_config['member']=array('module'=>'member','other_param'=>array('index'),'pos'=>array('banner'));
//default_wap 模板
$ad_config['default_wap']=array('module'=>'default_wap','other_param'=>array(),'pos'=>array('banner','floor1_banner','floor1_brands','floor2_banner','floor2_brands','floor3_banner','floor3_brands','floor4_banner','floor4_brands','floor5_banner','floor5_brands','floor6_banner','floor6_brands'));
//firered_wap 模板
$ad_config['firered_wap']=array('module'=>'firered_wap','other_param'=>array(),'pos'=>array('topic','floor1_banner','floor2_banner','floor3_banner','floor4_banner','floor5_banner','floor6_banner','floor7_banner','floor8_banner','floor9_banner','floor10_banner'));
//red_wap 模板
$ad_config['red_wap']=array('module'=>'red_wap','other_param'=>array(),'pos'=>array('brands','topic','theme'));
//orange_wap 模板
$ad_config['orange_wap']=array('module'=>'orange_wap','other_param'=>array(),'pos'=>array('brands','topic','theme'));
//black_wap 模板
$ad_config['black_wap']=array('module'=>'black_wap','other_param'=>array(),'pos'=>array('brands','topic','theme'));
//blue_wap 模板
$ad_config['blue_wap']=array('module'=>'blue_wap','other_param'=>array(),'pos'=>array('sales','coupon','topic','theme'));
//cycle
$ad_config['cycle']=array('module'=>'cycle','other_param'=>array(),'pos'=>array('wap_infor_supply','wap_infor_buy','wap_news','wap_test'));

$cty=$cache->get_cache('right_tree');
foreach ($cty as $val)
{
	$ad_config['sub']['other_param'][]=$val['uid'];
	$ad_config['infor_buy']['other_param'][]=$val['uid'];
	$ad_config['infor_supply']['other_param'][]=$val['uid'];
}