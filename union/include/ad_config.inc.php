<?php 
//广告相关配置
$ad_config=array();    
$ad_config['ad_type']=array(0=>'商品广告',1=>'商铺广告',2=>'其它类型广告');    //广告类型：0:商品;1:商铺;2:其它
//index
$ad_config['index']=array('module'=>'index','pos'=>array('notice','hot','brand_pos','brand_pos_small','new','tabad1_goods','tabad1_news','tabad1_banner','tabad1_lcolume','tabad1_rcolume','tabad2_goods','tabad2_news','tabad2_banner','tabad2_lcolume','tabad2_rcolume','tabad3_goods','tabad3_news','tabad3_banner','tabad3_lcolume','tabad3_rcolume','tabad4_goods','tabad4_news','tabad4_banner','tabad4_lcolume','tabad4_rcolume'));
//sub
$ad_config['sub']=array('module'=>'sub','other_param'=>array(),'pos'=>array('sale','tip','banner','hot','cat_ad1','cat_ad2','cat_ad3','cat_ad4','cat_ad5','cat_shop1','cat_shop2','cat_shop3','cat_shop4','cat_shop5'));
$cache->var_stack['isMain']=true;
$cty=$cache->get_cache('right_tree');
foreach ($cty as $val)
{
	$ad_config['sub']['other_param'][]=$val['uid'];
	foreach ($val['children'] as $ckey=>$cval)
	{
		$ad_config['sub']['other_param'][]=$cval['uid'];
	}
}
sort($ad_config['sub']['other_param']);
//category
$ad_config['category']=array('module'=>'category','pos'=>array('recommend'));
//goodcat
$ad_config['goodcat']=array('module'=>'goodcat','pos'=>array('recommend'));
//brand
$ad_config['brand']=array('module'=>'brand','other_param'=>array('index'),'pos'=>array('news','banner','certificate','story','brand_mini2','special','shop1','brand_ad1','brand_goods1','brand_shop1','brand_shop_news1','brand_ad2','brand_goods2','brand_shop2','brand_shop_news2','brand_ad3','brand_goods3','brand_shop3','brand_shop_news3'));
//gift
$ad_config['gift']=array('module'=>'gift','pos'=>array('news','banner','certificate','story','brand_mini2','special','shop1','brand_ad1','brand_goods1','brand_shop1','brand_shop_news1','brand_ad2','brand_goods2','brand_shop2','brand_shop_news2','brand_ad3','brand_goods3','brand_shop3','brand_shop_news3'));
//onsale
$ad_config['onsale']=array('module'=>'onsale','pos'=>array('news','banner','certificate','story','brand_mini2','special','shop1','brand_ad1','brand_goods1','brand_shop1','brand_shop_news1','brand_ad2','brand_goods2','brand_shop2','brand_shop_news2','brand_ad3','brand_goods3','brand_shop3','brand_shop_news3'));
//shop
$ad_config['shop']=array('module'=>'shop','pos'=>array('recommend'));
//search
$ad_config['search']=array('module'=>'search','pos'=>array('recommend'));
//shopcat
$ad_config['shopcat']=array('module'=>'shopcat','pos'=>array('shopcat'));