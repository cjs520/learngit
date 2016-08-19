<?php
class wx_oauth
{
    private $app_data;
    private $callback_url;
    private $login_url;
    private $signature;
    private $oauth_type='wx';
    
    private $access_token;
    private $open_id;
    
    private $robot_info;
    
    public function __construct($data)
    {
        global $mm_mall_url,$mm_refer_url,$m_now_time;
        $this->callback_url=$mm_mall_url.'/oauth_back.php?oauth_type='.$this->oauth_type."&rnd=$m_now_time";
        $this->app_data=$data;
        
        $this->login_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->app_data['app_id'].
                         "&redirect_uri=".urlencode($this->callback_url)."&response_type=code&scope=snsapi_userinfo&state=xyz#wechat_redirect";
                         
        $this->robot_info="我是商城机器人，代号M:\n回复N  获取热门资讯\n回复M+信息 进行咨询/留言\n回复W 查看近期功能更新";
    }//end __construct
    
    public function __destruct(){}//end __destruct
    
    public function get_login_url()
    {
        return $this->login_url;
    }//end get_login_url
    
    public function callback()
    {
        if(!$_GET['code']) exit('wx coder error');
        if($_GET['state']!='xyz') exit('wx state error');
        global $db,$tablepre,$m_check_uid;
        
        
        $access_url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->app_data['app_id'].
                    "&secret=".$this->app_data['app_secret']."&code=".$_GET['code']."&grant_type=authorization_code";
        $data=$this->get_data($access_url);
        $arr_data=json_decode($data);
        
        if(intval($arr_data->errcode)==40029)    //万恶的电信，这里防截持
        {
            $this->get_member();
            //move_page('oauth_back.php?oauth_type='.$this->oauth_type.'&state=xyz&hack_type=1');
            exit;
        }
        $_SESSION['openid']=$arr_data->openid;
        
        $refresh_url="https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$this->app_data['app_id']."&grant_type=refresh_token&refresh_token=".$arr_data->refresh_token;
        $refresh_data=$this->get_data($refresh_url);
        $arr_refresh_data=json_decode($refresh_data);
        
        $_SESSION['access_token']=$arr_refresh_data->access_token;
        $this->open_id=$_SESSION['openid'];
        $_SESSION['oauth_type']=$this->oauth_type;
        
        $this->get_member();
    }//end callback
    
    private function destruct_state(){}//end destruct_state
    
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
            if($member_oauth['m_uid']>0) $member=$db->get_one("SELECT uid,member_id,member_class FROM `{$tablepre}member_table` WHERE uid='$member_oauth[m_uid]' LIMIT 1");
            if($member)    //是本站会员，但还未在本站登录
            {
                $_SESSION['user']['mvm_sess_uid'] = $member['uid'];
                $_SESSION['user']['mvm_sess_id'] = $member['member_id'];

                $grade=$db->get_one("SELECT is_admin,rank_list FROM `{$tablepre}grade_table` WHERE group_id='$member[member_class]' LIMIT 1");
                $_SESSION['user']['mvm_is_admin']=$grade['is_admin'];
                if($grade['is_admin']==1) $_SESSION['user']['mvm_rank_list']=$grade['rank_list'];
                
                //更新auth记录
                $row=array(
                    'm_uid'=>$member['uid'],
                    'oauth_uid'=>$open_id,
                    'token'=>$access_token,
                    'type'=>$this->oauth_type
                );
                $db->replace("`{$tablepre}member_oauth`",$row);
                
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
                
                move_page($_SESSION['refer_url']?$_SESSION['refer_url']:'index.php');
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
        $url="https://api.weixin.qq.com/sns/userinfo?access_token=".$this->get_access_token()."&openid=".$this->get_open_id()."&lang=zh_CN";
        $user_data=$this->get_data($url);
        $arr_user_data=json_decode($user_data);
        return array(
            'nickname'=>$arr_user_data->nickname,
            'province'=>$arr_user_data->province,
            'headimgurl'=>$arr_user_data->headimgurl,
            'city'=>$arr_user_data->city,
            'sex'=>$arr_user_data->sex
        );
    }//end get_user_blog_info
    
    public function get_oauth_data()
    {
        return array(
            'app_id'=>$this->app_data['app_id'],
            'app_secret'=>$this->app_data['app_secret'],
            'open_id'=>$this->get_open_id(),
            'access_token'=>$this->get_access_token(),
        );
    }//end get_oauth_data
    
    public function post_to_tblog($cnt,$img){}//end post_to_weibo
    
    public function parse_event($data)
    {
       
        $xml=simplexml_load_string($data);
        switch (strtolower($xml->Event))
        {
            case 'subscribe':
            case 'location':
                $this->replyDefaultMsg($xml);
                break;
            default:
                if($xml->MsgType=='text')    //用户回复文字消息
                {
                    $tag=strtoupper(substr($xml->Content,0,1));
                    if($tag=='N') $this->replyNewsMsg($xml,(int)$GLOBALS['supid']);
                    else if($tag=='W') $this->replyWhatsNew($xml);
                    else if($tag=='M')
                    {
                        if(strlen(trim($xml->Content))<=1) break;
                        $m=$GLOBALS['db']->get_one("SELECT m_uid FROM `{$GLOBALS['tablepre']}member_oauth` WHERE type='wx' AND oauth_uid='{$xml->FromUserName}' LIMIT 1");
                        $row=array(
                            'm_uid'=>$m['m_uid'],
                            'from_open_id'=>$xml->FromUserName,
                            'supplier_id'=>$GLOBALS['supid'],
                            'content'=>$xml->Content,
                            'register_date'=>$GLOBALS['m_now_time']
                        );
                        $GLOBALS['db']->insert("`{$GLOBALS['tablepre']}wx_msg`",$row);
                        
                        $this->robot_info='您的留言已成功提交，客服正快马加鞭进行回复';
                        $this->replyDefaultMsg($xml);
                    }
                    else $this->replyDefaultMsg($xml);
                }
                break;
        }
    }//end parse_event
    
    private function replyDefaultMsg($xml)
    {
        global $m_now_time,$mm_mall_title,$mm_mall_url,$mm_wx_focus_img;
        if(!$mm_wx_focus_img || !file_exists($mm_wx_focus_img)) $mm_wx_focus_img='images/noimages/nologo.jpg';
        $textTpl="<xml>
                  <ToUserName><![CDATA[%s]]></ToUserName>
                  <FromUserName><![CDATA[%s]]></FromUserName>
                  <CreateTime>%s</CreateTime>
                  <MsgType><![CDATA[%s]]></MsgType>
                  <ArticleCount>2</ArticleCount>
                  <Articles>
                  <item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>
                  <item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>
                  </Articles>
                  </xml>";
        $resultStr = sprintf($textTpl, $xml->FromUserName, $xml->ToUserName, $xml->CreateTime, 'news', 
                             $mm_mall_title,'点击进入注册',"$mm_mall_url/$mm_wx_focus_img",$this->login_url,
                             $this->robot_info,"","$mm_mall_url/images/default/mrobot.png",$mm_mall_url);     
        echo $resultStr;
        exit;
    }//end replyDefaultMsg
    
    private function replyWhatsNew($xml)
    {
        global $m_now_time,$mm_mall_title,$mm_mall_url,$mm_wx_focus_img;
        if(!$mm_wx_focus_img || !file_exists($mm_wx_focus_img)) $mm_wx_focus_img='images/noimages/nologo.jpg';
        $textTpl="<xml>
                  <ToUserName><![CDATA[%s]]></ToUserName>
                  <FromUserName><![CDATA[%s]]></FromUserName>
                  <CreateTime>%s</CreateTime>
                  <MsgType><![CDATA[%s]]></MsgType>
                  <ArticleCount>2</ArticleCount>
                  <Articles>
                  <item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>
                  <item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>
                  </Articles>
                  </xml>";
        $resultStr = sprintf($textTpl, $xml->FromUserName, $xml->ToUserName, $xml->CreateTime, 'news', 
                             $mm_mall_title,'点击进入注册',"$mm_mall_url/$mm_wx_focus_img",$this->login_url,
                             $this->robot_info."\n\n微信客服咨询功能已上线，近期准备开发购物分享功能:)","","$mm_mall_url/images/default/mrobot.png",$mm_mall_url);     
        echo $resultStr;
        exit;
    }//end replyWhatsNew
    
    private function replyNewsMsg($xml,$supid=0)
    {
        global $m_now_time,$mm_mall_title,$mm_mall_url,$mm_wx_focus_img;
        global $db,$tablepre;
        
        $arr_news=array();
        $q=$db->query("SELECT title,description,pic,url FROM `{$tablepre}wx_auto_reply` WHERE supplier_id='$supid' AND type='news' ORDER BY od LIMIT 5");
        while($rtl=$db->fetch_array($q))
        {
            if(!$rtl['pic'] || !file_exists($rtl['pic'])) $rtl['pic']='images/noimages/nologo.jpg';
            else $rtl['pic']=$mm_mall_url.'/'.$rtl['pic'];
            if(!$rtl['url']) $rtl['url']=$mm_mall_url;
            
            $arr_news[]=$rtl;
        }
        $db->free_result();
        
        $resultStr='';
        if(sizeof($arr_news)<=0)
        {
            if(!$mm_wx_focus_img || !file_exists($mm_wx_focus_img)) $mm_wx_focus_img='images/noimages/nologo.jpg';
            $textTpl="<xml>
                      <ToUserName><![CDATA[%s]]></ToUserName>
                      <FromUserName><![CDATA[%s]]></FromUserName>
                      <CreateTime>%s</CreateTime>
                      <MsgType><![CDATA[%s]]></MsgType>
                      <ArticleCount>2</ArticleCount>
                      <Articles>
                      <item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>
                      <item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>
                     </Articles>
                      </xml>";
            $resultStr = sprintf($textTpl, $xml->FromUserName, $xml->ToUserName, $xml->CreateTime, 'news', 
                                 $mm_mall_title,'点击进入注册',"$mm_mall_url/$mm_wx_focus_img",$this->login_url,
                                 "对不起，没有找到推荐新闻:(","","$mm_mall_url/images/default/mrobot.png",$mm_mall_url); 
        }
        else
        {
            if(!$mm_wx_focus_img || !file_exists($mm_wx_focus_img)) $mm_wx_focus_img='images/noimages/nologo.jpg';
            $article_count=sizeof($arr_news)+1;
            $str_xml_news='';
            foreach ($arr_news as $val)
            {
                $str_xml_news.="<item><Title><![CDATA[$val[title]]]></Title><Description><![CDATA[$val[description]]]></Description><PicUrl><![CDATA[$val[pic]]]></PicUrl><Url><![CDATA[$val[url]]]></Url></item>";
            }
            
            $textTpl="<xml>
                      <ToUserName><![CDATA[%s]]></ToUserName>
                      <FromUserName><![CDATA[%s]]></FromUserName>
                      <CreateTime>%s</CreateTime>
                      <MsgType><![CDATA[%s]]></MsgType>
                      <ArticleCount>$article_count</ArticleCount>
                      <Articles>
                      <mvm_news></mvm_news>
                      <item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>
                      </Articles>
                      </xml>";
            $resultStr = sprintf($textTpl, $xml->FromUserName, $xml->ToUserName, $xml->CreateTime, 'news', 
                                 $this->robot_info,"","$mm_mall_url/images/default/mrobot.png",$mm_mall_url);
            $resultStr=str_replace('<mvm_news></mvm_news>',$str_xml_news,$resultStr);
        }
        
        echo $resultStr;
        exit;
    }//end replyNewsMsg
    
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