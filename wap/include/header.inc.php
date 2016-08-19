<?php
$cat_parent = $cache->get_cache('right_tree');

$mm_url=array();
$mm_url['index'] = GetBaseUrl('index');//首页
$mm_url['my_account'] = $mvm_member['isSupplier']>=2?'sadmin.php?module=index':'member.php?action=index';
$mm_url['investment'] = $mvm_member['isSupplier']>0?GetBaseUrl('index','','',$m_check_uid):GetBaseUrl('investment');
$mm_url['login'] = GetBaseUrl('logging','login');//登录
$mm_url['logout'] = GetBaseUrl('logging','logout');//退出
$mm_url['user_reg'] = GetBaseUrl('register');//注册
$mm_url['cart'] = GetBaseUrl('cart','list');//购物车
$mm_url['goodcat'] = GetBaseUrl('goodcat','');//所有分类
$mm_url['help'] = GetBaseUrl('help');//帮助中心