<?php
$uid=(int)$uid;
if($uid>0 && $m_check_id)
{
    $db->query("DELETE FROM `{$tablepre}friend` WHERE uid='$uid' AND belong_uid='$m_check_uid'");
    $db->free_result();
}