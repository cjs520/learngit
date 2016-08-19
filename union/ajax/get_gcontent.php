<?php

$uid=(int)$uid;
if($uid<=0) exit('');
$detail_table=dhtmlchars($detail_table);

!$detail_table && $detail_table=$shop_file['sellshow']==1?"{$tablepre}goods_detail":"{$tablepre}goods_show_detail";
$detail=$db->get_one("SELECT goods_main FROM `$detail_table` WHERE g_uid='$uid' LIMIT 1");


echo stripslashes($detail['goods_main']);
//filter_editor_img(stripslashes($detail['goods_main']));
