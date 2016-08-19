<?php
class ucache
{
    private $cache_dir;
    private $db;
    private $tablepre;
    private $var_stack;    //为各缓存函数接受参数
    private $ch;
    
    public function __construct($db,$tablepre)
    {
        global $cache_settings;

        $this->cache_dir='union/data/cache/';
        $this->db = $db;
        $this->tablepre = $tablepre;
        if($cache_settings['cache_type']=='memcache')
        {
            require_once 'include/cache/memcache_cache.class.php';
            $this->ch=new memcache_cache($this,true);
        }
        else if($cache_settings['cache_type']=='redis')
        {
            require_once 'include/cache/redis_cache.class.php';
            $this->ch=new redis_cache($this,true);
        }
        else
        {
            require_once 'include/cache/file_cache.class.php';
            $this->ch=new file_cache($this,true);
        }
    }
    //读取缓存
    public function get_cache($cache_id,$userid=0)
    {
        $cache_data=$this->ch->get_cache($cache_id,$userid);
        unset($this->var_stack);    //销毁参数栈
        return $cache_data;
    }
    
    //写入缓存
    public function put_cache($cache_id,$userid=0)
    {
    	$cache_data=$this->ch->put_cache($cache_id,$userid);
        unset($this->var_stack);    //销毁参数栈
        return $cache_data;
    }
    
    //开始定义缓存功能函数
    public function flush()
    {
        if($cache_settings['cache_type']=='memcache'){}
        else
        {
            $cache_path="union/data/cache/user_cache/{$page_member_id}/";
            DeleteDir($cache_path);
        }
    }//end flush
    
    //系统配置文件
    public function cfg()
    {
    	global $page_member_id;
    	$get_page_id=$this->var_stack['isMain']?0:$page_member_id;
        $arr = array();
        $query = $this->db->query("SELECT * FROM `{$this->tablepre}config` WHERE `supplier_id`='$get_page_id'");
        while ($rt = $this->db->fetch_array($query)){
            $arr['cf_name']         = str_replace("'","\'",$rt['cf_name']);
            $arr[$rt['cf_name']] = $rt['cf_value'];
        }
        if($arr['mm_client_ww'])
        {
        	$arr['mm_client_ww_code']="<a target=\"_blank\" href=\"http://www.taobao.com/webww/ww.php?ver=3&touid=$arr[mm_client_ww]&siteid=cntaobao&status=2&charset=utf-8\" ><img border=\"0\" src=\"http://amos.im.alisoft.com/online.aw?v=2&uid=$arr[mm_client_ww]&site=cntaobao&s=2&charset=utf-8\" alt=\"点击这里给我发消息\" /></a>";
        }
        return $arr;
    }
    
    public function brand()    //品牌
    {
        $mm_brand = array();
        $result = $this->db->query("SELECT id,brandname FROM `{$this->tablepre}brand_table` WHERE isCheck='1' ORDER BY `id` DESC");
        while($rt = $this->db->fetch_array($result))
        {
            $mm_brand[0] = '--';
            $mm_brand[$rt['id']] = $rt['brandname'];
        }
        return $mm_brand;
    }
    
    //所有的分类树
    public function tree()
    {
    	global $page_member_id;
    	$pmi=$this->var_stack['isMain']?0:(int)$page_member_id;    //标识是否取得主站的分类
        require_once 'include/category_tree.class.php';
        $tree = new tree();
        //查询数据库，返回分类的ＩＤ，名称，父类３个字段
        $result = $this->db->query("SELECT uid,category_name,category_id,category_rank FROM `{$this->tablepre}category` 
                                    WHERE `supplier_id`=$pmi 
                                    ORDER BY category_id,category_rank ASC");
        //遍历结果集，并压入无限分类
        while ($rt = $this->db->fetch_array($result))
        {
            $tree->new_node($rt['uid'],$rt['category_name'],$rt['category_id'],0,$rt['category_rank'],array());
        }
        
        return $tree->get_childs() ;
    }
}