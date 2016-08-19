<?php
if(!function_exists('curl_init')) exit('ERR:请先加载curl模块');
if(!$m_check_id) exit('ERR:请先登录');

$supplier_id=(int)$supplier_id;
$rtl=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE cf_name='mm_wx_logo' AND supplier_id='$supplier_id' LIMIT 1");
if(!$rtl['cf_value']) exit('ERR:请先上传您的二维码');
if($supplier_id>0) $rtl['cf_value']='union/'.$rtl['cf_value'];
if(!file_exists($rtl['cf_value'])) exit('ERR:您的二维码图片不存在，请重新上传');

$img_url="$mm_mall_url/$rtl[cf_value]";
$url="http://zxing.org/w/decode?u=$img_url";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$resp = curl_exec($ch);
curl_close($ch) ;

if(!strstr($resp,'http://weixin.qq.com/r/')) exit('ERR:请上传正确的微信二维码');
$arr_rtl=array();
preg_match('/(?<=qq\.com\/r\/).*?(?=<\/pre>)/',$resp,$arr_rtl);
if(sizeof($arr_rtl)<1) exit('ERR:解析错误,请联系管理员');

exit($arr_rtl[0]);