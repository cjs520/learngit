<?php
//share star
$arr_star=array();
$arr_category=array();
$expire=$today_timestamp-7*24*3600;
$q=$db->query("SELECT m_uid,goods_category FROM `{$tablepre}order_share` WHERE register_date>='$expire'");
while ($rtl=$db->fetch_array($q))
{
    if(!isset($arr_star[$rtl['m_uid']])) $arr_star[$rtl['m_uid']]=0;
    $arr_star[$rtl['m_uid']]++;
    $arr_category[$rtl['goods_category']]=true;
    
    unset($rtl);
}
$db->free_result();

foreach ($arr_star as $key=>$val)
{
    $rtl_tmp=$db->get_one("SELECT m_uid FROM `{$tablepre}member_statistics` WHERE m_uid='$key' LIMIT 1");
    if($rtl_tmp)
    {
        $db->query("UPDATE `{$tablepre}member_statistics` SET week_share_num='$val' WHERE m_uid='$key'");
        $db->free_result(1);
    }
    else
    {
        $m=$db->get_one("SELECT uid,member_id FROM `{$tablepre}member_table` WHERE uid='$key' LIMIT 1");
        $db->query("INSERT INTO `{$tablepre}member_statistics` (m_uid,m_id,week_share_num) 
                    VALUES ('$key','$m[member_id]','$val')");
        $db->free_result(1);
        unset($m);
    }
    
    unset($rtl_tmp);
}
unset($arr_star);

//share category
include 'data/malldata/category.config.php';
include 'data/malldata/category_pid.config.php';
require_once 'include/cat_func.func.php';

foreach ($arr_category as $key=>$val)
{
    if(!$cat) break;
    if(!$uid_2_pid) break;
    $arr_parents=get_parents($key,$uid_2_pid);
    
    $n=false;
    foreach ($arr_parents as $k=>$v)
    {
        if($k>2) break;
        if($k==0)
        {
            $n=$cat[$v];
            continue;
        }
        
        $n=$n['child'][$v];
        $rtl=$db->get_one("SELECT cat_uid FROM `{$tablepre}order_share_cat` WHERE cat_uid='$v' LIMIT 1");
        if($rtl)
        {
            $db->query("UPDATE `{$tablepre}order_share_cat` SET od=od+1 WHERE cat_uid='$v'");
            $db->free_result(1);
        }
        else
        {
            $row=array(
                'cat_uid'=>$v,
                'category_id'=>$arr_parents[$k-1],
                'category_name'=>$n['data']['category_name'],
                'od'=>1
            );
            $db->replace("`{$tablepre}order_share_cat`",$row);

            unset($row);
        }
        unset($rtl);
    }
    
    unset($arr_parents);
}

unset($cat);
unset($uid_2_pid);
unset($arr_category);

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);


?>