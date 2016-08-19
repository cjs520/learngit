<?php
if (!$id) exit($lang['intput_member']);
else if (mb_strlen($id,'UTF-8')<2  || mb_strlen($id,'UTF-8')>16) exit('请输入ID 2-16 位英文、数字或汉字组合');

$id = dhtmlchars(trim($id));
if(!preg_match('/^(\w|[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3})+$/',$id)) exit('会员ID格式错误');

$reply_info='';
$id_list = $db->get_one("SELECT member_id FROM `{$tablepre}member_table` WHERE member_id = '$id' LIMIT 1");
if ($id_list['member_id']) exit('此ID已被占用');    //用户名已存在
else $reply_info='<span class="green">可以注册</span>';

echo $reply_info;
exit;