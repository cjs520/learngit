<?php
if(!$m_check_uid) exit('ERR:您还未登录，请先登录');
$uid=(int)$uid;
$t=(int)$t;
$module=dhtmlchars($module);
$gt=dhtmlchars($gt);
if($t!=1 && $t!=0) exit('ERR:收藏类型错误');

$db->query("REPLACE INTO `{$tablepre}favorite` (m_uid,f_uid,t,module,goods_table) 
            VALUES ('$m_check_uid','$uid','$t','$module','$gt')");
$db->free_result();

echo "OK:收藏成功";
exit;