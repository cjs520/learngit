<?php
$cat_parent = $cache->get_cache('right_tree',$page_member_id);

/**系统url**/
$mm_url=array();
if($mm_domain_name) $mm_url['login'] = "$_URL[0]/logging.php?action=login&subrel=$mm_domain_name";
else $mm_url['login'] = GetBaseUrl('logging','login','action','1',$page_member_id,true);//登录
$mm_url['logout'] = GetBaseUrl('logging','logout','action','1',$page_member_id,true);//退出
$mm_url['user_reg'] = GetBaseUrl('register','','','1',$page_member_id,true);//注册
$mm_url['certification'] = GetBaseUrl('page','certification','action','1',0,true);//商铺认证
$mm_url['cart'] = GetBaseUrl('cart','list','action','1',0,true);//购物车
$mm_url['changegd'] = GetBaseUrl('changegd','list');
$mm_url['coupon'] = GetBaseUrl('coupon','list');
$mm_url['investment'] = GetBaseUrl('investment','','','',0,true);//我要开店