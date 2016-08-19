<?php

/**
 * MVM_MALL 网上商店系统  购物车
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-02 $
 * $Id: cache.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

class cache
{
    private $db;
    private $tablepre;
    private $ch;
    
    public function __construct($db,$tablepre)
    {
        global $cache_settings;
        if($cache_settings['cache_type']=='memcache')
        {
            require_once 'include/cache/memcache_cache.class.php';
            $this->ch=new memcache_cache($this,false);
        }
        else if($cache_settings['cache_type']=='redis')
        {
            require_once 'include/cache/redis_cache.class.php';
            $this->ch=new redis_cache($this,false);
        }
        else
        {
            require_once 'include/cache/file_cache.class.php';
            $this->ch=new file_cache($this,false);
        }
        
        $this->db = $db;
        $this->tablepre = $tablepre;
    }
    
    public function write_cache($cache_file,$cache_data,$cache_var='cache')
    {
        $this->ch->write_cache($cache_file,$cache_data,$cache_var);
    }
    
    public function read_cache($cache_file,$cache_func,$cache_param,$cache_var='cache',$context=null)
    {
        $cache_data=$this->ch->read_cache($cache_file,$cache_func,$cache_param,$cache_var,$context);
        return $cache_data;
    }
    
    public function remove_cache($cache_file)
    {
        $this->ch->remove_cache($cache_file);
    }
    
    public function get_cache($cache_id)    //读取缓存
    {
        return $this->ch->get_cache($cache_id);
    }
     
    public function put_cache($cache_id)    //写入缓存
    {
        $arr=$this->ch->put_cache($cache_id);
        return $arr;
    }
    
    public function delete($cache_name,$supplier_id)
    {
        $this->ch->delete($cache_name,$supplier_id);
    }//end delete
    
    public function flush($only_main)
    {
        $this->ch->flush($only_main);
    }
    
    public function cfg()    //系统配置文件
    {
    	$arr = array();
        $query = $this->db->query("SELECT * FROM `{$this->tablepre}config` WHERE supplier_id='0'");
        while ($rt = $this->db->fetch_array($query))
        {
            $arr['cf_name'] = str_replace("'","\'",$rt['cf_name']);
            $arr[$rt['cf_name']] = $rt['cf_value'];
        }
        if($arr['mm_client_ww'])
        {
        	$arr['mm_client_ww_code'] = "<a target=\"_blank\" href=\"http://www.taobao.com/webww/ww.php?ver=3&touid=$arr[mm_client_ww]&siteid=cntaobao&status=2&charset=utf-8\" >
        	                             <img border=\"0\" src=\"http://amos.im.alisoft.com/online.aw?v=2&uid=$arr[mm_client_ww]&site=cntaobao&s=2&charset=utf-8\" alt=\"点击这里给我发消息\" />
        	                             </a>";
        }
        $arr['mm_logo']=IMG_URL.$arr['mm_logo'];
        $arr['mm_wx_logo']=IMG_URL.$arr['mm_wx_logo'];
        $arr['mm_wx_focus_img']=IMG_URL.$arr['mm_wx_focus_img'];
        $arr['mm_wx_shop_logo']=IMG_URL.$arr['mm_wx_shop_logo'];
        $arr['mm_wx_res_img']=IMG_URL.$arr['mm_wx_res_img'];
        $this->db->free_result(1);
        return $arr;
    }
    
    public function grade()    //会员等级
    {
        $arr = array(0=>'-- 请选择 --');
        $result = $this->db->query("SELECT group_name,group_id FROM `{$this->tablepre}grade_table` ORDER BY `degree`");
        while ($rt = $this->db->fetch_array($result)) $arr[$rt['group_id']] = $rt['group_name'];
        $this->db->free_result(1);
        return $arr;
    }
    
    public function nav()    //导航缓存文件处理
    {
        $nav_array = array();
        $rs	= $this->db->query("SELECT * FROM `{$this->tablepre}nav` WHERE `supplier_id`='0' ORDER BY view");
        while($navdb = $this->db->fetch_array($rs))
        {
        	$navdb['target']=$navdb['target']==1?'target="_blank"':'';
            $style_array = explode("|",$navdb['style']);
            $style_array[1] && $navdb['title']="<b>".$navdb['title']."</b>";
            $style_array[2] && $navdb['title']="<i>".$navdb['title']."</i>";
            $style_array[3] && $navdb['title']="<u>".$navdb['title']."</u>";
            $style_array[0] && $navdb['title']="<font color=\"$style_array[0]\">".$navdb['title']."</font>";
            
            $is_out_link=false;
            if(strtolower(substr($navdb['link'],0,4))=='http') $is_out_link=true;
            if(!$is_out_link)
            {
                $nav_link = parse_url($navdb['link']);
                $nav_query = explode("=",$nav_link['query']);
                $nav_name = explode(".",$nav_link['path']);
                $navdb['link'] = $nav_link['path']&&$nav_query[0] ? GetBaseUrl($nav_name[0],$nav_query[1],$nav_query[0]): GetBaseUrl($nav_name[0]);
            }
            
            $nav_array[]=$navdb;
        }
        $this->db->free_result(1);
        return $nav_array;
    }
    
    public function nav2()    //双层导航分级
    {
        $nav_array = array();
        $rs	= $this->db->query("SELECT * FROM `{$this->tablepre}nav` WHERE pid='0' AND supplier_id='0' ORDER BY view");
        while($navdb = $this->db->fetch_array($rs))
        {
            $style_array = explode("|",$navdb['style']);
            $style_array[1] && $navdb['title']="<b>".$navdb['title']."</b>";
            $style_array[2] && $navdb['title']="<i>".$navdb['title']."</i>";
            $style_array[3] && $navdb['title']="<u>".$navdb['title']."</u>";
            $style_array[0] && $navdb['title']="<font color=\"$style_array[0]\">".$navdb['title']."</font>";
            $navdb['target'] = $navdb['target'] ? "target=\"_blank\"" : "";
            
            $is_out_link=false;
            if(strtolower(substr($navdb['link'],0,4))=='http') $is_out_link=true;
            
            if(!$is_out_link)
            {
                $nav_link    = parse_url($navdb['link']);
                $nav_query   = explode("=",$nav_link['query']);
                $nav_name    = explode(".",$nav_link['path']);
                $navdb['link'] = $nav_link['path']&&$nav_query[0] ? GetBaseUrl($nav_name[0],$nav_query[1],$nav_query[0]): GetBaseUrl($nav_name[0]);
            }
            
            //获取第二级
            $navdb['child']=array();
            $q=$this->db->query("SELECT * FROM `{$this->tablepre}nav` WHERE pid='$navdb[nid]' AND supplier_id='0' ORDER BY view");
            while ($rtl=$this->db->fetch_array($q))
            {
            	$arrStyle = explode("|",$rtl['style']);
                $arrStyle[1] && $rtl['title']="<b>".$rtl['title']."</b>";
                $arrStyle[2] && $rtl['title']="<i>".$rtl['title']."</i>";
                $arrStyle[3] && $rtl['title']="<u>".$rtl['title']."</u>";
                $arrStyle[0] && $rtl['title']="<font color=\"$arrStyle[0]\">".$rtl['title']."</font>";
                $rtl['target'] = $rtl['target'] ? "target=\"_blank\"" : "";
                
                $is_out_link=false;
                if(strtolower(substr($rtl['link'],0,4))=='http') $is_out_link=true;
                if(!$is_out_link)
                {
                    $nav_link = parse_url($rtl['link']);
                    $nav_query = explode("=",$nav_link['query']);
                    $nav_name = explode(".",$nav_link['path']);
                    $rtl['link'] = $nav_link['path']&&$nav_query[0] ? GetBaseUrl($nav_name[0],$nav_query[1],$nav_query[0]): GetBaseUrl($nav_name[0]);
                }
            	$navdb['child'][]=$rtl;
            }
            $this->db->free_result(1);
            $nav_array[]=$navdb;
        }
        $this->db->free_result(1);
        return $nav_array;
    }
    
    public function right_tree()    //右边分类
    {
        $arr = array();
        $query = $this->db->query("SELECT uid,category_id,category_name,category_file1 
                                   FROM `{$this->tablepre}category` 
                                   WHERE category_id = '0' AND supplier_id='0' 
                                   ORDER BY category_rank");
        while($rt = $this->db->fetch_array($query))
        {
            $category=array();
            $sc_result = $this->db->query("SELECT uid,category_name,category_file1 
                                           FROM `{$this->tablepre}category` 
                                           WHERE category_id='$rt[uid]' AND supplier_id='0' 
                                           ORDER BY category_rank");
            while($rs = $this->db->fetch_array($sc_result))
            {
                $three_level=array();
                $q=$this->db->query("SELECT uid,category_name,category_file1 
                                     FROM `{$this->tablepre}category` 
                                     WHERE category_id='$rs[uid]' AND supplier_id='0' 
                                     ORDER BY category_rank");
                while($rtl=$this->db->fetch_array($q))
                {
                    $rtl['url'] = $GLOBALS['rewrite'] == '1' ? "category-$rtl[uid]-1.html":"category.php?action=$rtl[uid]";
                    $rtl['sub_url']=$GLOBALS['rewrite'] == '1' ? "sub-$rtl[uid]-1.html":"sub.php?action=$rtl[uid]";
                    $isUrl && $rtl['blank']='target="_blank"';
                    $rtl['title'] = $rtl['category_file1'] ? "<img src='$rtl[category_file1]' border='0' alt='$rtl[category_name]'> ":$rtl['category_name'];
                    if(!$rtl['category_file1'] || !@fopen($rtl['category_file1'],'r')) $rtl['category_file1']='images/noimages/no_cat.png';
                    $three_level[]=$rtl;
                }
                $this->db->free_result(1);
                $rs['children']=$three_level;
            	$rs['url'] = $GLOBALS['rewrite'] == '1' ? "category-$rs[uid]-1.html":"category.php?action=$rs[uid]";
                $rs['sub_url']=$GLOBALS['rewrite'] == '1' ? "sub-$rs[uid]-1.html":"sub.php?action=$rs[uid]";
                $isUrl && $rs['blank']='target="_blank"';
                $rs['title'] = $rs['category_file1'] ? "<img src='$rs[category_file1]' border='0' alt='$rs[category_name]'> ":$rs['category_name'];
                $category[] = $rs;
            }
            $this->db->free_result(1);
            $rt['children'] = $category;
            $rt['url'] = $GLOBALS['rewrite'] == '1' ? "category-$rt[uid]-1.html":"category.php?action=$rt[uid]";
            $rt['sub_url'] = $GLOBALS['rewrite'] == '1' ? "sub-$rt[uid]-1.html":"sub.php?action=$rt[uid]";
            $isUrl && $rt['blank']='target="_blank"';
            $rt['title'] = $rt['category_file1'] ? " <img src='$rt[category_file1]' border='0' alt='$rs[category_name]'> " : $rt['category_name'];
            $arr[] = $rt;
        }
        $this->db->free_result(1);
        return $arr;
    }
    
    public function brand()    //品牌
    {
        $mm_brand = array(0=>'--');
        $q = $this->db->query("SELECT id,brandname FROM `{$this->tablepre}brand_table` WHERE isCheck=1 ORDER BY `train`");
        while($rt = $this->db->fetch_array($q)) $mm_brand[$rt['id']] = $rt['brandname'];
        $this->db->free_result(1);
        return $mm_brand;
    }
    
    public function payment()    //支付方式
    {
        $payment=array();
        $q = $this->db->query("SELECT id,name,pay_desc,class_name FROM `{$this->tablepre}payment_table` WHERE supplier_id='0'");
	    while ($rtl = $this->db->fetch_array($q)) $payment[] = $rtl;
	    $this->db->free_result(1);
	    return $payment;
    }
    
    public function links()    //友情连接
    {
    	$friend_links = array();
        $q = $this->db->query("SELECT * FROM `{$this->tablepre}forumlinks_table` WHERE `supplier_id`='0' ORDER BY `displayorder` ");
        while($rt = $this->db->fetch_array($q))
        {
             $rt['title'] = $rt['name'];
             $rt['logo']= IMG_URL.$rt['logo'];
             $friend_links[] = $rt;
        }
        $this->db->free_result(1);
        return $friend_links;
    }
    
    public function get_hot_community()
    {
        $arr_community=array();

        $q=$this->db->query("SELECT uid,c_name,c_logo,m_id,c_proclaim 
                             FROM `{$this->tablepre}community` 
                             WHERE topic_num>0 
                             ORDER BY topic_num DESC 
                             LIMIT 6");
        while ($rtl=$this->db->fetch_array($q))
        {
        	$rtl['c_logo'] = IMG_URL.$rtl['c_logo'];
            if(!$rtl['c_logo'] || !@fopen($rtl['c_logo'],'r')) $rtl['c_logo']='images/noimages/noproduct.jpg';
            $rtl['detail_url']=GetBaseUrl('life_detail',$rtl['uid']);
            $rtl['join_url']=GetBaseUrl('life_join',$rtl['uid']);
            $rtl['register_date']=date('Y-m-d',$rtl['register_date']);

            $rtl_tmp=$this->db->get_one("SELECT COUNT(*) AS cnt FROM `{$this->tablepre}community_member` WHERE c_uid='$rtl[uid]' AND approval_date>10");
            $rtl['member_num']=$rtl_tmp['cnt'];

            $rtl_tmp=$this->db->get_one("SELECT COUNT(*) AS cnt FROM `{$this->tablepre}community_topic` WHERE c_uid='$rtl[uid]' AND approval_date>10");
            $rtl['topic_num']=$rtl_tmp['cnt'];

            $arr_community[]=$rtl;
        }
        $this->db->free_result(1);
        
        return $arr_community;
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////
    
    public function shop_level0_admin_menu()
    {
    	$menu=shop_level_admin_menu(0);
    	$rtl_menu=array();
    	$menu_parent=array();
    	foreach ($menu as $key=>$val)    //第一级
    	{
    		if($val['menu_id']!=0) continue;
    		$rtl_menu[$val['uid']]=$val;
    		$menu_parent[$val['uid']]=$val['menu_id'];
    	    unset($menu[$key]);
    	}
    	
    	foreach ($menu as $key=>$val)    //第二级
    	{
    		if(!key_exists($val['menu_id'],$rtl_menu)) continue;
    		if(!isset($rtl_menu[$val['menu_id']]['children'])) $rtl_menu[$val['menu_id']]['children']=array();
    		$rtl_menu[$val['menu_id']]['children'][$val['uid']]=$val;
    		$menu_parent[$val['uid']]=$val['menu_id'];
    		unset($menu[$key]);
    	}
    	
    	foreach ($menu as $key=>$val)    //第三级
    	{
    		if(!key_exists($val['menu_id'],$menu_parent)) continue;
    		if(!isset($rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children']))
    		    $rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children']=array();
    		$rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children'][$val['uid']]=$val;
    	}
    	return $rtl_menu;
    }
    
    public function shop_level1_admin_menu()
    {
    	$menu=shop_level_admin_menu(1);
    	$rtl_menu=array();
    	$menu_parent=array();
    	foreach ($menu as $key=>$val)    //第一级
    	{
    		if($val['menu_id']!=0) continue;
    		$rtl_menu[$val['uid']]=$val;
    		$menu_parent[$val['uid']]=$val['menu_id'];
    	    unset($menu[$key]);
    	}
    	
    	foreach ($menu as $key=>$val)    //第二级
    	{
    		if(!key_exists($val['menu_id'],$rtl_menu)) continue;
    		if(!isset($rtl_menu[$val['menu_id']]['children'])) $rtl_menu[$val['menu_id']]['children']=array();
    		$rtl_menu[$val['menu_id']]['children'][$val['uid']]=$val;
    		$menu_parent[$val['uid']]=$val['menu_id'];
    		unset($menu[$key]);
    	}
    	
    	foreach ($menu as $key=>$val)    //第三级
    	{
    		if(!key_exists($val['menu_id'],$menu_parent)) continue;
    		if(!isset($rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children']))
    		    $rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children']=array();
    		$rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children'][$val['uid']]=$val;
    	}
    	return $rtl_menu;
    }
    
    public function shop_level2_admin_menu()
    {
    	$menu=shop_level_admin_menu(2);
    	$rtl_menu=array();
    	$menu_parent=array();
    	foreach ($menu as $key=>$val)    //第一级
    	{
    		if($val['menu_id']!=0) continue;
    		$rtl_menu[$val['uid']]=$val;
    		$menu_parent[$val['uid']]=$val['menu_id'];
    	    unset($menu[$key]);
    	}
    	
    	foreach ($menu as $key=>$val)    //第二级
    	{
    		if(!key_exists($val['menu_id'],$rtl_menu)) continue;
    		if(!isset($rtl_menu[$val['menu_id']]['children'])) $rtl_menu[$val['menu_id']]['children']=array();
    		$rtl_menu[$val['menu_id']]['children'][$val['uid']]=$val;
    		$menu_parent[$val['uid']]=$val['menu_id'];
    		unset($menu[$key]);
    	}
    	
    	foreach ($menu as $key=>$val)    //第三级
    	{
    		if(!key_exists($val['menu_id'],$menu_parent)) continue;
    		if(!isset($rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children']))
    		    $rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children']=array();
    		$rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children'][$val['uid']]=$val;
    	}
    	return $rtl_menu;
    }
    
    public function shop_level3_admin_menu()
    {
    	$menu=shop_level_admin_menu(3);
    	$rtl_menu=array();
    	$menu_parent=array();
    	foreach ($menu as $key=>$val)    //第一级
    	{
    		if($val['menu_id']!=0) continue;
    		$rtl_menu[$val['uid']]=$val;
    		$menu_parent[$val['uid']]=$val['menu_id'];
    	    unset($menu[$key]);
    	}
    	
    	foreach ($menu as $key=>$val)    //第二级
    	{
    		if(!key_exists($val['menu_id'],$rtl_menu)) continue;
    		if(!isset($rtl_menu[$val['menu_id']]['children'])) $rtl_menu[$val['menu_id']]['children']=array();
    		$rtl_menu[$val['menu_id']]['children'][$val['uid']]=$val;
    		$menu_parent[$val['uid']]=$val['menu_id'];
    		unset($menu[$key]);
    	}
    	
    	foreach ($menu as $key=>$val)    //第三级
    	{
    		if(!key_exists($val['menu_id'],$menu_parent)) continue;
    		if(!isset($rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children']))
    		    $rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children']=array();
    		$rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children'][$val['uid']]=$val;
    	}
    	return $rtl_menu;
    }
    
    public function shop_level4_admin_menu()    //旗舰商铺后台菜单
    {
    	$menu=shop_level_admin_menu(4);
    	$rtl_menu=array();
    	$menu_parent=array();
    	foreach ($menu as $key=>$val)    //第一级
    	{
    		if($val['menu_id']!=0) continue;
    		$rtl_menu[$val['uid']]=$val;
    		$menu_parent[$val['uid']]=$val['menu_id'];
    	    unset($menu[$key]);
    	}
    	
    	foreach ($menu as $key=>$val)    //第二级
    	{
    		if(!key_exists($val['menu_id'],$rtl_menu)) continue;
    		if(!isset($rtl_menu[$val['menu_id']]['children'])) $rtl_menu[$val['menu_id']]['children']=array();
    		$rtl_menu[$val['menu_id']]['children'][$val['uid']]=$val;
    		$menu_parent[$val['uid']]=$val['menu_id'];
    		unset($menu[$key]);
    	}
    	
    	foreach ($menu as $key=>$val)    //第三级
    	{
    		if(!key_exists($val['menu_id'],$menu_parent)) continue;
    		if(!isset($rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children']))
    		    $rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children']=array();
    		$rtl_menu[$menu_parent[$val['menu_id']]]['children'][$val['menu_id']]['children'][$val['uid']]=$val;
    	}
    	return $rtl_menu;
    }
}//end class cache

function shop_level_admin_menu($shop_level)
{
	global $db,$tablepre;
	$shop_level=(int)$shop_level;
	!in_array($shop_level,array(0,1,2,3,4)) && $shop_level=0;
	$menu=array();
	$q=$db->query("SELECT uid,menu_id,menu_name,menu_url FROM `{$tablepre}admin_menu` WHERE `shop_level{$shop_level}`='1' ORDER BY menu_order,uid");
	while($rtl=$db->fetch_array($q))
	{
		$rtl['menu_url']=str_replace('admincp.php','sadmin.php',$rtl['menu_url']);
		$menu[]=$rtl;
	}
	$db->free_result(1);
	return $menu;
}