<?php
$arr_rtl=array('err'=>'');

if($cmd=='all_member')
{
    $c_uid=(int)$c_uid;
    $arr_rtl['all_member']=array();
    do
    {
        if($c_uid<=0) break;
        $q=$db->query("SELECT m_uid FROM `{$tablepre}community_member` WHERE c_uid='$c_uid' AND approval_date>10 
                       ORDER BY approval_date DESC");
        while ($rtl=$db->fetch_array($q))
        {
            $m=$db->get_one("SELECT member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
            if(!$m) continue;
            $m['member_image']=ProcImgPath($m['member_image'],'face');
            $rtl=array_merge($rtl,$m);
            $arr_rtl['all_member'][]=$rtl;
        }
        $db->free_result();
    }while (0);
}
else if($cmd=='exit')
{
    $c_uid=(int)$c_uid;
    do
    {
        if(!$m_check_id)
        {
            $arr_rtl['err']='您还没有登录，无法退出生活圈';
            break;
        }
        $comm=$db->get_one("SELECT uid,m_uid,c_name FROM `{$tablepre}community` WHERE uid='$c_uid' AND approval_date>10 LIMIT 1");
        if(!$comm)
        {
            $arr_rtl['err']='检索不到指定的生活圈';
            break;
        }
        if($comm['m_uid']==$m_check_uid)
        {
            $arr_rtl['err']='您是圈主，无法抛弃圈子独自离去';
            break;
        }
        $db->query("DELETE FROM `{$tablepre}community_member` WHERE c_uid='$comm[uid]' AND m_uid='$m_check_uid' LIMIT 1");
        $db->free_result();
        $arr_rtl['info']="成功退出$comm[c_name]生活圈";
    }while (0);
}
else if($cmd=='invite_friends')
{
    $c_uid=(int)$c_uid;
    $f_uid=dhtmlchars($f_uid);
    do
    {
        if(!$m_check_id)
        {
            $arr_rtl['err']='您还没有登录，无法邀请好友';
            break;
        }
        
        $comm=$db->get_one("SELECT uid,m_uid,c_name,join_check FROM `{$tablepre}community` WHERE uid='$c_uid' AND approval_date>10 LIMIT 1");
        if(!$comm)
        {
            $arr_rtl['err']='检索不到指定的生活圈';
            break;
        }
        
        if($comm['m_uid']!=$m_check_uid)
        {
            $m=$db->get_one("SELECT uid FROM `{$tablepre}community_member` WHERE c_uid='$comm[uid]' AND m_uid='$m_check_uid' AND approval_date>10 LIMIT 1");
            if(!$m)
            {
                $arr_rtl['err']="您不是生活圈$comm[c_name]的成员，无法邀请好友";
                break;
            }
        }
        
        $arr_f_uid=explode(',',$f_uid);
        $i=0;
        foreach ($arr_f_uid as $val)
        {
            $i++;
            if($i>20) break;
            
            $val=(int)$val;
            if($val<=0) continue;
            if($comm['m_uid']==$val) continue;
            $f=$db->get_one("SELECT uid FROM `{$tablepre}friend` WHERE belong_uid='$m_check_uid' AND member_uid='$val' LIMIT 1");
            if(!$f) continue;
            $m=$db->get_one("SELECT uid FROM `{$tablepre}community_member` WHERE c_uid='$comm[uid]' AND m_uid='$val' AND approval_date>10 LIMIT 1");
            if($m) continue;
            
            $row=array(
                'c_uid'=>$comm['uid'],
                'c_m_uid'=>$comm['m_uid'],
                'm_uid'=>$val,
                'join_reason'=>"{$m_check_id}邀请加入",
                'register_date'=>$m_now_time,
                'approval_date'=>$comm['join_check']==1?0:$m_now_time
            );
            $db->replace("`{$tablepre}community_member`",$row);
            $db->free_result();
        }
        $arr_rtl['info']=$comm['join_check']==1?'您的好友邀请已发送，请等待管理员审核':'恭喜，您的好友已应邀成功加入';
        
    }while (0);
}

exit(json_encode($arr_rtl));