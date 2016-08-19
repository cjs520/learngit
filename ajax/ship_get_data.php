<?php
$shop2code=array(
    'stkd'=>'shentong',
    'ems'=>'ems',
    'sfkd'=>'shunfeng',
    'ytkd'=>'yuantong',
    'ydkd'=>'yunda',
    'ztkd'=>'zhongtong'
);
$sh_id=(int)$sh_id;
$rtl=$db->get_one("SELECT class_name FROM `{$tablepre}ship_table` WHERE uid='$sh_id' LIMIT 1");

if(!$rtl || !isset($shop2code[$rtl['class_name']])) exit('ERR:找不到指定的快递公司');
$typeCom = $shop2code[strtolower($rtl['class_name'])];//快递公司
$typeNu = dhtmlchars($dcode);  //快递单号

//echo $typeCom.'<br/>' ;exit;
//echo $typeNu ;
//$url ='http://api.kuaidi100.com/api?id='.$mm_order_key.'&com='.$typeCom.'&nu='.$typeNu.'&show=2&muti=1&order=asc';
$url="http://www.kuaidi100.com/query?type=$typeCom&postid=$typeNu";

//请勿删除变量$powered 的信息，否者本站将不再为你提供快递接口服务。
//$powered = '查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 ';


//优先使用curl模式发送数据
if (function_exists('curl_init') == 1){
  $curl = curl_init();
  curl_setopt ($curl, CURLOPT_URL, $url);
  curl_setopt ($curl, CURLOPT_HEADER,0);
  curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
  curl_setopt ($curl, CURLOPT_TIMEOUT,5);
  $get_content = curl_exec($curl);
  curl_close ($curl);
}else{
  include("include/snoopy.php");
  $snoopy = new snoopy();
  $snoopy->referer = 'http://www.google.com/';//伪装来源
  $snoopy->fetch($url);
  $get_content = $snoopy->results;
}
print_r($get_content);
exit();
?>
