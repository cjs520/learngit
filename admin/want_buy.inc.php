<?php

/**
 * MVM_MALL 网上商店系统  友情连接管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: want_buy.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/pic.class.php';

if($action=='list')
{
    $search_sql=" WHERE uid>0 ";
    $ps_search=dhtmlchars($ps_search);
    if($ps_search) $search_sql.=" AND goods_name LIKE '%$ps_search%'";
    
    $arr_want_buy=array();
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}want_buy",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,goods_name,goods_category,num,price,register_date,approval_date,pic,back_reason,od,m_uid FROM `{$tablepre}want_buy` 
                     $search_sql 
                     ORDER BY `uid` DESC  
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $m=$db->get_one("SELECT member_id FROM `{$tablepre}member_table` WHERE uid='$rtl[m_uid]' LIMIT 1");
        $rtl['member_id']=$m['member_id'];
        
        $rtl['status']=get_status($rtl['approval_date']);
        if(!$rtl['pic'] || !file_exists($rtl['pic'])) $rtl['pic']='images/noimages/noproduct.jpg';
        $rtl['price']=$rtl['price']<=0?'面议':currency($rtl['price']);
        $tmp_rtl=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$rtl[goods_category]' LIMIT 1");
        $rtl['goods_category']=$tmp_rtl['category_name'];
        
        $tmp_rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}want_buy_msg` WHERE buy_id='$rtl[uid]'");
        $rtl['msg_num']=$tmp_rtl['cnt'];
        
        $arr_want_buy[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&ps_search=".urlencode($ps_search)."&page=");
    require_once template('want_buy');
    footer();
}
else if ($action=='edit')
{
	$uid = (int)$uid;
    $supply=$db->get_one("SELECT * FROM `{$tablepre}want_buy` WHERE uid='$uid' LIMIT 1");
    if(!$supply) sadmin_show_msg('检索不到指定的求购信息',$p_url);
    
    if($_POST && (int)$step==1)
    {
        $goods_name=daddslashes(dhtmlchars($goods_name));
        if(!$goods_name) show_msg('请填写商品名称');
        $num=(int)$num;
        $num<0 && $num=0;
        $price=floatval($price);
        $price<0 && $price=0;
        $intro=dhtmlchars($intro);
        $intro=mb_substr($intro,0,200,'UTF-8');
        $province=dhtmlchars($province);
        $city=dhtmlchars($city);
        $county=dhtmlchars($county);
        $tel=dhtmlchars($tel);
        $qq=dhtmlchars($qq);
        $ww=dhtmlchars($ww);
        $od=(int)$od;
        
        $goods_category_value=0;
		foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $goods_category_value=$val;
        }
        
        $pic=$supply['pic'];
        if ($_FILES['pic']['name']!='')
        {
            file_unlink($pic);
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','upload/acc/');
            $pic = $rowset->upload('pic');
            $pic=pic::PicZoom($pic,290,290);
        }
        
        $row=array(
            'goods_category'=>$goods_category_value,
            'goods_name'=>$goods_name,
            'pic'=>$pic,
            'num'=>$num,
            'price'=>$price,
            'province'=>$province,
            'city'=>$city,
            'county'=>$county,
            'qq'=>$qq,
            'ww'=>$ww,
            'tel'=>$tel,
            'intro'=>$intro,
            'detail'=>$detail,
            'od'=>$od,
            'register_date'=>$m_now_time
        );
        $db->update("`{$tablepre}want_buy`",$row," uid='$supply[uid]' ");
        $db->free_result();
        
        admin_log("编辑求购信息：$supply[name]");
        iframe_callback('','');
    }
    $pic=$supply['pic'];
    if(!$pic || !file_exists($pic)) $pic='images/noimages/noproduct.jpg';
    require_once template('want_buy_add');
    footer(false);
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,goods_name,pic FROM `{$tablepre}want_buy` WHERE uid='$uid' LIMIT 1");
    if($rtl)
    {
        file_unlink($rtl['pic']);
        admin_log("删除求购信息：$rtl[goods_name]");
        $db->query("DELETE FROM `{$tablepre}want_buy` WHERE uid='$uid'");
        $db->free_result();
    }
    exit;
}
else if($action=='check')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,goods_name,approval_date FROM `{$tablepre}want_buy` WHERE uid='$uid' LIMIT 1");
    if($rtl)
    {
        admin_log("审核求购信息：$rtl[goods_name]");
        $db->query("UPDATE `{$tablepre}want_buy` SET approval_date='$m_now_time' WHERE uid='$uid'");
        $db->free_result();
    }
    exit(get_status($m_now_time));
}
else if($action=='back')
{
    $uid=(int)$uid;
    $back_reason=dhtmlchars(strip_tags($back_reason));
    $rtl=$db->get_one("SELECT uid,goods_name,approval_date FROM `{$tablepre}want_buy` WHERE uid='$uid' LIMIT 1");
    if($rtl)
    {
        admin_log("驳回求购信息：$rtl[goods_name]");
        $db->query("UPDATE `{$tablepre}want_buy` SET 
                    approval_date='-1',
                    back_reason='$back_reason' 
                    WHERE uid='$uid'");
        $db->free_result();
    }
    exit(get_status(-1));
}
else if($action=='set_od')
{
    $uid=(int)$uid;
    $od=(int)$od;
    $db->query("UPDATE `{$tablepre}want_buy` SET od='$od' WHERE uid='$uid'");
    $db->free_result();
    exit;
}
else show_msg('pass_worng');

function get_status($approval_date)
{
    $approval_date=(int)$approval_date;
    if($approval_date==-1) return '已驳回';
    else if($approval_date==10) return '已结束';
    else if($approval_date==0) return '未审核';
    else if($approval_date>10) return '已审核';
}