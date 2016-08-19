<?php
class youku_oauth
{
    private $app_data;
    private $callback_url;
    private $login_url;
    private $signature;
    private $oauth_type='youku';
    
    private $access_token;
    private $code;
    
    public function __construct($data)
    {
        global $mm_mall_url,$mm_refer_url;
        $this->callback_url=$mm_mall_url.'/oauth_back.php?oauth_type='.$this->oauth_type;
        $this->app_data=$data;
        
        $this->login_url="https://openapi.youku.com/v2/oauth2/authorize?client_id=".$this->app_data['client_id']."&response_type=code&redirect_uri=".$this->callback_url."&state=xyz";
        
    }//end __construct
    
    public function __destruct(){}//end __destruct
    
    public function get_login_url()
    {
        return $this->login_url;
    }//end get_login_url
    
    public function callback()
    {
        if($_GET['code'])
        {
            $this->code=$_GET['code'];
            $arr_post_data=array(
                'client_id'=>$this->app_data['client_id'],
                'client_secret'=>$this->app_data['client_secret'],
                'grant_type'=>'authorization_code',
                'code'=>$this->code,
                'redirect_uri'=>$this->callback_url
            );
            $str_json=$this->get_data('https://openapi.youku.com/v2/oauth2/token',true,$arr_post_data);
            if(!$str_json) exit('ERR:youku access token return error');
            $arr_json=json_decode($str_json);
            $_SESSION['access_token']=$arr_json->access_token;
            $_SESSION['access_token_expire']=$GLOBALS['m_now_time']+$arr_json->expires_in;
            move_page('sadmin.php?module=video_youku&action=list');
        }
        exit('ERR:youku code return error');
    }//end callback
    
    private function destruct_state()
    {
    }//end destruct_state
    
    public function get_access_token()
    {
        if($_SESSION['access_token'] && $GLOBALS['m_now_time']<$_SESSION['access_token_expire']) return $_SESSION['access_token'];
        return '';
    }//end get_access_token
    
    public function get_open_id()
    {
        return $this->code;
    }//end get_open_id
    
    public function get_oauth_type()
    {
        return $_SESSION['oauth_type'];
    }//end get_login_type
    
    public function get_member()
    {
        $_SESSION['oauth_type']=$this->oauth_type;
    }//end get_member
    
    public function get_user_blog_info()
    {
    }//end get_user_blog_info
    
    public function get_oauth_data()
    {
        return array(
            'client_id'=>$this->app_data['client_id'],
            'client_secret'=>$this->app_data['client_secret'],
            'code'=>$this->get_open_id(),
            'access_token'=>$this->get_access_token()
        );
    }//end get_oauth_data
    
    private function get_data($url,$is_post=false,$post_data=false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
        if($is_post)
        {
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
        }
        $response = curl_exec($ch);  
        curl_close($ch);
        
        return $response;
    }//end get_data
    
    public function post_to_tblog($cnt,$img){}//end post_to_weibo
    public function parse_event($data){}//end parse_event
}
?>