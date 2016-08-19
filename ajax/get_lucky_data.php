<?php
if(!$m_check_id) exit('ERR:请先登录');
$lucky=$db->get_one("SELECT * FROM `{$tablepre}lucky_table` ORDER BY uid DESC LIMIT 1");
if(!$lucky['img'] || !file_exists($lucky['img'])) exit('ERR:活动资料未完整');
if($m_now_time<=$lucky['start_time']) exit('ERR:活动还未开始');
if($m_now_time>=$lucky['end_time']) exit('ERR:活动已过期');

$log=$db->get_one("SELECT degree,tag FROM `{$tablepre}lucky_log` WHERE lucky_uid='$lucky[uid]' AND m_uid='$m_check_uid' LIMIT 1");

if(!$log)
{
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

    $log=array(
        'lucky_uid'=>$lucky['uid'],
        'm_uid'=>$m_check_uid,
        'degree'=>$lucky_degree,
        'lucky_g_uid'=>$lucky_goods?$lucky_goods['uid']:0,
        'tag'=>md5($lucky['uid'].$m_now_time.rand(0,1000).$lucky_degree)
    );
    $db->insert("`{$tablepre}lucky_log`",$log);
}

$arr_info=array(
    'uid'=>$lucky['uid'],
    'point'=>$lucky['point'],
    'img'=>$lucky['img'],
    'id'=>$m_check_id,
    'lucky_degree'=>$log['degree'],
    'tag'=>$log['tag'],
    'cur_point'=>$mvm_member['member_point']
);

exit(json_encode($arr_info));