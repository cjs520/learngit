<?php
require_once 'include/pager.class.php';
require_once 'include/pic.class.php';
$filter_sql="WHERE m_uid='$m_check_uid'";

if($cmd=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,c_logo FROM `{$tablepre}community` WHERE uid='$uid' AND m_uid='$m_check_uid' LIMIT 1");
    if($rtl)
    {
        file_unlink(str_replace('@!community', '', $rtl['c_logo']),'bucket');
        $db->query("DELETE FROM `{$tablepre}community` WHERE uid='$uid'");
        $db->free_result();
    }
    exit;
}
else if($cmd=='add')
{
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community` WHERE m_uid='$m_check_uid'");
    $mm_open_community=(int)$mm_open_community;
    if($rtl['cnt']>=$mm_open_community) show_msg("每个会员最多只能创建{$mm_open_community}生活圈");
    
    if($_POST && (int)$step==1)
    {
        $cat_uid=(int)$cat[1];
        $c_name=dhtmlchars(trim($c_name));
        $c_intro=dhtmlchars(strip_tags($c_intro));
        $c_hobby=dhtmlchars($c_hobby);
        $c_proclaim=dhtmlchars($c_proclaim);
        $c_tag=dhtmlchars(trim(strip_tags($c_tag)));
        $join_check=(int)$join_check==2?2:1;
        $agree=(int)$agree;
        
        if($cat_uid<=0) show_msg('请选择圈子分类');
        if(!$c_name) show_msg('请填写圈子名称');
        if(!$c_tag) show_msg('请填写圈子标签');
        if(!$agree) show_msg('请认真阅读并同意《圈子指导原则》');
        
        $arr_tmp=explode(' ',$c_tag);
        $arr_tmp_target=array();
        $i=0;
        foreach ($arr_tmp as $val)
        {
            if($i>=3) break;
            $val=trim($val);
            if(!$val) continue;
            $arr_tmp_target[]=$val;
            $i++;
        }
        $c_tag=implode(' ',$arr_tmp_target);
        
        $c_logo='';
        if ($_FILES['c_logo']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','upload/community/');
            $c_logo = $rowset->upload('c_logo');
            $c_logo=$c_logo.'@!community';
        }
        $row=array(
            'c_name'=>$c_name,
            'c_tag'=>$c_tag,
            'c_cat'=>$cat_uid,
            'c_intro'=>$c_intro,
            'c_hobby'=>$c_hobby,
            'm_uid'=>$m_check_uid,
            'm_id'=>$m_check_id,
            'c_proclaim'=>$c_proclaim,
            'c_logo'=>$c_logo,
            'join_check'=>$join_check,
            'register_date'=>$m_now_time
        );
        $db->insert("`{$tablepre}community`",$row);
        $db->free_result();
        
        show_msg('圈子创建成功，请等待管理员审核',"member.php?action=$action");
    }
    
    $join_checked_1='checked';
    $pic='images/noimages/noproduct.jpg';
    $cat_name='未选择';
    require 'header.php';
    include template('member_my_community_add');
}
else if($cmd=='edit')
{
    $uid=(int)$uid;
    $comm=$db->get_one("SELECT * FROM `{$tablepre}community` WHERE uid='$uid' AND m_uid='$m_check_uid' LIMIT 1");
    if(!$comm) show_msg('检索不到您指定的生活圈');
    
    if($_POST && (int)$step==1)
    {
        $cat_uid=(int)$cat[1]?(int)$cat[1]:$comm['c_cat'];
        $c_name=dhtmlchars(trim($c_name));
        $c_intro=dhtmlchars(strip_tags($c_intro));
        $c_hobby=dhtmlchars($c_hobby);
        $c_proclaim=dhtmlchars($c_proclaim);
        $c_tag=dhtmlchars(trim(strip_tags($c_tag)));
        $join_check=(int)$join_check==2?2:1;
        $agree=(int)$agree;
        
        if(!$c_name) show_msg('请填写圈子名称');
        if(!$c_tag) show_msg('请填写圈子标签');
        if(!$agree) show_msg('请认真阅读并同意《圈子指导原则》');
        
        $arr_tmp=explode(' ',$c_tag);
        $arr_tmp_target=array();
        $i=0;
        foreach ($arr_tmp as $val)
        {
            if($i>=3) break;
            $val=trim($val);
            if(!$val) continue;
            $arr_tmp_target[]=$val;
            $i++;
        }
        $c_tag=implode(' ',$arr_tmp_target);
        
        $c_logo=$comm['c_logo'];
        if ($_FILES['c_logo']['name']!='')
        {
            file_unlink(str_replace('@!community', '', $c_logo),'bucket');
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','upload/community/');
            $c_logo = $rowset->upload('c_logo');
            $c_logo=$c_logo.'@!community';
        }
        $row=array(
            'c_name'=>$c_name,
            'c_tag'=>$c_tag,
            'c_cat'=>$cat_uid,
            'c_intro'=>$c_intro,
            'c_hobby'=>$c_hobby,
            'm_uid'=>$m_check_uid,
            'm_id'=>$m_check_id,
            'c_proclaim'=>$c_proclaim,
            'c_logo'=>$c_logo,
            'join_check'=>$join_check
        );
        $db->update("`{$tablepre}community`",$row," uid='$comm[uid]' ");
        $db->free_result();
        
        show_msg('生活圈信息修改成功',base64_decode($p_url));
    }
    
    $cat_name='';
    do
    {
        $rtl=$db->get_one("SELECT category_id,category_name FROM `{$tablepre}category` WHERE uid='$comm[c_cat]' LIMIT 1");
        $cat_name=$rtl['category_name'];
        if($rtl['category_id']<=0) break;
        $rtl=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$rtl[category_id]' LIMIT 1");
        $cat_name=$rtl['category_name'].' -> '.$cat_name;
    }while (0);
    
    if($comm['join_check']==2) $join_checked_2='checked';
    else $join_checked_1='checked';
    $pic=IMG_URL.$comm['c_logo'];
    //if(!$pic || !file_exists($pic)) $pic='images/noimages/noproduct.jpg';
    require 'header.php';
    include template('member_my_community_add');
}
else
{
    $p_url=base64_encode($mm_refer_url);
    
    $arr_community = array();
    $total_count = $db->counter("`{$tablepre}community`"," WHERE m_uid='$m_check_uid'");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,c_logo,c_name,approval_date,back_reason,c_cat,c_tag 
                     FROM `{$tablepre}community` 
                     WHERE m_uid='$m_check_uid' 
                     ORDER BY approval_date DESC  
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        //if(!$rtl['c_logo'] || !file_exists($rtl['c_logo'])) $rtl['c_logo']='images/noimages/noproduct.jpg';
        $rtl['status']=get_status($rtl['approval_date']);
        
        $cat=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$rtl[c_cat]' LIMIT 1");
        $rtl['c_cat']=$cat['category_name'];
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_member` WHERE c_uid='$rtl[uid]'");
        $rtl['member_num']=(int)$rtl_tmp['cnt'];
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_topic` WHERE c_uid='$rtl[uid]'");
        $rtl['topic_num']=(int)$rtl_tmp['cnt'];
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}community_comment` WHERE c_uid='$rtl[uid]'");
        $rtl['comment_num']=(int)$rtl_tmp['cnt'];
        $rtl['c_logo']=IMG_URL.$rtl['c_logo'];
        $arr_community[] = $rtl;
    }

    $page_list = $rowset->link("member.php?action=$action&page=");

    require 'header.php';
    include template('member_my_community');
}

function get_status($approval_date)
{
    $approval_date=(int)$approval_date;
    if($approval_date==-1) return '已驳回';
    else if($approval_date==0) return '未审核';
    else if($approval_date>10) return '已审核';
}