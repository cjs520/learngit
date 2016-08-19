<?php
class wx_public
{
    private $app_id;
    private $app_secret;
    private $access_token;
    private $access_token_expire;
    
    public function __construct($app_id,$app_secret)
    {
        $this->app_id=$app_id;
        $this->app_secret=$app_secret;
    }//end constructor
    
    public function get_access_token($force=false)
    {
        if($_SESSION['wx_public_access_token'] && time()<(int)$_SESSION['wx_public_access_token_expire'] && !$force)
        {
            $this->access_token=$_SESSION['wx_public_access_token'];
            $this->access_token_expire=(int)$_SESSION['wx_public_access_token_expire'];
            return $this->access_token;
        }
        
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->app_id}&secret={$this->app_secret}";
        $rtl=$this->get_data($url,'GET');
        if(!$rtl) show_msg('腾迅服务器返回空值，请求失败');
        $json_rtl=json_decode($rtl);
        if($json_rtl->errmsg) show_msg("错误：{$json_rtl->errmsg}");
        
        $this->access_token=$_SESSION['wx_public_access_token']=$json_rtl->access_token;
        $this->access_token_expire=$_SESSION['wx_public_access_token_expire']=time()+(int)$json_rtl->expires_in;
        
        return $this->access_token;
    }//end function get_access_token
    
    public function query_menu()
    {
        $this->get_access_token();
        
        $url="https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$this->access_token}";
        $rtl=$this->get_data($url,'GET');
        return $rtl;
    }//end function query_menu
    
    public function add_menu($menu_data)
    {
        $this->get_access_token();
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$this->access_token}";
        $rtl=$this->get_data($url,'POST',$menu_data);
        return $rtl;
    }//end function menu
    
    public function del_menu()
    {
        $this->get_access_token();
        
        $url="https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$this->access_token}";
        $rtl=$this->get_data($url,'GET');
        return $rtl;
    }//end function del_menu
    
    public function reply_msg($to_open_id,$reply_cnt)
    {
        $this->get_access_token();
        
        $row=array(
            'touser'=>$to_open_id,
            'msgtype'=>'text',
            'text'=>array('content'=>'REPLY_CNT')
        );
        //echo json_encode($row),'----',$this->access_token;
        $url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->access_token;
        $rtl=$this->get_data($url,'POST',str_replace('REPLY_CNT',$reply_cnt,json_encode($row)));
        return json_decode($rtl);
    }//end function reply_msg
    
    public function fetch_qrcode()    //not tested yet
    {
        $this->get_access_token();
        
        $url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$this->access_token}";
        $post_data='{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 1}}}';
        $rtl=$this->get_data($url,'POST',$post_data);
        if($rtl->errcode) return "ERR:{$rtl->errmsg}";
        
        $url="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($rtl->ticket);
        $img=$this->get_data($url,'GET');
        return $img;
    }//end function fetch_qrcode
    
    private function get_data($url,$gp='GET',$post_fields=false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        
        if($gp=='POST')
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            if($post_fields)
            {
                if(is_array($post_fields)) $post_fields=http_build_query($post_fields);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            }
        }
        
        $resp = curl_exec($ch);
        curl_close($ch) ;
        return $resp;
    }//end get_data
}
?>