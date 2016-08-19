<?php
if ($act=='edit')
{
    $uid=(int)$uid;
    $add_info=$db->get_one("SELECT * FROM `{$tablepre}address` WHERE m_uid='$m_check_uid' AND uid='$uid' LIMIT 1");
    
    if(!$add_info) exit('检索不到指定的收货地址');
    if ($_POST && (int)$step==1)
    {
        $consignee=dhtmlchars($consignee);
        if(!$consignee) show_msg('请填写联系人姓名');
        $is_buy=(int)$is_buy==1?1:0;
        if($is_buy==1) $db->query("UPDATE `{$tablepre}address` SET is_buy=0 WHERE m_uid='$m_check_uid'");
        
        $row = array(
            'is_buy' => $is_buy,
            'consignee' => $consignee,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'county' => $county,
            'address' => $address,
            'zipcode' => $zipcode,
            'm_uid'=>$m_check_uid
        );
        $db->update("{$tablepre}address",$row," uid='$uid' ");
        
        move_page("account.php?action=$action");
    }
    
    $add_info['is_buy'] = $add_info['is_buy']==1 ?'checked':'';
    require_once MVMMALL_ROOT.'header.php';
    require_once template('member_address_edit');
    exit;
}
else if($act=='del')
{
    $uid=(int)$uid;
    $db->query("DELETE FROM `{$tablepre}address` WHERE uid='$uid' AND m_uid='$m_check_uid'");
    $db->free_result();
    exit;
}
else if($act=='add')
{
    $address_nums = $db->counter("{$tablepre}address","m_uid='$m_check_uid'");    //判断收货地址个数
    if($address_nums>=3) exit('收货地址数量已超过3个，不能添加');
        
    if ($_POST && (int)$step==1)
    {
        $consignee=dhtmlchars($consignee);
        if(!$consignee) show_msg('请填写联系人姓名');
        if(!$mobile) show_msg('请填写联系手机号码');
        if(!$address) show_msg('请填写收货地址');
        
        $is_buy=(int)$is_buy==1?1:0;
        if($is_buy==1) $db->query("UPDATE `{$tablepre}address` SET is_buy=0 WHERE m_uid='$m_check_uid'");
        
        $row = array(
            'is_buy' => $is_buy,
            'consignee' => $consignee,
            'mobile' => $mobile,
            'province' => $province,
            'city' => $city,
            'county' => $county,
            'address' => $address,
            'zipcode' => $zipcode,
            'm_uid'=>$m_check_uid
        );
        $db->insert("{$tablepre}address",$row);
        
        move_page("account.php?action=$action");
    }
    require_once MVMMALL_ROOT.'header.php';
    require_once template('member_address_edit');
    exit;
}
else
{
    $member_address=array();
    $q = $db->query("SELECT * FROM `{$tablepre}address` WHERE m_uid='$m_check_uid' LIMIT 3");
    while ($rtl = $db->fetch_array($q)) $member_address[] = $rtl;

    require_once MVMMALL_ROOT.'header.php';
    require_once template('member_address');
}