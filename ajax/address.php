<?php
$arr_address=array();
$q = $db->query("SELECT uid,is_buy,consignee,address,province,city,county,mobile,zipcode 
                 FROM `{$tablepre}address` WHERE m_uid='$m_check_uid' 
                 LIMIT 3");
while ($rtl=$db->fetch_array($q))
{
    $rtl['is_buy']=$rtl['is_buy']==1?'checked':'';
    $arr_address[]=$rtl;
}

echo json_encode($arr_address);
exit;