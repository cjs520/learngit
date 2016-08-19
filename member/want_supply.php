<?php
require_once 'include/pager.class.php';
require_once 'include/pic.class.php';
$filter_sql="WHERE m_uid='$m_check_uid'";

if($cmd=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT pic FROM `{$tablepre}want_supply` WHERE uid='$uid' AND m_uid='$m_check_uid' LIMIT 1");
    if($rtl)
    {
        file_unlink(str_replace('@!want_supply', '', $rtl['pic']),'bucket');
        $db->query("DELETE FROM `{$tablepre}want_supply` WHERE uid='$uid'");
        $db->free_result();
    }
    exit;
}
else if($cmd=='finish')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,approval_date FROM `{$tablepre}want_supply` WHERE uid='$uid' AND m_uid='$m_check_uid' LIMIT 1");
    if(!$rtl) exit('ERR:检索不到指定的供应信息');
    if($rtl['approval_date']<=10) exit('ERR:指定供应信息无法结束');
    
    $db->query("UPDATE `{$tablepre}want_supply` SET approval_date='10' WHERE uid='$rtl[uid]'");
    $db->free_result();
    exit('OK:设置成功');
}
else if($cmd=='add')
{
    if($_POST && (int)$step==1)
    {
        $goods_name=daddslashes(dhtmlchars($goods_name));
        if(!$goods_name) show_msg('请填写商品名称');
        $num=(int)$num;
        $num<0 && $num=0;
        $price=floatval($price);
        $price<0 && $price=0;
        $intro=daddslashes(dhtmlchars($intro));
        $intro=mb_substr($intro,0,200,'UTF-8');
        $province=dhtmlchars($province);
        $city=dhtmlchars($city);
        $county=dhtmlchars($county);
        $tel=dhtmlchars($tel);
        $qq=dhtmlchars($qq);
        $ww=dhtmlchars($ww);
        
        $goods_category_value=0;
		foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $goods_category_value=$val;
        }
        
        $pic='';
        if ($_FILES['pic']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','upload/acc/');
            $pic = $rowset->upload('pic');
            $pic=$pic.'@!want_supply';
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
            'detail'=>daddslashes($detail),
            'register_date'=>$m_now_time,
            'approval_date'=>0,
            'm_uid'=>$m_check_uid
        );
        $db->insert("`{$tablepre}want_supply`",$row);
        $db->free_result();
        show_msg('供应信息添加成功，请等待管理员审核',"member.php?action=$action");
    }
    
    $pic='images/noimages/noproduct.jpg';
    require 'header.php';
    include template('member_want_supply_add');
}
else if($cmd=='edit')
{
    $uid=(int)$uid;
    $supply=$db->get_one("SELECT * FROM `{$tablepre}want_supply` WHERE uid='$uid' AND m_uid='$m_check_uid' LIMIT 1");
    if(!$supply) show_msg('检索不到您指定的供应信息');
    
    if($_POST && (int)$step==1)
    {
        $goods_name=daddslashes(dhtmlchars($goods_name));
        if(!$goods_name) show_msg('请填写商品名称');
        $num=(int)$num;
        $num<0 && $num=0;
        $price=floatval($price);
        $price<0 && $price=0;
        $intro=daddslashes(dhtmlchars($intro));
        $intro=mb_substr($intro,0,200,'UTF-8');
        $province=dhtmlchars($province);
        $city=dhtmlchars($city);
        $county=dhtmlchars($county);
        $tel=dhtmlchars($tel);
        $qq=dhtmlchars($qq);
        $ww=dhtmlchars($ww);
        
        $goods_category_value=0;
		foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $goods_category_value=$val;
        }
        
        $pic=$supply['pic'];
        if ($_FILES['pic']['name']!='')
        {
            file_unlink(str_replace('@!want_supply', '', $pic),'bucket');
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','upload/acc/');
            $pic = $rowset->upload('pic');
            $pic=$pic.'@!want_supply';
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
            'detail'=>daddslashes($detail),
            'register_date'=>$m_now_time
        );
        $db->update("`{$tablepre}want_supply`",$row," uid='$supply[uid]' ");
        $db->free_result();
        show_msg('供应信息修改成功',base64_decode($p_url));
    }
    
    $pic=IMG_URL.$supply['pic'];
    //if(!$pic || !file_exists($pic)) $pic='images/noimages/noproduct.jpg';
    require 'header.php';
    include template('member_want_supply_add');
}
else
{
    $p_url=base64_encode($mm_refer_url);
    
    $arr_want_supply = array();
    $total_count = $db->counter("`{$tablepre}want_supply`",$filter_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,goods_name,goods_category,num,price,register_date,approval_date,pic,back_reason 
                     FROM `{$tablepre}want_supply` 
                     $filter_sql 
                     ORDER BY register_date DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['status']=get_status($rtl['approval_date']);
        $rtl['price']=$rtl['price']<=0?'面议':currency($rtl['price']);
        $tmp_rtl=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$rtl[goods_category]' LIMIT 1");
        $rtl['goods_category']=$tmp_rtl['category_name'];
        
        $tmp_rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}want_supply_msg` WHERE supply_id='$rtl[uid]'");
        $rtl['pic']=IMG_URL.$rtl['pic'];
        $rtl['msg_num']=$tmp_rtl['cnt'];
        $arr_want_supply[] = $rtl;
    }

    $page_list = $rowset->link("member.php?action=$action&page=");

    require 'header.php';
    include template('member_want_supply');
}

function get_status($approval_date)
{
    $approval_date=(int)$approval_date;
    if($approval_date==-1) return '已驳回';
    else if($approval_date==10) return '已结束';
    else if($approval_date==0) return '未审核';
    else if($approval_date>10) return '已审核';
}