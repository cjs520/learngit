<?php

/**
 * MVM_MALL 网上商店系统 首页文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: wx_auto_reply.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if(!$ucfg['mm_wx_app_id'] || !$ucfg['mm_wx_app_secret']) show_msg('请先将微信公众平台资料填写完整');

if($action=='list')
{
	//资讯自动应答
    $arr_reply_news=array();
    $q=$db->query("SELECT uid,title,description,pic,url FROM `{$tablepre}wx_auto_reply` WHERE supplier_id='$page_member_id' AND type='news' ORDER BY od LIMIT 5");
    while($rtl=$db->fetch_array($q))
    {
        $arr_reply_news[]=$rtl;
    }
    $db->free_result();
    for($i=0;$i<5;$i++)
    {
        if(!$arr_reply_news[$i]['pic'] || !file_exists($arr_reply_news[$i]['pic'])) $arr_reply_news[$i]['pic']='images/noimages/noproduct.jpg';
        $arr_reply_news[$i]['uid']=(int)$arr_reply_news[$i]['uid'];
    }
    
	include template('sadmin_wx_auto_reply');
}
else if($action=='add')
{
	if($_POST && (int)$step==1)    //资讯自动应答提交
    {
        $url=$_POST['url'];
        if(!is_array($uid) || !is_array($od) || !is_array($title) || !is_array($description) || !is_array($url)) show_msg('参数传递有问题，请仔细检查');
        $size_title=sizeof($title);
        if(sizeof($uid)!=$size_title || sizeof($od)!=$size_title || sizeof($description)!=$size_title || sizeof($url)!=$size_title) show_msg('参数数量匹配错误，请仔细检查');
        
        foreach ($title as $key=>$val)
        {
            if(!$val) continue;
            $pic='';
            if($uid[$key]>0)
            {
                $rtl=$db->get_one("SELECT uid,pic FROM `{$tablepre}wx_auto_reply` WHERE uid='{$uid[$key]}' LIMIT 1");
                $pic=$rtl['pic'];
            }
            
            require_once 'include/upfile.class.php';
            $rowset = new upfile('gif,jpg,png,bmp','upload/auto_reply/');
            if($_FILES['pic']['name'][$key])
            {
                file_unlink($pic);
                $file_form=array(
                    'name'=>$_FILES['pic']['name'][$key],
                    'type'=>$_FILES['pic']['type'][$key],
                    'tmp_name'=>$_FILES['pic']['tmp_name'][$key],
                    'error'=>$_FILES['pic']['error'][$key],
                    'size'=>$_FILES['pic']['size'][$key]
                );
                $pic = $rowset->upload($file_form);
                
            }
            
            $row=array(
                'supplier_id'=>$page_member_id,
                'title'=>$val,
                'description'=>dhtmlchars($description[$key]),
                'pic'=>$pic,
                'url'=>$url[$key],
                'type'=>'news',
                'od'=>$od[$key]
            );
            if($uid[$key]>0) $db->update("`{$tablepre}wx_auto_reply`",$row,"uid='{$uid[$key]}'");
            else $db->insert("`{$tablepre}wx_auto_reply`",$row);
        }
    }
    
    show_msg('修改成功',"sadmin.php?module=$module&action=list");
}
else if($action=='del')
{
	$uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,title,pic FROM `{$tablepre}wx_auto_reply` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
    if(!$rtl) exit('ERR:检索不到您指定的记录');
    file_unlink($rtl['pic']);
    $db->query("DELETE FROM `{$tablepre}wx_auto_reply` WHERE uid='$uid'");
    $db->free_result();
    exit('OK:删除成功');
}