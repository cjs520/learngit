<?php
define('OAUTH_QQ',0);
define('OAUTH_SINA',1);
define('OAUTH_WX',3);
define('OAUTH_YOUKU',4);

class mvm_oauth
{
    private $app_data;
    private $oauth;
    
    public function __construct($data,$type=OAUTH_QQ)
    {
        $this->app_data=$data;
        switch ($type)
        {
            case OAUTH_QQ:
                require_once dirname(__FILE__).'/oauth/qq_oauth.class.php';
                $this->oauth=new qq_oauth($data);
                break;
            case OAUTH_SINA:
                require_once dirname(__FILE__).'/oauth/sina_oauth.class.php';
                $this->oauth=new sina_oauth($data);
                break;
            case OAUTH_WX:
                require_once dirname(__FILE__).'/oauth/wx_oauth.class.php';
                $this->oauth=new wx_oauth($data);
                break;
            case OAUTH_YOUKU:
                require_once dirname(__FILE__).'/oauth/youku_oauth.class.php';
                $this->oauth=new youku_oauth($data);
                break;
            default:
                exit('tblog type error');
                break;
        }
    }//end __construct
    
    public function __destruct(){}//end __destruct
    
    public function get_login_url()
    {
        return $this->oauth->get_login_url();
    }//end get_login_url
    
    public function callback()
    {
        $this->oauth->callback();
    }//end callback
    
    public function get_oauth_type()
    {
        return $this->oauth->get_oauth_type();
    }//end get_oauth_type
    
    public function get_user_blog_info()
    {
        return $this->oauth->get_user_blog_info();
    }//end get_user_blog_info
    
    public function get_oauth_data()
    {
        return $this->oauth->get_oauth_data();
    }//end get_oauth_data
    
    public function post_to_tblog($cnt,$img)
    {
        return $this->oauth->post_to_tblog($cnt,$img);
    }//end post_to_blog
    
    public function get_member()
    {
        $this->oauth->get_member();
        return;
    }//end get_member
    
    public function parse_event($data)
    {
        $this->oauth->parse_event($data);
    }//end parse_event
}
?>