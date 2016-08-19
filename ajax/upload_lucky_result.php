<?php
$tag=dhtmlchars($_POST['tag']);
$log=$db->get_one("SELECT * FROM `{$tablepre}lucky_log` WHERE tag='$tag' LIMIT 1");
if(!$log) exit('ERR:您提交了非法数据，请联系管理员');
if($log['m_uid']!=$m_check_uid) exit('ERR:您提交了非法数据，请联系管理员。');

$lucky=$db->get_one("SELECT * FROM `{$tablepre}lucky_table` WHERE uid='$log[lucky_uid]' LIMIT 1");
if(!$lucky) exit('ERR:检索不到指定的幸运转盘');
if($mvm_member['member_point']<$lucky['point']) exit('ERR:您的积分不足，无法进行抽奖');

if($log['lucky_g_uid']>0)
{
    $lg=$db->get_one("SELECT * FROM `{$tablepre}lucky_goods` WHERE uid='$log[lucky_g_uid]' LIMIT 1");
    if(!$lg) exit('ERR:检索不到指定的奖品');
}

//记录得奖
$row=array(
    'ordersn'=>'LK'.date('YmdHks').rand(10,99),
    'm_uid'=>$m_check_uid,
    'm_id'=>$m_check_id,
    'lucky_uid'=>$lucky['uid'],
    'lucky_g_uid'=>$log['lucky_g_uid'],
    'degree'=>$log['degree'],
    'reg_date'=>$m_now_time
);
$rec_uid=$db->insert("`{$tablepre}lucky_rec`",$row);

//扣积分
add_score($m_check_uid,-$lucky['point'],'幸运转盘','参加幸运转盘抽奖');
$m=cur_member_info($m_check_uid);
$point_left=$m['member_point'];

$db->query("DELETE FROM `{$tablepre}lucky_log` WHERE lucky_uid='$log[lucky_uid]' AND m_uid='$log[m_uid]'");

//再形成一个记录
$arr_goods_rate=array();
$total_rate=$lucky['fail_rate'];
$q=$db->query("SELECT uid,degree_min,degree_max,rate FROM `{$tablepre}lucky_goods` WHERE lucky_uid='$lucky[uid]' ORDER BY od");
while ($rtl=$db->fetch_array($q))
{
    $total_rate+=$rtl['rate'];
    $arr_goods_rate[]=$rtl;
}
if($total_rate<=0) exit('ERR:幸运转盘参数设置有误');

$lucky_goods=false;
$rate=rand(1,$total_rate);
$cur_rate=$lucky['fail_rate'];
if($rate<=$cur_rate)    //无奖
{
    $lucky_degree=rand($lucky['fail_degree_min'],$lucky['fail_degree_max']);
}
else
{
    foreach ($arr_goods_rate as $val)
    {
        $cur_rate+=$val['rate'];
        if($rate<=$cur_rate)
        {
            $lucky_degree=rand($val['degree_min'],$val['degree_max']);
            $lucky_goods=$val;
            break;
        }
    }
}

$new_log=array(
    'lucky_uid'=>$lucky['uid'],
    'm_uid'=>$m_check_uid,
    'degree'=>$lucky_degree,
    'lucky_g_uid'=>$lucky_goods?$lucky_goods['uid']:0,
    'tag'=>md5($lucky['uid'].$m_now_time.rand(0,1000).$lucky_degree)
);
$db->insert("`{$tablepre}lucky_log`",$new_log);


//回传的值
$rtn_row=array(
    'lucky_degree'=>$new_log['degree'],
    'tag'=>$new_log['tag'],
    'cur_point'=>$point_left,
    'lucky_g_uid'=>$log['lucky_g_uid'],
    'goods_name'=>$log['lucky_g_uid']>0?$lg['goods_name']:'',
    'lucky_name'=>$log['lucky_g_uid']>0?$lg['lucky_name']:'',
    'url'=>$log['lucky_g_uid']>0?$lg['url']:'',
    'goods_img'=>$log['lucky_g_uid']>0?$lg['goods_img']:'',
    'rec_uid'=>$rec_uid
);

exit(json_encode($rtn_row));