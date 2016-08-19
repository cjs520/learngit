<?php
$half_year_expire=$m_now_time-180*24*3600;
$month_expire=$m_now_time-30*24*3600;
$week_expire=$m_now_time-7*24*3600;

$arr_member=array();
$black_list=array();

//buyer to seller
$arr_goods_statistics=array();
$q=$db->query("SELECT level,to_id,goods_table,g_uid,reg_date FROM `{$tablepre}order_goods_comment`");
while ($rtl=$db->fetch_array($q))
{
    if(in_array($rtl['to_list'],$black_list)) continue;
    
    if(!isset($arr_member[$rtl['to_id']]))
    {
        $statistics=$db->get_one("SELECT comment_data FROM `{$tablepre}member_statistics` WHERE m_id='$rtl[to_id]' LIMIT 1");
        if(!$statistics)
        {
            $m=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$rtl[to_id]' LIMIT 1");
            if(!$m)
            {
                $black_list[]=$rtl['to_id'];
                continue;
            }
            $db->query("INSERT INTO `{$tablepre}member_statistics` (m_uid,m_id) VALUES ('$m[uid]','$rtl[to_id]')");
            $db->free_result(1);
            $arr_member[$rtl['to_id']]=InitCommentStruct();
        }
        else
        {
            $arr_member[$rtl['to_id']]=InitCommentStruct();
        }
    }
    
    $idx=$rtl['goods_table'].'|'.$rtl['g_uid'];
    if(!isset($arr_goods_statistics[$idx]))
    {
        $g_statistics=$db->get_one("SELECT * FROM `{$tablepre}goods_statistics` WHERE g_uid='$rtl[g_uid]' AND goods_table='$rtl[goods_table]' LIMIT 1");
        if(!$g_statistics)
        {
            $db->query("INSERT INTO `{$tablepre}goods_statistics` (g_uid,goods_table) 
                        VALUES ('$rtl[g_uid]','$rtl[goods_table]')");
            $db->free_result(1);
        }
        $arr_goods_statistics[$idx]=array('good'=>0,'normal'=>0,'bad'=>0);
    }
    switch ($rtl['level'])
    {
        case -1:
            if($rtl['reg_date']>$half_year_expire) $arr_member[$rtl['to_id']]['seller']['bad']['half_year']++;
            if($rtl['reg_date']>$month_expire) $arr_member[$rtl['to_id']]['seller']['bad']['month']++;
            if($rtl['reg_date']>$week_expire) $arr_member[$rtl['to_id']]['seller']['bad']['week']++;
            $arr_member[$rtl['to_id']]['seller']['bad']['total']++;
            $arr_goods_statistics[$idx]['bad']++;
            break;
        case 0:
            if($rtl['reg_date']>$half_year_expire) $arr_member[$rtl['to_id']]['seller']['normal']['half_year']++;
            if($rtl['reg_date']>$month_expire) $arr_member[$rtl['to_id']]['seller']['normal']['month']++;
            if($rtl['reg_date']>$week_expire) $arr_member[$rtl['to_id']]['seller']['normal']['week']++;
            $arr_member[$rtl['to_id']]['seller']['normal']['total']++;
            $arr_goods_statistics[$idx]['normal']++;
            break;
        default:
            if($rtl['reg_date']>$half_year_expire) $arr_member[$rtl['to_id']]['seller']['good']['half_year']++;
            if($rtl['reg_date']>$month_expire) $arr_member[$rtl['to_id']]['seller']['good']['month']++;
            if($rtl['reg_date']>$week_expire) $arr_member[$rtl['to_id']]['seller']['good']['week']++;
            $arr_member[$rtl['to_id']]['seller']['good']['total']++;
            $arr_goods_statistics[$idx]['good']++;
            break;
    }
    
    unset($rtl);
}
$db->free_result();

foreach ($arr_goods_statistics as $key=>$val)
{
    $arr_tmp=explode('|',$key);
    $db->update("`{$tablepre}goods_statistics`",$val," g_uid='$arr_tmp[1]' AND goods_table='$arr_tmp[0]'");
    unset($arr_tmp);
}
$db->free_result();
unset($arr_goods_statistics);

//seller to buyer
$q=$db->query("SELECT from_id,to_id,level,reg_date FROM `{$tablepre}order_user_comment` WHERE roll='1'");
while ($rtl=$db->fetch_array($q))
{
    if(in_array($rtl['to_list'],$black_list)) continue;
    if(!isset($arr_member[$rtl['to_id']]))
    {
        $statistics=$db->get_one("SELECT comment_data FROM `{$tablepre}member_statistics` WHERE m_id='$rtl[to_id]' LIMIT 1");
        if(!$statistics)
        {
            $m=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$rtl[to_id]' LIMIT 1");
            if(!$m)
            {
                $black_list[]=$rtl['to_id'];
                continue;
            }
            $db->query("INSERT INTO `{$tablepre}member_statistics` (m_uid,m_id) VALUES ('$m[uid]','$rtl[to_id]')");
            $db->free_result(1);
            $arr_member[$rtl['to_id']]=InitCommentStruct();
        }
        else
        {
            $arr_member[$rtl['to_id']]=InitCommentStruct();
        }
    }
    
    switch ($rtl['level'])
    {
        case -1:
            if($rtl['reg_date']>$half_year_expire) $arr_member[$rtl['to_id']]['buyer']['bad']['half_year']++;
            if($rtl['reg_date']>$month_expire) $arr_member[$rtl['to_id']]['buyer']['bad']['month']++;
            if($rtl['reg_date']>$week_expire) $arr_member[$rtl['to_id']]['buyer']['bad']['week']++;
            $arr_member[$rtl['to_id']]['buyer']['bad']['total']++;
            break;
        case 0:
            if($rtl['reg_date']>$half_year_expire) $arr_member[$rtl['to_id']]['buyer']['normal']['half_year']++;
            if($rtl['reg_date']>$month_expire) $arr_member[$rtl['to_id']]['buyer']['normal']['month']++;
            if($rtl['reg_date']>$week_expire) $arr_member[$rtl['to_id']]['buyer']['normal']['week']++;
            $arr_member[$rtl['to_id']]['buyer']['normal']['total']++;
            break;
        default:
            if($rtl['reg_date']>$half_year_expire) $arr_member[$rtl['to_id']]['buyer']['good']['half_year']++;
            if($rtl['reg_date']>$month_expire) $arr_member[$rtl['to_id']]['buyer']['good']['month']++;
            if($rtl['reg_date']>$week_expire) $arr_member[$rtl['to_id']]['buyer']['good']['week']++;
            $arr_member[$rtl['to_id']]['buyer']['good']['total']++;
            break;
    }
    
    unset($rtl);
}
$db->free_result();

//write to database
foreach ($arr_member as $key=>$val)
{
    $val=serialize($val);
    $db->query("UPDATE `{$tablepre}member_statistics` SET comment_data='$val' WHERE m_id='$key'");
    $db->free_result(1);
}
$db->free_result();
unset($arr_member);
unset($black_list);

//write down log
$basename=str_replace('.php','',basename(__FILE__));
!$action && $action='all';
task_log($basename,$action,$today_timestamp);

function InitCommentStruct()
{
    return array(
        'seller'=>array(
            'good'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0),
            'normal'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0),
            'bad'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0)
        ),
        'buyer'=>array(
            'good'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0),
            'normal'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0),
            'bad'=>array('total'=>0,'week'=>0,'month'=>0,'half_year'=>0)
        ),
    );
}
?>