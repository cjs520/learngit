<?php
class cache
{
    private $cache_dir ;
    private $db;
    private $tablepre;
    private $ch;
    public $var_stack;   
    
    //为各缓存函数接受参数
    public function __construct($db,$tablepre)
    {
        global $cache_settings;
        if($cache_settings['cache_type']=='memcache')
        {
            require_once 'include/cache/memcache_cache.class.php';
            $this->ch=new memcache_cache($this,true);
        }
        else if($cache_settings['cache_type']=='redis')
        {
            require_once 'include/cache/redis_cache.class.php';
            $this->ch=new redis_cache($this,false);
        }
        else
        {
            require_once 'include/cache/file_cache.class.php';
            $this->ch=new file_cache($this,true);
        }
        
        $this->db = $db;
        $this->tablepre = $tablepre;
    }
    
    //读取缓存
    function get_cache($cache_id,$userid=0)
    {
        $rtl=$this->ch->get_cache($cache_id,$userid);
        
        unset($this->var_stack);    //销毁参数栈
        return $rtl;
    }
     //写入缓存
    public function put_cache($cache_id,$userid=0)
    {
    	$arr=$this->ch->put_cache($cache_id,$userid);
    	
        unset($this->var_stack);    //销毁参数栈
        return $arr;
    }
    
    //系统配置文件
    public function cfg()
    {
    	global $page_member_id;
    	$get_page_id=$this->var_stack['isMain']?0:$page_member_id;
        $arr = array();
        $query = $this->db->query("SELECT * FROM `{$this->tablepre}config` WHERE `supplier_id`='$get_page_id'");
        while ($rt = $this->db->fetch_array($query))
        {
            $arr['cf_name'] = str_replace("'","\'",$rt['cf_name']);
            $arr[$rt['cf_name']] = $rt['cf_value'];
        }
        if($arr['mm_client_ww'])
        {
        	$arr['mm_client_ww_code']="<a target=\"_blank\" href=\"http://www.taobao.com/webww/ww.php?ver=3&touid=$arr[mm_client_ww]&siteid=cntaobao&status=2&charset=utf-8\" >
        	                           <img border=\"0\" src=\"http://amos.im.alisoft.com/online.aw?v=2&uid=$arr[mm_client_ww]&site=cntaobao&s=2&charset=utf-8\" alt=\"点击这里给我发消息\" align=\"absmiddle\" />
        	                           </a>";
        }
        $this->db->free_result(1);
        return $arr;
    }
    
    //会员等级
    public function grade()
    {
        $arr = array(0=>'Guest');
        $q = $this->db->query("SELECT group_name,group_id FROM `{$this->tablepre}grade_table`");
        while ($rt = $this->db->fetch_array($q)) $arr[$rt['group_id']] = $rt['group_name'];
        $this->db->free_result(1);
        return $arr;
    }
    
    public function grade_discount()
    {
        global $page_member_id;
        $arr=array();
        $q=$this->db->query("SELECT group_id,discount FROM `{$this->tablepre}grade_discount` WHERE supplier_id='$page_member_id'");
        while ($rtl=$this->db->fetch_array($q))
        {
            $arr[$rtl['group_id']]=$rtl['discount'];
        }
        $this->db->free_result(1);
        return $arr;
    }
    
    //导航缓存文件处理
    public function nav()
    {
    	global $page_member_id,$_URL,$main_settings;
    	$get_page_id=$this->var_stack['isMain']?0:$page_member_id;
        $nav_array = array();
        $rs	= $this->db->query("SELECT * FROM `{$this->tablepre}nav` WHERE `supplier_id`='$get_page_id' ORDER BY view");
        while($navdb = $this->db->fetch_array($rs))
        {
        	$navdb['target']=$navdb['target']==1?'target="_blank"':'';
            $style_array    = explode("|",$navdb['style']);
            $style_array[1] && $navdb['title']="<b>".$navdb['title']."</b>";
            $style_array[2] && $navdb['title']="<i>".$navdb['title']."</i>";
            $style_array[3] && $navdb['title']="<u>".$navdb['title']."</u>";
            $style_array[0] && $navdb['title']="<font color=\"$style_array[0]\">".$navdb['title']."</font>";
            $navdb['target'] = $navdb['target'] ? "target=\"_blank\"" : "";
            $is_out_link=false;
            if(strtolower(substr($navdb['link'],0,4))=='http') $is_out_link=true;
            
            if(!$is_out_link)
            {
                $nav_link = parse_url($navdb['link']);
                $nav_query = explode("=",$nav_link['query']);
                $nav_name = explode(".",$nav_link['path']);
                
                $navdb['link'] = $nav_link['path'] && $nav_query[0] ? 
                                 GetBaseUrl($nav_name[0],$nav_query[1],$nav_query[0],'1',$get_page_id,(int)$get_page_id==0): 
                                 GetBaseUrl($nav_name[0],'','','1',$get_page_id,(int)$get_page_id==0);
            }
            $nav_array[]=$navdb;
        }
        $this->db->free_result(1);
        unset($this->var_stack);
        return $nav_array;
    }
    
    public function nav2()
    {
        $nav_array = array();
        $rs	= $this->db->query("SELECT nid,title,link,target FROM `{$this->tablepre}nav` 
                                WHERE pid='0' AND `supplier_id`='0' AND pos='help' 
                                ORDER BY view");    //modify by dxd
        while($navdb = $this->db->fetch_array($rs))
        {
            $navdb['target'] = $navdb['target'] ? "target=\"_blank\"" : "";
            
            $is_out_link=false;
            if(strtolower(substr($navdb['link'],0,4))=='http') $is_out_link=true;
            if(!$is_out_link)
            {
                $nav_link = parse_url($navdb['link']);
                $nav_query = explode("=",$nav_link['query']);
                $nav_name = explode(".",$nav_link['path']);
                $navdb['link'] = $nav_link['path'] && $nav_query[0] ? 
                                 GetBaseUrl($nav_name[0],$nav_query[1],$nav_query[0],'1',$get_page_id,(int)$get_page_id==0): 
                                 GetBaseUrl($nav_name[0],'','','1',$get_page_id,(int)$get_page_id==0);
            }
            
            //获取第二级 add by dxd
            $navdb['child']=array();
            $q=$this->db->query("SELECT nid,title,link,target 
                                 FROM `{$this->tablepre}nav` WHERE pid='$navdb[nid]' AND `supplier_id`='0' 
                                 ORDER BY view");
            while ($rtl=$this->db->fetch_array($q))
            {
                $rtl['target'] = $rtl['target'] ? "target=\"_blank\"" : "";
                $is_out_link=false;
                if(strtolower(substr($rtl['link'],0,4))=='http') $is_out_link=true;
                if(!$is_out_link)
                {
                    $nav_link = parse_url($rtl['link']);
                    $nav_query = explode("=",$nav_link['query']);
                    $nav_name = explode(".",$nav_link['path']);
                    $rtl['link'] = $nav_link['path'] && $nav_query[0] ? 
                                   GetBaseUrl($nav_name[0],$nav_query[1],$nav_query[0],'1',$get_page_id,(int)$get_page_id==0): 
                                   GetBaseUrl($nav_name[0],'','','1',$get_page_id,(int)$get_page_id==0);
                }
            	$navdb['child'][]=$rtl;
            }
            $this->db->free_result(1);
            $nav_array[]=$navdb;
        }
        $this->db->free_result(1);
        return $nav_array;
    }
    
    //右边分类
    public function right_tree()
    {
    	global $page_member_id;
    	$pmi=$this->var_stack['isMain']?0:$page_member_id;    //标识是否取得主站的分类
        $arr = array();
        $query = $this->db->query("SELECT uid,category_id,category_name,category_key,category_file1 
                                   FROM `{$this->tablepre}category` 
                                   WHERE category_id = '0' AND `supplier_id`='$pmi' 
                                   ORDER BY category_rank");
        while($rt = $this->db->fetch_array($query))
        {
            $sc_result = $this->db->query("SELECT uid,category_name,category_key,category_file1 
                                           FROM `{$this->tablepre}category` 
                                           WHERE category_id='$rt[uid]' AND `supplier_id`='$pmi' 
                                           ORDER BY category_rank");
            $category = array();
            while($rs = $this->db->fetch_array($sc_result))
            {
            	$rs['url'] = GetBaseUrl('category',$rs['uid'],'action','1',$pmi,$this->var_stack['isMain']);
                $isUrl && $rs['blank']='target="_blank"';
                if(!$rs['category_file1'])
                {
                    $rs['category_file1']='images/noimages/nocategory.jpg';
                    $rs['title']="<span>$rs[category_name]</span>";
                }
                else
                {
                    $rs['title'] = '<img src='.ProcImgPath($rs[category_file1])." border='0' alt='$rs[category_name]'>";
                }
                $category[] = $rs;
            }
            $this->db->free_result(1);
            $rt['children']=$category;
            
            $rt['url'] = GetBaseUrl('category',$rt['uid'],'action','1',$pmi,$this->var_stack['isMain']);
            $isUrl && $rt['blank']='target="_blank"';
            if(!$rt['category_file1'])
            {
                $rt['category_file1']='images/noimages/nocategory.jpg';
                $rt['title']="<span>$rt[category_name]</span>";
            }
            else
            {
                $rt['title']='<img src='.ProcImgPath($rt[category_file1])." border='0' alt='$rs[category_name]'>";
            }
            $arr[] = $rt;
        }
        $this->db->free_result(1);
        return $arr;
    }
     
     //友情连接
     public function links()
     {
     	 global $page_member_id;
         $friend_links = array();
         $q = $this->db->query("SELECT * FROM `{$this->tablepre}forumlinks_table` WHERE `supplier_id`='$page_member_id' ORDER BY `displayorder` ");
         while($rt = $this->db->fetch_array($q))
         {
             $rt['title'] = $rt['name'];
             $rt['logo']=ProcImgPath($rt['logo']);
             $friend_links[] = $rt;
         }
         $this->db->free_result(1);
         return $friend_links;
     }
     
     public function banner_array()
     {
     	global $page_member_id,$m_now_time;
     	$banner_array=array();
     	$q = $this->db->query("SELECT * FROM `{$this->tablepre}banner_table` WHERE `supplier_id`='$page_member_id' ORDER BY uid");
        while($rtl = $this->db->fetch_array($q))
        {
            if(!is_array($banner_array[$rtl['banner_point']])) $banner_array[$rtl['banner_point']]=array();
            $banner_array[$rtl['banner_point']][]=$rtl;
        }
        $this->db->free_result(1);
        return $banner_array;
     }
     
     public function shop_category()
     {
         global $page_member_id;
         $arr=array();
         
         $q=$this->db->query("SELECT uid,category_id,category_rank FROM `{$this->tablepre}category` WHERE supplier_id='$page_member_id' ORDER BY category_rank");
         while ($rtl=$this->db->fetch_array($q))
         {
             $arr[]=$rtl;
         }
         $this->db->free_result(1);
         return $arr;
     }
    
    public function get_hot_community()
    {
        global $main_settings;
        $arr_community=array();

        $q=$this->db->query("SELECT uid,c_name,c_logo,m_id,c_proclaim 
                             FROM `{$this->tablepre}community` 
                             WHERE topic_num>0 
                             ORDER BY topic_num DESC 
                             LIMIT 6");
        while ($rtl=$this->db->fetch_array($q))
        {
            if(!$rtl['c_logo']) $rtl['c_logo']='images/noimages/noproduct.jpg';
            else $rtl['c_logo']=$main_settings['mm_mall_url'].'/'.$rtl['c_logo'];
            $rtl['detail_url']=GetBaseUrl('life_detail',$rtl['uid'],'action',1,0,true);
            $rtl['join_url']=GetBaseUrl('life_join',$rtl['uid'],'action',1,0,true);
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
}