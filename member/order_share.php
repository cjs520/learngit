<?php
require_once 'include/pager.class.php';
require_once 'include/pic.class.php';
$filter_sql="WHERE m_uid='$m_check_uid'";

if($cmd=='del')
{
    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT pics FROM `{$tablepre}order_share` WHERE uid='$uid' AND m_uid='$m_check_uid' LIMIT 1");
    if($rtl)
    {
        $arr_tmp=unserialize($rtl['pics']);
        if(is_array($arr_tmp) && $arr_tmp)
        {
            foreach ($arr_tmp as $val) file_unlink($val);
        }
        $db->query("DELETE FROM `{$tablepre}order_share` WHERE uid='$uid'");
        $db->free_result();
    }
    exit;
}
else if($cmd=='del_pic')
{
    $uid=(int)$uid;
    $pic_idx=(int)$pic_idx;
    $rtl=$db->get_one("SELECT uid,pics FROM `{$tablepre}order_share` WHERE uid='$uid' AND m_uid='$m_check_uid' LIMIT 1");
    do
    {
        if(!$rtl) break;
        $rtl['pics']=unserialize($rtl['pics']);
        if(!is_array($rtl['pics']) || !$rtl['pics']) break;
        if(!isset($rtl['pics'][$pic_idx])) break;
        file_unlink($rtl['pics'][$pic_idx]);
        unset($rtl['pics'][$pic_idx]);
        $rtl['pics']=serialize($rtl['pics']);
        
        $db->query("UPDATE `{$tablepre}order_share` SET pics='$rtl[pics]' WHERE uid='$rtl[uid]'");
        $db->free_result();
    }while (0);
    exit;
}
else if($cmd=='add')
{
    $og_uid=(int)$og_uid;
    if($og_uid<=0) show_msg('指定的分享商品错误');
    $share=$db->get_one("SELECT uid FROM `{$tablepre}order_share` WHERE m_uid='$m_check_uid' AND og_uid='$og_uid' LIMIT 1");
    if($share) move_page("member.php?action=$action&cmd=edit&uid=$share[uid]");
    
    $og=$db->get_one("SELECT uid,order_id,goods_name,g_uid,goods_table,module,buy_number,buy_price,goods_attr,supplier_id 
                      FROM `{$tablepre}order_goods` 
                      WHERE uid='$og_uid' AND status=1 
                      LIMIT 1");
    if(!$og) show_msg('检索不到指定的分享商品');
    $order_info=$db->get_one("SELECT uid,ordersn FROM `{$tablepre}order_info` WHERE uid='$og[order_id]' AND username='$m_check_id' LIMIT 1");
    if(!$order_info) show_msg('检索不到指定的订单');
    
    $g=$db->get_one("SELECT uid,goods_file1,goods_category FROM `$og[goods_table]` WHERE uid='$og[g_uid]' LIMIT 1");
    
    if($_POST && (int)$step==1)
    {
        $comment=dhtmlchars(trim($comment));
        if(!$comment) show_msg('请填写分享心得');
        $pics=array();
        
        if($_FILES['gallery']['name'][0]!='')
        {
            require_once 'include/upfile.class.php';
            $f = new upfile('jpg,jpeg,png','upload/share/');
            foreach ($_FILES['gallery']['name'] as $key => $value)
            {
                if(!$_FILES['gallery']['name'][$key]) continue;
                $upload = array(
                    'name' => $_FILES['gallery']['name'][$key],
                    'type' => $_FILES['gallery']['type'][$key],
                    'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                    'error' => $_FILES['gallery']['error'][$key],
                    'size' => $_FILES['gallery']['size'][$key],
                );
                $img = $f->upload($upload);
                $pics[]=pic::PicZoomWithWH($img,200,-1,true);
            }
        }
        
        $row=array(
            'goods_name'=>$og['goods_name'],
            'og_uid'=>$og['uid'],
            'g_uid'=>$og['g_uid'],
            'goods_table'=>$og['goods_table'],
            'module'=>$og['module'],
            'goods_category'=>$g['goods_category'],
            'attr'=>$og['goods_attr'],
            'buy_price'=>$og['buy_price'],
            'comment'=>$comment,
            'pics'=>serialize($pics),
            'm_uid'=>$m_check_uid,
            'supplier_id'=>$og['supplier_id'],
            'register_date'=>$m_now_time
        );
        $db->insert("`{$tablepre}order_share`",$row);
        $db->free_result();
        
        show_msg('分享信息发布成功',"member.php?action=$action");
    }
    
    
    $g['goods_file1']=ProcImgPath($g['goods_file1']);
    $g['url']=GetBaseUrl($og['module'],$og['g_uid'],'action',$og['supplier_id']);
    
    $og['buy_price']=currency($og['buy_price']);
    $upload_num=5;
    
    require 'header.php';
    include template('member_order_share_add');
}
else if($cmd=='edit')
{
    $uid=(int)$uid;
    $share=$db->get_one("SELECT uid,pics,comment,og_uid,goods_table,module,g_uid 
                         FROM `{$tablepre}order_share` 
                         WHERE uid='$uid' AND m_uid='$m_check_uid' 
                         LIMIT 1");
    if(!$share) show_msg('检索不到指定的分享纪录');
    
    if($_POST && (int)$step==1)
    {
        $comment=dhtmlchars(trim($comment));
        if(!$comment) show_msg('请填写分享心得');
        $pics=unserialize($share['pics']);
        if(!$pics) $pics=array();
        
        if($_FILES['gallery']['name'][0]!='')
        {
            require_once 'include/upfile.class.php';
            $f = new upfile('jpg,jpeg,png','upload/share/');
            foreach ($_FILES['gallery']['name'] as $key => $value)
            {
                if(!$_FILES['gallery']['name'][$key]) continue;
                $upload = array(
                    'name' => $_FILES['gallery']['name'][$key],
                    'type' => $_FILES['gallery']['type'][$key],
                    'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                    'error' => $_FILES['gallery']['error'][$key],
                    'size' => $_FILES['gallery']['size'][$key],
                );
                $img = $f->upload($upload);
                $pics[]=pic::PicZoomWithWH($img,200,-1,true);
            }
        }
        
        $row=array(
            'comment'=>$comment,
            'pics'=>serialize($pics)
        );
        $db->update("`{$tablepre}order_share`",$row," uid='$share[uid]' ");
        $db->free_result();
        
        show_msg('分享信息修改成功',$p_url?base64_decode($p_url):"member.php?action=$action");
    }
    
    $og=$db->get_one("SELECT uid,order_id,goods_name,g_uid,goods_table,module,buy_number,buy_price,goods_attr,supplier_id 
                      FROM `{$tablepre}order_goods` 
                      WHERE uid='$share[og_uid]' 
                      LIMIT 1");
    
    $g=$db->get_one("SELECT uid,goods_file1,goods_category FROM `$og[goods_table]` WHERE uid='$og[g_uid]' LIMIT 1");
    $g['goods_file1']=ProcImgPath($g['goods_file1']);
    $g['url']=GetBaseUrl($og['module'],$og['g_uid'],'action',$og['supplier_id']);
    
    $og['buy_price']=currency($og['buy_price']);
    $arr_pics=unserialize($share['pics']);
    if(!is_array($arr_pics)) $arr_pics=array();
    $upload_num=5-sizeof($arr_pics);
    
    require 'header.php';
    include template('member_order_share_add');
}
else
{
    $p_url=base64_encode($mm_refer_url);
    
    $arr_order_share = array();
    $total_count = $db->counter("`{$tablepre}order_share`"," WHERE m_uid='$m_check_uid' ");
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,attr,buy_price,goods_name,og_uid,g_uid,module,goods_table,supplier_id,love,comment 
                     FROM `{$tablepre}order_share` 
                     WHERE m_uid='$m_check_uid' 
                     ORDER BY register_date DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl = $db->fetch_array($q))
    {
        $g=$db->get_one("SELECT uid,goods_file1 FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
        $rtl['goods_file1']=ProcImgPath($g['goods_file1']);
        $rtl['url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
        
        $rtl['buy_price']=currency($rtl['buy_price']);
        
        $arr_order_share[] = $rtl;
    }

    $page_list = $rowset->link("member.php?action=$action&page=");

    require 'header.php';
    include template('member_order_share');
}
