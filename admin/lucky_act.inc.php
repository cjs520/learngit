<?php

/**
 * MVM_MALL 网上商店系统  团购活动管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: lucky_act.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');

if($action=='list')
{
    $arr_lucky=array();
    require_once 'include/pager.class.php';
    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}lucky_table`");
    $total_count = $rtl['cnt'];
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $sql = "SELECT uid,name,start_time,end_time,reg_date,point 
            FROM `{$tablepre}lucky_table` USE INDEX (`PRIMARY`)
            ORDER BY uid DESC
            LIMIT $from_record, $list_num";
    $q = $db->query($sql);
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['start_time']=date('Y-m-d',$rtl['start_time']);
        $rtl['end_time']=date('Y-m-d',$rtl['end_time']);
        $rtl['reg_date']=date('Y-m-d',$rtl['reg_date']);
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}lucky_rec` WHERE lucky_uid='$rtl[uid]'");
        $rtl['join_num']=$rtl_tmp['cnt'];
        
        $rtl_tmp=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}lucky_rec` WHERE lucky_uid='$rtl[uid]' AND lucky_g_uid>0");
        $rtl['lucky_num']=$rtl_tmp['cnt'];
        
        $arr_lucky[] = $rtl;
    }
    $db->free_result();
    
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&page=");
    require_once template('lucky_act');
    footer();
}
else if ($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $name=dhtmlchars($name);
        if(!$name) show_msg('请填写活动名称');
        $start_time=strtotime($start_time);
        $end_time=strtotime($end_time);
        
        require_once 'include/upfile.class.php';
        $f = new upfile('gif,jpg,png,jpeg,GIF,JPG,PNG,JPEG','upload/lucky/');
        if($_FILES['img']['name']!='')
        {
            $img = $f->upload('img');
        }
        
        $row=array(
            'name'=>$name,
            'point'=>(int)$point,
            'end_time'=>$end_time,
            'start_time'=>$start_time,
            'img'=>$img,
            'fail_rate'=>(int)$fail_rate,
            'fail_degree_min'=>(int)$fail_degree_min,
            'fail_degree_max'=>(int)$fail_degree_max,
            'reg_date'=>$m_now_time
        );
        $insert_id=$db->insert("`{$tablepre}lucky_table`",$row);
        $db->free_result();
        
        if($_FILES['goods_img']['name'][0]!='')
        {
            $f = new upfile('gif,jpg,png,bmp','upload/lucky/');
            foreach ($_FILES['goods_img']['name'] as $key => $value)
            {
                $upload = array(
                    'name' => $_FILES['goods_img']['name'][$key],
                    'type' => $_FILES['goods_img']['type'][$key],
                    'tmp_name' => $_FILES['goods_img']['tmp_name'][$key],
                    'error' => $_FILES['goods_img']['error'][$key],
                    'size' => $_FILES['goods_img']['size'][$key],
                );
                $goods_img = $f->upload($upload);
                
                $row=array(
                    'lucky_uid'=>$insert_id,
                    'lucky_name'=>dhtmlchars($lucky_name[$key]),
                    'goods_name'=>dhtmlchars($goods_name[$key]),
                    'url'=>dhtmlchars($url[$key]),
                    'goods_img'=>$goods_img,
                    'degree_min'=>intval($degree_min[$key])%360,
                    'degree_max'=>intval($degree_max[$key])%360,
                    'od'=>$od[$key],
                    'rate'=>(int)$rate[$key]
                );
                $db->insert("`{$tablepre}lucky_goods`",$row);
                $db->free_result();
            }
        }
        
        show_msg('活动添加成功',"admincp.php?module=$module&action=list");
    }
    
    $img='images/noimages/noproduct.jpg';
    
    require_once template('lucky_act_add');
    footer();
}
else if ($action == 'edit')
{
	$uid=(int)$uid;
	$lucky=$db->get_one("SELECT * FROM `{$tablepre}lucky_table` WHERE uid='$uid' LIMIT 1");
    if(!$lucky) show_msg('检索不到指定的抽奖活动记录');
	
    if($_POST && (int)$step==1)
    {
        $name=dhtmlchars($name);
        if(!$name) show_msg('请填写活动名称');
        $start_time=strtotime($start_time);
        $end_time=strtotime($end_time);
        
        require_once 'include/upfile.class.php';
        $f = new upfile('gif,jpg,png,jpeg,GIF,JPG,PNG,JPEG','upload/lucky/');
        if($_FILES['img']['name']!='')
        {
            $img = $f->upload('img');
            file_unlink($lucky['img'],'buctket');
        }
        else $img=$lucky['img'];
        
        $row=array(
            'name'=>$name,
            'point'=>(int)$point,
            'end_time'=>$end_time,
            'start_time'=>$start_time,
            'img'=>$img,
            'fail_rate'=>(int)$fail_rate,
            'fail_degree_min'=>(int)$fail_degree_min,
            'fail_degree_max'=>(int)$fail_degree_max,
        );
        $db->update("`{$tablepre}lucky_table`",$row," uid='$uid' ");
        $db->free_result();
        
        if($_FILES['goods_img']['name'][0]!='')
        {
            $f = new upfile('gif,jpg,png,bmp','upload/lucky/');
            foreach ($_FILES['goods_img']['name'] as $key => $value)
            {
                $upload = array(
                    'name' => $_FILES['goods_img']['name'][$key],
                    'type' => $_FILES['goods_img']['type'][$key],
                    'tmp_name' => $_FILES['goods_img']['tmp_name'][$key],
                    'error' => $_FILES['goods_img']['error'][$key],
                    'size' => $_FILES['goods_img']['size'][$key],
                );
                $goods_img = $f->upload($upload);
                
                $row=array(
                    'lucky_uid'=>$uid,
                    'lucky_name'=>dhtmlchars($lucky_name[$key]),
                    'goods_name'=>dhtmlchars($goods_name[$key]),
                    'url'=>dhtmlchars($url[$key]),
                    'goods_img'=>$goods_img,
                    'degree_min'=>intval($degree_min[$key])%360,
                    'degree_max'=>intval($degree_max[$key])%360,
                    'od'=>$od[$key],
                    'rate'=>(int)$rate[$key]
                );
                $db->insert("`{$tablepre}lucky_goods`",$row);
                $db->free_result();
            }
        }
        $db->query("DELETE FROM `{$tablepre}lucky_log` WHERE lucky_uid='$uid'");
        $db->free_result();
        
        show_msg('活动编辑成功',"admincp.php?module=$module&action=list");
    }
    
    extract($lucky,EXTR_OVERWRITE);
    $start_time=date('Y-m-d H:i',$start_time);
    $end_time=date('Y-m-d H:i',$end_time);
    $img=$img?$img:'images/noimages/noproduct.jpg';
    $img= IMG_URL.$img;
    
    $arr_lucky_goods=array();
    $q=$db->query("SELECT uid,lucky_name,goods_name,url,goods_img,degree_min,degree_max,od,rate FROM `{$tablepre}lucky_goods` WHERE lucky_uid='$uid' ORDER BY od");
    while ($rtl=$db->fetch_array($q))
    {
        if(!$rtl['url']) $rtl['url']='#';
        else $rtl['target']="target='_blank'";
        $rtl['goods_img']=IMG_URL.$rtl['goods_img'];
        $arr_lucky_goods[]=$rtl;
    }
    
    require_once template('lucky_act_add');
    footer();
}
else if($action=='del')
{
    $uid = (int)$uid;
    do
    {
        if($uid<=0) break;
        $lucky=$db->get_one("SELECT uid,img,name FROM `{$tablepre}lucky_table` WHERE uid='$uid' LIMIT 1");
        if(!$lucky) break;
        if($lucky['img']) file_unlink($lucky['img'],'buctket');
        admin_log("删除抽奖：$lucky[name]");
        $q=$db->query("SELECT goods_img FROM `{$tablepre}lucky_goods` WHERE lucky_uid='$uid'");
        while ($rtl=$db->fetch_array($q))
        {
            if($rtl['goods_img']) file_unlink($rtl['goods_img'],'buctket');
        }
        $db->query("DELETE FROM `{$tablepre}lucky_goods` WHERE lucky_uid='$uid'");
        $db->query("DELETE FROM `{$tablepre}lucky_rec` WHERE lucky_uid='$uid'");
        $db->query("DELETE FROM `{$tablepre}lucky_log` WHERE lucky_uid='$uid'");
        $db->query("DELETE FROM `{$tablepre}lucky_table` WHERE uid='$uid'");
        $db->free_result();
    }while(0);
   
   exit;
}
else if($action=='del_goods')
{
    $uid=(int)$uid;
    $lucky_goods=$db->get_one("SELECT uid,goods_img,lucky_name,goods_name FROM `{$tablepre}lucky_goods` WHERE uid='$uid' LIMIT 1");
    if(!$lucky_goods) exit;
    admin_log("删除抽奖奖品：$lucky_goods[lucky_name] $lucky_goods[goods_name]");
    if($lucky_goods['goods_img']) file_unlink($lucky_goods['goods_img'],'buctket');
    $db->query("DELETE FROM `{$tablepre}lucky_goods` WHERE uid='$uid'");
    $db->free_result();
    exit;
}
else admin_msg('pass_worng');
