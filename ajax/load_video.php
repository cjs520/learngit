<?php
$arr_good_comment=array(
    '震撼，好莱坞大片即视感！',
    '镜头感真心好，广告当文艺片看，免费的哟！',
    'OMG，这是广告片？我还以为是电影呢！',
    '哇塞，配乐真好！',
    '导演是学美术滴么？色彩调节好漂亮！'
);

$arr_bad_comment=array(
    '广告片拍成这样，我也是醉了',
    '到底在拍啥？有点为我的智商捉急了。。。',
    '神啊，收了这货吧',
    '闪瞎我的钛合金狗眼',
    '这样也能行？毁三观！'
);

if($cmd=='write_comment')
{
    $uid=(int)$uid;
    $rel=dhtmlchars($rel);
    if($rel!='good_video' && $rel!='bad_video') exit('ERR:参数错误，请联系管理员');
    if(!$m_check_uid) exit('ERR:您还未登录，请先登录');
    if($m_now_time-(int)$_SESSION['video_comment']<10) exit('ERR:您评价得太快，先喝杯水缓缓');
    $video=$db->get_one("SELECT uid FROM `{$tablepre}video_ad` WHERE uid='$uid' AND approval=1 LIMIT 1");
    if(!$video) exit('ERR:检索不到指定的视频广告');
    $_SESSION['video_comment']=$m_now_time;
    
    
    $idx=(rand(0,(sizeof($arr_good_comment)-1)*10))%5;
    $comment=$rel=='good_video'?$arr_good_comment[$idx]:$arr_bad_comment[$idx];
    $comment_row=array(
        'video_uid'=>$uid,
        'm_uid'=>$m_check_uid,
        'comment'=>$comment,
        'good_bad'=>$rel=='good_video'?1:2,
        'register_date'=>$m_now_time
    );
    $db->insert("`{$tablepre}video_comment`",$comment_row);
    
    if($rel=='good_video') $db->query("UPDATE `{$tablepre}video_ad` SET good=good+1 WHERE uid='$uid'");
    else $db->query("UPDATE `{$tablepre}video_ad` SET bad=bad+1 WHERE uid='$uid'");
    $db->free_result();
    
    exit('OK:您的评价就是我们的财富:)');
}
else
{
    $cat_uid=(int)$cat_uid;
    $time_area=(int)$time_area;

    $search_sql="WHERE approval=1 AND time_area='$time_area'";
    if($cat_uid>0) $search_sql.=" AND cat_uid='$cat_uid' ";

    $arr_video=array();
    $q=$db->query("SELECT uid,title,pic FROM `{$tablepre}video_ad`
                   $search_sql 
                   ORDER BY rate DESC 
                   LIMIT 12");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['url']=GetBaseUrl('tv_detail',$rtl['uid'],'action');
        unset($rtl['uid']);
        $arr_video[]=$rtl;
    }

    exit(json_encode($arr_video));
}


