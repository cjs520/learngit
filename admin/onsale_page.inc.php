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
 * $Id: onsale_page.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if($action=='list')
{
    $topic=(int)$topic;
    if($topic<1 || $topic>5) $topic=1;
    
	$onsale_data=array();
	$q=$db->query("SELECT * FROM `{$tablepre}onsale_page` WHERE topic=$topic");
	while($rtl=$db->fetch_array($q))
	{
		$onsale_data[$rtl['type']][$rtl['level']]=$rtl;
	}
	
	if(!$onsale_data['bk_color'][0]['data']) $onsale_data['bk_color'][0]['data']='#ffffff';
	$onsale_data['bk_color'][0]['r']=hexdec(substr($onsale_data['bk_color'][0]['data'],1,2));
	$onsale_data['bk_color'][0]['g']=hexdec(substr($onsale_data['bk_color'][0]['data'],3,2));
	$onsale_data['bk_color'][0]['b']=hexdec(substr($onsale_data['bk_color'][0]['data'],5,2));
	$onsale_data['top_ad'][0]['data']=IMG_URL.$onsale_data['top_ad'][0]['data'];
	$onsale_data['top_btn'][0]['data']=IMG_URL.$onsale_data['top_btn'][0]['data'];
	$onsale_data['top_btn_hl'][0]['data']=IMG_URL.$onsale_data['top_btn_hl'][0]['data'];
	
	$arr_level_use=explode(',',$onsale_data['level_use'][0]['data']);
	foreach ($arr_level_use as $val) ${"level_use_$val"}='checked';
	
	$rtl=$db->get_one("SELECT data FROM `{$tablepre}onsale_page` WHERE topic=0 AND type='topic_use' LIMIT 1");
	$arr_topic_use=explode(',',$rtl['data']);
	foreach ($arr_topic_use as $val) ${"topic_use_$val"}='checked';
	
    require_once template('onsale_page');
    footer();
}
else if($action=='add')
{
    $topic=(int)$topic;
    if($_POST) admin_log("编辑专题页面：第".strval($topic).'专题');
    
	if($_POST && (int)$step==1)    //顶部推广
	{
	    $rtl=$db->get_one("SELECT * FROM `{$tablepre}onsale_page` WHERE type='top_ad' AND topic=$topic LIMIT 1");
	    $top_ad=$rtl['data'];
	    if ($_FILES['top_ad']['name']!='')
        {
           if(@fopen(IMG_URL.$top_ad, 'r')) file_unlink($top_ad,'buctket');
        	require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,jpeg',"images/topic/");
            $top_ad = $f->upload('top_ad');
            
            $data_row=array(
                'topic'=>$topic,
                'type'=>'top_ad',
                'level'=>0,
                'data'=>$top_ad
            );
            if($rtl) $db->update("`{$tablepre}onsale_page`",$data_row," uid='$rtl[uid]' ");
            else $db->insert("`{$tablepre}onsale_page`",$data_row);
        }
        
        $rtl=$db->get_one("SELECT * FROM `{$tablepre}onsale_page` WHERE type='top_btn' AND topic=$topic LIMIT 1");
	    $top_btn=$rtl['data'];
        if ($_FILES['top_btn']['name']!='')
        {
             if(@fopen(IMG_URL.$top_btn, 'r')) file_unlink($top_btn,'buctket');
        	require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,jpeg',"images/topic/");
            $top_btn = $f->upload('top_btn');
            
            $data_row=array(
                'topic'=>$topic,
                'type'=>'top_btn',
                'level'=>0,
                'data'=>$top_btn
            );
            if($rtl) $db->update("`{$tablepre}onsale_page`",$data_row," uid='$rtl[uid]' ");
            else $db->insert("`{$tablepre}onsale_page`",$data_row);
        }
        
        $rtl=$db->get_one("SELECT * FROM `{$tablepre}onsale_page` WHERE type='top_btn_hl' AND topic=$topic LIMIT 1");
	    $top_btn_hl=$rtl['data'];
        if ($_FILES['top_btn_hl']['name']!='')
        {
             if(@fopen(IMG_URL.$top_btn_hl, 'r')) file_unlink($top_btn_hl,'buctket');
        	require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,jpeg',"images/topic/");
            $top_btn_hl = $f->upload('top_btn_hl');
            
            $data_row=array(
                'topic'=>$topic,
                'type'=>'top_btn_hl',
                'level'=>0,
                'data'=>$top_btn_hl
            );
            if($rtl) $db->update("`{$tablepre}onsale_page`",$data_row," uid='$rtl[uid]' ");
            else $db->insert("`{$tablepre}onsale_page`",$data_row);
        }
        
        $db->query("DELETE FROM `{$tablepre}onsale_page` WHERE type='top_ad_color' AND topic=$topic");
        $data_row=array(
            'topic'=>$topic,
            'type'=>'top_ad_color',
            'level'=>0,
            'data'=>$top_ad_color
        );
        $db->insert("`{$tablepre}onsale_page`",$data_row);
	}
	else if($_POST && (int)$step==3)    //楼层使用
	{
		$rtl=$db->get_one("SELECT * FROM `{$tablepre}onsale_page` WHERE type='level_use' AND topic=$topic LIMIT 1");
		foreach ($level_use as $key=>$val)
		{
			$val=(int)$val;
			if($val<=0) unset($level_use[$key]);
		}
		$level_use=implode(',',$level_use);
		
		$data_row=array(
            'topic'=>$topic,
            'type'=>'level_use',
            'level'=>0,
            'data'=>$level_use,
            'link'=>''
        );
        if($rtl) $db->update("`{$tablepre}onsale_page`",$data_row," uid='$rtl[uid]' ");
        else $db->insert("`{$tablepre}onsale_page`",$data_row);
	}
	else if($_POST && (int)$step==5)    //楼顶和楼标
	{
		$level=(int)$level;
		if(!in_array($level,array(1,2,3,4,5,6,7))) show_msg('楼层异常！');
		
		if ($_FILES['ceiling']['name']!='')    //楼顶
		{
			$rtl=$db->get_one("SELECT * FROM `{$tablepre}onsale_page` WHERE type='ceiling' AND level='$level' AND topic=$topic LIMIT 1");
			$link=dhtmlchars($link);
			$ceiling=$rtl['data'];
			
			if(@fopen(IMG_URL.$ceiling, 'r')) file_unlink($ceiling,'buctket');
			require_once MVMMALL_ROOT.'include/upfile.class.php';
			$f = new upfile('gif,jpg,png,jpeg',"images/topic/");
			$ceiling = $f->upload('ceiling');
			
			$data_row=array(
			    'topic'=>$topic,
			    'type'=>'ceiling',
			    'level'=>$level,
			    'data'=>$ceiling,
			    'link'=>$link
			);
			if($rtl) $db->update("`{$tablepre}onsale_page`",$data_row," uid='$rtl[uid]' ");
			else $db->insert("`{$tablepre}onsale_page`",$data_row);
		}
	}
	else if($_POST && (int)$step==6)    //设置专题使用数量
	{
	    $topic_use=(int)$topic_use;
	    if(!in_array($topic_use,array(1,2,3,4,5))) exit;
	    $op=dhtmlchars($op);
	    $rtl=$db->get_one("SELECT uid,type,data FROM `{$tablepre}onsale_page` WHERE type='topic_use' LIMIT 1");
	    if($op=='add')
	    {
	        if($rtl)
	        {
	            if(strstr($rtl['data'],strval($topic_use))) exit;    //已经存在
	            $rtl['data'].=','.strval($topic_use);
	            $arr_tmp=explode(',',$rtl['data']);
	            sort($arr_tmp,SORT_ASC);
	            $arr_tmp=array_unique($arr_tmp);
	            $rtl['data']=implode(',',$arr_tmp);
	            $db->query("UPDATE `{$tablepre}onsale_page` SET data='$rtl[data]' WHERE uid='$rtl[uid]'");
	            $db->free_result();
	        }
	        else
	        {
	            $db->query("INSERT INTO `{$tablepre}onsale_page` (type,data) VALUES ('topic_use','$topic_use')");
	            $db->free_result();
	        }
	    }
	    else if($op=='del' && $rtl)
	    {
	        if(!strstr($rtl['data'],strval($topic_use))) exit;
	        $arr_tmp=explode(',',$rtl['data']);
	        foreach ($arr_tmp as $key=>$val)
	        {
	            if($val==$topic_use) unset($arr_tmp[$key]);
	        }
	        $rtl['data']=implode(',',$arr_tmp);
	        $db->query("UPDATE `{$tablepre}onsale_page` SET data='$rtl[data]' WHERE uid='$rtl[uid]'");
	        $db->free_result();
	    }
	    
	    exit;
	}
	else if($_POST && (int)$step==7)    //页面背景颜色
	{
		$rtl=$db->get_one("SELECT * FROM `{$tablepre}onsale_page` WHERE type='bk_color' AND topic=$topic LIMIT 1");
	    $bk_color=dhtmlchars($bk_color);
	    	    
        $data_row=array(
            'topic'=>$topic,
            'type'=>'bk_color',
            'level'=>0,
            'data'=>$bk_color,
            'link'=>''
        );
        if($rtl) $db->update("`{$tablepre}onsale_page`",$data_row," uid='$rtl[uid]' ");
        else $db->insert("`{$tablepre}onsale_page`",$data_row);
	}
	show_msg('设置成功',"admincp.php?module=$module&action=list&topic=$topic");
}
else if($action=='del')
{
	$uid=(int)$uid;
	$data=dhtmlchars($data);
	
	$rtl=$db->get_one("SELECT type,topic,data FROM `{$tablepre}onsale_page` WHERE uid='$uid' LIMIT 1");
	if(!$rtl) exit;
	admin_log("删除第$rtl[topic]专题资料$rtl[type]");
	
	switch ($rtl['type'])
	{
		case 'top_ad':
		case 'ceiling':
		case 'ceiling_icon':
		case 'top_btn_hl':
		case 'top_btn':
		 if(@fopen(IMG_URL.$rtl['data'], 'r')) file_unlink($rtl['data'],'buctket');
		default:
		    $db->query("DELETE FROM `{$tablepre}onsale_page` WHERE uid='$uid'");
		    break;
	}
}
else show_msg('pass_worng');
