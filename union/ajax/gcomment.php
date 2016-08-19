<?php
$g_uid=(int)$g_uid;
$step=dhtmlchars($step);
if($step=='write')
{
    if(!$m_check_uid) exit('ERR:您还未登录，请登录后再提交内容');
    $comment_body=trim(mb_substr(strip_tags($comment_body),0,150,'UTF-8'));
    $code=substr($code,0,4);
    $mod=dhtmlchars($mod);
    $type=(int)$type;
    if(!$mod) exit('ERR:模块指定错误，请联系管理员');
    if($main_settings['mm_code_use']==1)
	{
		require_once 'include/captcha.class.php';
		$Captcha = new Captcha();
		if(!$Captcha->CheckCode($code)) exit('ERR:验证码错误');
	}
	if($type<0 || $type>9) exit('ERR:商品类型指定错误');
    if(!$comment_body) exit('ERR:请填写咨询内容');

    $row=array(
        'guid'=>$g_uid,
        'module'=>$mod,
        'type'=>$type,
        'm_id'=>$m_check_id,
        'comment_body'=>$comment_body,
        'register_date'=>$m_now_time,
        'supplier_id'=>$page_member_id
    );
    $db->insert("`{$tablepre}gcomment_table`",$row);
    
    $now_time=date('Y-m-d H:i');
    echo '<li class="h_oflow li1" style="display:none;height:72px;">',
         "<p><a class='gray'>$m_check_id</a><span class='fl gray'>$now_time</span><span class='fr red'>未审核，请等待店长审核</span></p>",
         "<p>咨询内容: {$comment_body}</p>",
         '<p class="red"><span class="fl">回复： 暂无回复</span></p>',
         '</li>';
}
else if($step=='read')
{
    $mod=dhtmlchars($mod);
    $html='';
    $q=$db->query("SELECT m_id,comment_body,reply,reply_time,register_date FROM `{$tablepre}gcomment_table` 
                   WHERE module='$mod' AND guid='$g_uid' AND supplier_id='$page_member_id' AND approval_date>0 
                   ORDER BY approval_date DESC 
                   LIMIT 6");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['register_date']=date('Y-m-d H:i',$rtl['register_date']);
        $rtl['reply']=$rtl['reply_time']<10?'暂无回复':$rtl['reply'];
        $rtl['reply_time']=$rtl['reply_time']<10?'':date('Y-m-d H:i',$rtl['reply_time']);
        
        $html.='<li class="h_oflow" style="height:72px;">'.
               "<p><a class='gray'>$rtl[m_id]</a><span class='fl gray'>$rtl[register_date]</span></p>".
               "<p>咨询内容: $rtl[comment_body]</p>".
               "<p><span class='fl red'>回复： $rtl[reply]</span><span class='fr gray'>$rtl[reply_time]</span></p>".
               '</li>';
    }
    $db->free_result();
    echo $html;
}
else exit('您进错门了');