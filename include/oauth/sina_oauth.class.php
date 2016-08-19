<?php
require_once dirname(__FILE__).'/sina_sdk.class.php';

class sina_oauth
{
    private $app_data;
    private $callback_url;
    private $login_url;
    private $o_sina;
    private $oauth_type='sina';
    
    private $access_token;
    private $open_id;
    
    public function __construct($data)
    {
        global $mm_mall_url,$mm_refer_url;
        $this->callback_url=$mm_mall_url.'/oauth_back.php?oauth_type='.$this->oauth_type;
        $this->app_data=$data;
        
        $this->o_sina=new SaeTOAuthV2($this->app_data['app_key'],$this->app_data['app_secret']);
        $this->login_url=$this->o_sina->getAuthorizeURL($this->callback_url);
    }//end __construct
    
    public function __destruct(){}//end __destruct
    
    public function get_login_url()
    {
        return $this->login_url;
    }//end get_login_url
    
    public function callback()
    {
        if (isset($_REQUEST['code']))
        {
            $keys = array(
                'code'=>$_REQUEST['code'],
                'redirect_uri'=>$this->callback_url
            );
            try
            {
                $token = $this->o_sina->getAccessToken( 'code', $keys );
            }
            catch (OAuthException $e)
            {
                file_put_contents('data/log/'.date('Ymd').'.txt',date('Y-m-d H:i:s').'  '.$e->getMessage().PHP_EOL.PHP_EOL,FILE_APPEND);
            }
        }
        if ($token)
        {
	        //$_SESSION['token'] = $token;
	        //setcookie( 'weibojs_'.$o->client_id, http_build_query($token) );
	        $_SESSION['openid']=$token['uid'];
	        $_SESSION['access_token']=$token['access_token'];
	        $_SESSION['oauth_type']=$this->oauth_type;
	        
	        $this->destruct_state();
	        $this->get_member();    //获得本站会员信息
        }
        else
        {
            sleep(1);    //变态的新浪，不知道为什么会返回两次，这里就睡一秒再重新认身份吧
            move_page('oauth_back.php?oauth_type='.$this->oauth_type.'hack_type=sina&rnd='.strval(rand()),0,true);
            exit('check error');
        }
    }//end callback
    
    private function destruct_state()
    {
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
        $c = new SaeTClientV2($this->app_data['app_key'] , $this->app_data['app_secret'] , $this->get_access_token() );
        $user_message = $c->show_user_by_id($this->get_open_id());
        if($user_message['error']) exit($user_message['error']);
        $arr=array(
            'nickname'=>$user_message['screen_name']
        );
        return $arr;
    }//end get_user_blog_info
    
    public function get_oauth_data()
    {
        return array(
            'app_key'=>$this->app_data['app_key'],
            'app_secret'=>$this->app_data['app_secret'],
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
        $img=str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']).'/'.str_replace('./','',$img);
       
        $c = new SaeTClientV2($this->app_data['app_key'] , $this->app_data['app_secret'] , $this->get_access_token() );
        $response = $c->update( $cnt );
        
        /*
        if(!$img || !file_exists($img) || is_dir($img))    //无图
        {
            $response = $c->update( $cnt );
        }
        else    //有图
        {
            $response = $c->upload($cnt,$img);
        }
        */
        
        
        $msg='ok';
        if($response['error']) $msg=$response['error_code'].':'.$response['error'];
        
        return $msg;
    }//end post_to_weibo
    
    public function parse_event($data){}//end parse_event
}
?>