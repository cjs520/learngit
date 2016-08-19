<?php
$member_homepage=dhtmlchars($member_homepage);
if(!preg_match('/^[0-9a-zA-Z]{5,15}$/',$member_homepage))
exit('请填写正确的二级域名，由5~15个英文和数字组成');
$domain_file='data/malldata/sudomain.dat';
if(file_exists($domain_file))
{
    $preserve_domain=file_get_contents($domain_file);
    $rtl=array_search($member_homepage,explode('|',$preserve_domain));
    if($rtl) exit('您的二级域名被占用，请更换一个');
}

$rtl=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_homepage='$member_homepage' LIMIT 1");
if($rtl) exit('您的二级域名已被占用，请更换一个');
exit('OK');