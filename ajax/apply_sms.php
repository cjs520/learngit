<?php
if($m_check_uid!=1) exit('这不是您来的地方，速速后退');
$sms_id=dhtmlchars($sms_id);
$pass=dhtmlchars($pass);

if(!$sms_id || !$pass) exit('请设置完整的短信管理账号和密码');
if(!preg_match('/^[0-9a-zA-Z]+$/',$sms_id)) exit('短信账号只能由数字和字母组成');
if(!preg_match('/^[0-9a-zA-Z]+$/',$pass)) exit('短信密码只能由数字和字母组成');

$sms_id=urldecode($sms_id);
$pass=urlencode($pass);
$host='120.132.132.102';
$path="/ws/AgentMakeAccount.aspx?LoginName=dls&LoginPwd=123456&CorpName=mvmmall&LinkMan=mvmmall&Tel=2921706&Mobile=2921706&Email=860051097@qq.com&Memo=nothing&CorpID=$sms_id&Pass=$pass";
$fp = fsockopen($host, 80, $errno, $errstr, 30);

if ($fp)
{
    @fputs($fp, "GET $path HTTP/1.1\r\n");
    @fputs($fp, "Host: $host\r\n");
    @fputs($fp, "Accept: */*\r\n");
    @fputs($fp, "Referer: http://$host/\r\n");
    @fputs($fp, "User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)\r\n");
    @fputs($fp, "Connection: Close\r\n\r\n");
}

$str_rec= '';
while ($str = fread($fp, 4096)) $str_rec .= $str;
@fclose($fp);

$pos=strpos($str_rec,"\r\n\r\n");
$str_rec=substr($str_rec,$pos+4);

$arr_info=array(
'accounthasuse'=>'该账户已存在',
'ok'=>'申请成功'
);

if(trim(strtolower($str_rec))=='ok')
{
    $db->query("DELETE FROM `{$tablepre}config` WHERE cf_name IN ('mm_sms_system_id','mm_sms_system_pass')");
    $db->query("INSERT INTO `{$tablepre}config` SET
                cf_name='mm_sms_system_id',
                cf_value='$sms_id',
                supplier_id='0'");
    $db->query("INSERT INTO `{$tablepre}config` SET
                cf_name='mm_sms_system_pass',
                cf_value='$pass',
                supplier_id='0'");
    $cache->put_cache('cfg');
}

$info=$arr_info[trim(strtolower($str_rec))];
if(!$info) $info="未知错误，错误代码：$str_rec 请联系管理员 $pos";
exit($info);