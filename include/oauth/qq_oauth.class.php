<?php
class qq_oauth
{
    private $app_data;
    private $callback_url;
    private $login_url;
    private $signature;
    private $oauth_type='qq';
    
    private $access_token;
    private $open_id;
    
    public function __construct($data)
    {
        global $mm_mall_url,$mm_refer_url;
        $this->callback_url=$mm_mall_url.'/oauth_back-'.$this->oauth_type.'-1.html';
        $this->app_data=$data;
        
        if(isset($_SESSION['qq_signature']) && $_SESSION['qq_signature']) $this->signature=$_SESSION['qq_signature'];
        else $_SESSION['qq_signature']=$this->signature=md5(uniqid(rand(),true));
        
        $this->login_url="https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" 
                         . $this->app_data['app_id'] . "&redirect_uri=" . urlencode($this->callback_url)
                         . "&state=" . $this->signature
                         . "&scope=get_user_info,upload_pic,add_topic,add_t,add_pic_t";
        
    }//end __construct
    
    public function __destruct(){}//end __destruct
    
    public function get_login_url()
    {
        return $this->login_url;
    }//end get_login_url
    
    public function callback()
    {
        if($_REQUEST['state'] != $_SESSION['qq_signature']) exit('check error');
        
        //get access token
        $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
            . "client_id=" . $this->app_data['app_id']. "&redirect_uri=" . urlencode($this->callback_url)
            . "&client_secret=" . $this->app_data['app_key']. "&code=" . $_REQUEST["code"];
        
        $response=$this->get_data($token_url);
        if (strpos($response, "callback") !== false)
        {
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);
            if (isset($msg->error))
            {
                echo "<h3>error:</h3>" . $msg->error;
                echo "<h3>msg  :</h3>" . $msg->error_description;
                exit;
            }
        }
        
        $params = array();
        parse_str($response, $params);
        $this->access_token=$_SESSION["access_token"]=$params["access_token"];
        
        //get open uid
        $graph_url = "https://graph.qq.com/oauth2.0/me?access_token={$this->access_token}" ;
        $str=$this->get_data($graph_url);
        if (strpos($str, "callback") !== false)
        {
            $lpos = strpos($str, "(");
            $rpos = strrpos($str, ")");
            $str  = substr($str, $lpos + 1, $rpos - $lpos -1);
        }
        $user = json_decode($str);
        if (isset($user->error))
        {
            echo "<h3>error:</h3>" . $user->error;
            echo "<h3>msg  :</h3>" . $user->error_description;
            exit;
        }
        //set openid to session
        $this->open_id= $_SESSION["openid"] = $user->openid;
        
        $this->destruct_state();
        
        $_SESSION['oauth_type']=$this->oauth_type;
        
        $this->get_member();    //获得本站会员信息
    }//end callback
    
    private function destruct_state()
    {
        $_SESSION['qq_signature']='';
        unset($_SESSION['qq_signature']);
    }//end destruct_state
    
    public function get_access_token()
    {
        return $_SESSION['access_token'];
    }//end get_access_token
    
    public function get_open_id()
    {
        return $_SESSION['openid'];
    }//end get_open_id
    
    public function get_oauth_type()
    {
        return $_SESSION['oauth_type'];
    }//end get_login_type
    
    public function get_member()
    {
        global $db,$tablepre,$m_check_uid,$m_now_time,$mvm_member;
        $open_id=$this->get_open_id();
        $access_token=$this->get_access_token();
        
        if($m_check_uid)    //本站会员，并且已登录，本分支适用于后期绑定，当前用于商户
        {
            $row=array(
                'm_uid'=>$m_check_uid,
                'oauth_uid'=>$open_id,
                'token'=>$access_token,
                'type'=>$this->oauth_type
            );
            $db->replace("`{$tablepre}member_oauth`",$row);
            $db->free_result();
            move_page($mvm_member['isSupplier']>0?'sadmin.php?module=index':'member.php?action=index');
        }
        else    //未登录
        {
            $member_oauth=$db->get_one("SELECT m_uid FROM `{$tablepre}member_oauth` WHERE type='{$this->oauth_type}' AND oauth_uid='$open_id' LIMIT 1");
            if($member_oauth) $member=$db->get_one("SELECT uid,member_id,member_class FROM `{$tablepre}member_table` WHERE uid='$member_oauth[m_uid]' LIMIT 1");
            if($member)    //是本站会员，但还未在本站登录
            {
                $_SESSION['user']['mvm_sess_uid'] = $member['uid'];
                $_SESSION['user']['mvm_sess_id'] = $member['member_id'];

                $grade=$db->get_one("SELECT is_admin,rank_list FROM `{$tablepre}grade_table` WHERE group_id='$member[member_class]' LIMIT 1");
                $_SESSION['user']['mvm_is_admin']=$grade['is_admin'];
                if($grade['is_admin']==1) $_SESSION['user']['mvm_rank_list']=$grade['rank_list'];
                
                //统计数据
                $statistics=$db->get_one("SELECT login_time,last_login_time,total_login FROM `{$tablepre}member_statistics` WHERE m_uid='$member[uid]' LIMIT 1");
                if($statistics)
                {
                    $statistics['last_login_time']=$statistics['login_time'];
                    $statistics['login_time']=$m_now_time;
                    $statistics['total_login']++;
                    $db->update("`{$tablepre}member_statistics`",$statistics," m_uid='$member[uid]' ");
                }
                else
                {
                    $db->query("REPLACE INTO `{$tablepre}member_statistics` (m_id,last_login_time,login_time,total_login,m_uid)
                                VALUES ('$member[member_id]','$m_now_time','$m_now_time','1','$member[uid]')");
                }
                $db->free_result();
                
                require_once 'include/cart.class.php';
                $mvm_member=$member;
                $cart=new cart();
                $cart->update_discount();
                
                move_page($_SESSION['refer_url']);
            }
            else    //不是本站会员
            {
                move_page('register.php?oauth='.$this->get_oauth_type());
                exit;
            }
        }
    }//end get_member
    
    public function get_user_blog_info()
    {
        $get_user_info_url = "https://graph.qq.com/user/get_user_info?"
                             . "access_token=" . $this->get_access_token()
                             . "&oauth_consumer_key=" . $this->app_data['app_id']
                             . "&openid=" . $this->get_open_id()
                             . "&format=json";
                             
        $info = $this->get_data($get_user_info_url);
        $arr = json_decode($info, true);
        
        return $arr;
    }//end get_user_blog_info
    
    public function get_oauth_data()
    {
        return array(
            'app_id'=>$this->app_data['app_id'],
            'app_key'=>$this->app_data['app_key'],
            'open_id'=>$this->get_open_id(),
            'access_token'=>$this->get_access_token()
        );
    }//end get_oauth_data
    
    private function get_data($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
        $response = curl_exec($ch);  
        curl_close($ch);
        
        return $response;
    }//end get_data
    
    public function post_to_tblog($cnt,$img)
    {
        $url  = 'https://graph.qq.com/t/add_pic_t';
        $img=str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']).'/'.str_replace('./','',$img);
        $data=array(
            'access_token'=>$this->get_access_token(),
            'oauth_consumer_key'=>$this->app_data['app_id'],
            'openid'=>$this->get_open_id(),
            'format'=>'json',
            'type'=>'1',
            'content'=>str_replace('%26','&',$cnt),
            'pic'=>"@$img",
            'clientip'=>$_SERVER['REMOTE_ADDR'],
            'rnd'=>strval(rand(100,000))
        );
        
        if(!$img || !file_exists($img) || is_dir($img))
        {
            $url='https://graph.qq.com/t/add_t';
            unset($data['pic']);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
        curl_setopt($ch, CURLOPT_POST, TRUE); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  
        $response = curl_exec($ch);
        curl_close($ch);
        
        $arr=json_decode($response,true);
        if(!$arr) return '未知的错误';
        if((int)$arr['ret']) $arr['msg']=$arr['ret'].':'.$arr['errcode'].':'.$arr['msg'];
        return $arr['msg'];
    }//end post_to_weibo
    
    public function parse_event($data){}//end parse_event
}
?>