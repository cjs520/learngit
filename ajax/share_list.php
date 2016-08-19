<?php
$arr_rtl=array('err'=>'');

$max_price=floatval($max_price);
$min_price=floatval($min_price);
$cat_uid=(int)$cat_uid;
$od=dhtmlchars($od);

$search_sql=" WHERE TRUE ";
if($max_price>0) $search_sql.=" AND buy_price<='$max_price' ";
if($min_price>0) $search_sql.=" AND buy_price>='$min_price' ";
if($cat_uid)
{
    include 'data/malldata/category.config.php';
    include 'data/malldata/category_pid.config.php';
    require_once 'include/cat_func.func.php';
    
    $children_uids=get_chidldren_uids($cat_uid,$uid_2_pid,$cat);
    array_push($children_uids,$cat_uid);
    $str_children_uids=implode(',',$children_uids);
    $search_sql.= " AND goods_category IN($str_children_uids)";
}

$list_num=20;
if($cmd=='total_page')
{
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}order_share` $search_sql");
    $arr_rtl['total_page']=ceil($rtl['cnt']/$list_num);
}
else if($cmd=='list')
{
    $page=(int)$page;
    if($page<1) $page=1;
    
    if($od=='hot') $order_sql=" ORDER BY love DESC ";
    else $order_sql=" ORDER BY uid DESC ";
    
    $arr_share=array();
    $from_rec=($page-1)*$list_num;
    $q=$db->query("SELECT uid,goods_name,og_uid,module,g_uid,goods_table,supplier_id,comment,buy_price,love,m_uid,attr,pics FROM `{$tablepre}order_share` 
                   $search_sql 
                   $order_sql 
                   LIMIT $from_rec,$list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
        if(!$rtl['comment']) $rtl['comment']='这家伙很懒，什么都没留下';
        $rtl['buy_price']=currency($rtl['buy_price']);
        if(!$rtl['attr']) $rtl['attr']='默认属性';
        $rtl['attr']=str_replace('|',',',$rtl['attr']);
        
        $m=$db->get_one("SELECT member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
        $rtl['member_image']=ProcImgPath($m['member_image'],'face');
        $rtl['member_id']=$m['member_id'];
        
        $rtl['pics']=unserialize($rtl['pics']);
        if(!is_array($rtl['pics']) || !$rtl['pics'])
        {
            $g=$db->get_one("SELECT goods_file1 FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
            $rtl['pics']=array(0=>ProcImgPath($g['goods_file1']));
        }
        $rtl['cover']=$rtl['pics'][0];
        
        $arr_share[]=$rtl;
    }
    $db->free_result();
    
    $arr_rtl['share']=$arr_share;
}
else if($cmd=='love')
{
    $uid=(int)$uid;
    if($m_now_time-(int)$_SESSION['love_time']<10) exit;
    $db->query("UPDATE `{$tablepre}order_share` SET love=love+1 WHERE uid='$uid'");
    $db->free_result();
    $_SESSION['love_time']=$m_now_time;
}

exit(json_encode($arr_rtl));