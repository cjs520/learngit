<?php
$g_uid=(int)$g_uid;
$goods_table=dhtmlchars($gt);
$html='';

if($cmd=='read')
{
    $q=$db->query("SELECT uid,from_id,comment,level,usable,unusable,reg_date FROM `{$tablepre}order_goods_comment`
                   FORCE INDEX(`g_uid`) 
                   WHERE goods_table='$goods_table' AND g_uid='$g_uid' 
                   ORDER BY reg_date DESC 
                   LIMIT 10");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['reg_date']=date('Y-m-d',$rtl['reg_date']);
        $m=$db->get_one("SELECT uid,member_image FROM `{$tablepre}member_table` WHERE member_id='$rtl[from_id]' LIMIT 1");
        if(!$m['member_image']) $m['member_image']='images/noimages/dface.jpg';
        $m['url']="$main_settings[mm_mall_url]/user_rate.php?supid=$m[uid]";
        
        $cls='';
        if($rtl['level']==-1) $cls='rate_bad';
        else if($rtl['level']==0) $cls='rate_soso';
        else $cls='rate_good';
        
        $html.='<tr>';
        $html.='<td class="fct"><a href="'.$m['url'].'" target="_blank"><img src="'.$m['member_image'].'" /></a><a href="#">'.$rtl['from_id'].'</a></td>';
        $html.='<td>';
        $html.="<p>$rtl[comment]</p>";
        $html.='<div class="red">';
        $html.='<span class="fr gray">('.$rtl['reg_date'].')</span>';
        $html.='<span class="fl"><span class="'.$cls.'"></span>&nbsp;&nbsp;<a href="#" rel="usable" uid="'.$rtl['uid'].'">赞(<b>'.$rtl['usable'].'</b>)</a>&nbsp;&nbsp;<a href="#" rel="unusable" uid="'.$rtl['uid'].'">踩(<b>'.$rtl['unusable'].'</b>)</a></span>';
        $html.='</div>';
        $html.='</td>';
        $html.='</tr>';
    }
    $db->free_result();
    echo $html;
    exit;
}
else if($cmd=='write')
{
    if($m_now_time-(int)$_SESSION['use_time']<20) exit;
    $_SESSION['use_time']=$m_now_time;
    $uid=(int)$uid;
    $field=dhtmlchars($tag)=='usable'?'usable':'unusable';
    $db->query("UPDATE `{$tablepre}order_goods_comment` SET `$field`=`$field`+1 WHERE uid='$uid'");
    exit;
}
