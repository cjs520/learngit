<?php
require_once 'include/pager.class.php';

$arr_rtl=array('err'=>'');
$t_uid=(int)$t_uid;

do
{
    if($t_uid<=0)
    {
        $arr_rtl['err']='指定的话题错误';
        break;
    }
    
    $search_sql="WHERE t_uid='$t_uid' AND approval_date>10";
    $arr_comment=array();
    $total_count = $db->counter("{$tablepre}community_comment",$search_sql);
    $page = $page ? (int)$page : 1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $i=1;
    $q = $db->query("SELECT uid,m_uid,register_date,title,content FROM `{$tablepre}community_comment` 
	                 $search_sql 
	                 ORDER BY approval_date DESC 
	                 LIMIT $from_record,$list_num");
    while($rtl = $db->fetch_array($q))
    {
        $rtl['floor']=$list_num*($page-1)+$i;
        $i++;
        $m=$db->get_one("SELECT member_id,member_image FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
        $m['member_image']=ProcImgPath($m['member_image'],'face');
        $rtl['member_id']=$m['member_id'];
        $rtl['member_image']=$m['member_image'];
        
        $rtl['register_date']=date('Y-m-d H:i:s',$rtl['register_date']);
        $arr_comment[]=$rtl;
    }
    $db->free_result();
    
    $arr_rtl['comment_list']=$arr_comment;
    $arr_rtl['page']=$page;
    $arr_rtl['total_page']=$rowset->pages;
    
}while (0);

exit(json_encode($arr_rtl));