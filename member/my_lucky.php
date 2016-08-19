<?php
if($_POST && (int)$step==1)
{
    $uid=(int)$uid;
    $name=dhtmlchars($name);
    $address=dhtmlchars($address);
    $tel=dhtmlchars($tel);
    $memo=dhtmlchars($memo);
    if(!$name) exit('ERR:请填写收货人姓名');
    if(!$address) exit('ERR:请填写收货地址');
    if(!$tel) exit('ERR:请填写收货人联系方式');

    $db->query("UPDATE `{$tablepre}lucky_rec` SET
                name='$name',
                tel='$tel',
                address='$address',
                memo='$memo' 
                WHERE uid='$uid' AND m_uid='$m_check_uid'");
    exit('OK:编辑成功');
}

$arr_lucky_rec=array();
$search_sql = "WHERE m_uid='$m_check_uid'";
$total_count = $db->counter("`{$tablepre}lucky_rec`",$search_sql);
require_once 'include/pager.class.php';
$page = $page ? (int)$page : 1;
$list_num = 12;
$rowset  = new Pager($total_count,$list_num,$page);
$from_record = $rowset->_offset();
$group_list = array();
$q=$db->query("SELECT uid,lucky_uid,name,address,tel,memo,ordersn,lucky_g_uid,approval_date FROM `{$tablepre}lucky_rec`
	           $search_sql 
	           ORDER BY uid DESC 
	           LIMIT $from_record,$list_num");
while($rtl=$db->fetch_array($q))
{
    $lucky=$db->get_one("SELECT name FROM `{$tablepre}lucky_table` WHERE uid='$rtl[lucky_uid]' LIMIT 1");
    $rtl['lucky_name']=$lucky['name'];
    $rtl['goods_name']='没有中奖';
    $rtl['goods_url']='#';
    $rtl['target']='';
    if($rtl['lucky_g_uid']>0)
    {
        $lucky_goods=$db->get_one("SELECT goods_name,url FROM `{$tablepre}lucky_goods` WHERE uid='$rtl[lucky_g_uid]' LIMIT 1");
        $rtl['goods_name']=$lucky_goods['goods_name'];
        if($lucky_goods['url'])
        {
            $rtl['goods_url']=$lucky_goods['url'];
            $rtl['target']="target='_blank'";
        }
    }
    $rtl['status']=$rtl['approval_date']>10?'已发货':'未发货';

    $arr_lucky_rec[]=$rtl;
}
$db->free_result();

$page_list = $rowset->link("member.php?action=$action&page=");

require 'header.php';
include_once template('member_mylucky');