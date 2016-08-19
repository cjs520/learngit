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
 * $Id: board.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

require_once 'include/pic.class.php';
if($action=='list')
{
	$q = $db->query("SELECT board_name_code,uid,board_title,od FROM `{$tablepre}badmin_table` WHERE `supplier_id`='$page_member_id' ORDER BY od");
    while ($rtl = $db->fetch_array($q))
    {
    	$rtl['link']=GetBaseUrl('board',$rtl['board_name_code'],'action',$page_member_id);
    	$board_rt[] = $rtl;
    }
    
	include template('sadmin_board');
}
else if($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $board_code=trim($board_code);
        !$board_code && show_msg('请输入系统编码');
        !preg_match('/^[a-zA-Z0-9]+$/',$board_code) && show_msg('系统编码只能由英文字符或数字组成');
        $board_code = dhtmlchars($board_code);
        $rtl = $db->get_one("SELECT uid FROM `{$tablepre}badmin_table` WHERE board_name_code = '$board_code' AND `supplier_id`='$page_member_id' LIMIT 1");
        $rtl && show_msg('编码重复');
        $od=(int)$od;
        $db->query("INSERT INTO `{$tablepre}badmin_table` SET
                    board_name_code = '$board_code',
                    board_title = '$board_name',
                    register_date = '$m_now_time',
                    supplier_id = '$page_member_id',
                    od='$od'");
    }
    show_msg('添加成功','sadmin.php?module=board&action=list');
}
else if($action=='edit')
{
	if($_POST && (int)$step==1)
	{
        $board_code=trim($board_code);
	    !$board_code && sadmin_show_msg('请输入系统编码',$p_url);
        !preg_match('/^[a-zA-Z0-9]+$/',$board_code) && sadmin_show_msg('系统编码只能由英文字符或数字组成',$p_url);
        $uid = (int)$uid;
        $rtl = $db->get_one("SELECT uid FROM `{$tablepre}badmin_table` WHERE board_name_code = '$board_code' AND `supplier_id`='$page_member_id' AND uid<>'$uid'");
        $rtl && sadmin_show_msg('编码重复',$p_url);
        $od=(int)$od;
        $row = array(
            'board_name_code' => $board_code,
            'board_title' => $board_title,
            'od'=>$od
        );
        $db->update("{$tablepre}badmin_table",$row,"uid='$uid' AND supplier_id='$page_member_id'");
        move_page(base64_decode($p_url));
    }
    $rt = $db->get_one("SELECT * FROM `{$tablepre}badmin_table` WHERE uid = '$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
    @extract($rt,EXTR_OVERWRITE);
	
	include template('sadmin_board_add');
	exit;
}
else if($action=='del')
{
	$rt_board = $db->get_one("SELECT uid,board_name_code FROM `{$tablepre}badmin_table` WHERE uid = '$uid' AND `supplier_id`='$page_member_id' LIMIT 1");
	if(!$rt_board) exit;

	$q=$db->query("SELECT cover FROM `{$tablepre}bmain` WHERE ps_name='$rtl_board[board_name_code]' AND supplier_id='$page_member_id'");
    while ($rtl=$db->fetch_array($q)) file_unlink($rtl['cover']);
    $db->free_result();
	
    $db->query("DELETE FROM `{$tablepre}bmain` WHERE ps_name = '$rt_board[board_name_code]' AND `supplier_id`='$page_member_id'");    //删除文章表
    $db->query("DELETE FROM `{$tablepre}badmin_table` WHERE uid = '$uid' AND `supplier_id`='$page_member_id'");    //删除公告牌
    exit;
}
else if($action=='article_list')
{
    $ps_name=dhtmlchars($ps_name);
    $board=$db->get_one("SELECT uid,board_name_code,board_title FROM `{$tablepre}badmin_table` WHERE supplier_id='$page_member_id' AND board_name_code='$ps_name' LIMIT 1");
    if(!$board) show_msg('检索不到指定的资讯版块');
    
    $arr_article=array();
    $search_sql=" WHERE supplier_id='$page_member_id' AND ps_name='$board[board_name_code]'";
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}bmain",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,author,board_subject,board_hit,register_date,supplier_id FROM `{$tablepre}bmain` 
                     $search_sql 
                     ORDER BY od DESC
                     LIMIT $from_record, $list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['url']=GetBaseUrl('article',$rtl['uid'],'id',$rtl['supplier_id']);
        $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
        $arr_article[]=$rtl;
    }
    
    $page_list = $rowset->link("sdmin.php?module=$module&action=$action&ps_name=$ps_name&page=");
    include template('sadmin_article');
}
else if($action=='article_add')
{
    if($_POST && (int)$step==1)
    {
        $board_subject=dhtmlchars($board_subject);
        $board_name_code=dhtmlchars($board_name_code);
        $is_top=(int)$is_top;
        if(!$board_subject) show_msg('请填写资讯名称');
        $board=$db->get_one("SELECT uid FROM `{$tablepre}badmin_table` WHERE supplier_id='$page_member_id' AND board_name_code='$board_name_code' LIMIT 1");
        if(!$board) show_msg('检索不到您指定的资讯版块');
        
        $cover='';
        if ($_FILES['cover']['name']!='')
        {
            require_once MVMMALL_ROOT.'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp',"union/images/news/$page_member_id/");
            $cover = $f->upload('cover');
            $cover=$cover.'@!web_news';
            $cover=str_replace('union/','',$cover);
        }
        
        $row=array(
            'ps_name'=>$board_name_code,
            'author'=>$m_check_id,
            'cover'=>$cover,
            'board_subject'=>$board_subject,
            'board_body'=>daddslashes($board_body),
            'register_date'=>$m_now_time,
            'supplier_id'=>$page_member_id,
            'od'=>$is_top?PHP_INT_MAX:$m_now_time
        );
        $db->insert("`{$tablepre}bmain`",$row);
        
        show_msg('发布成功',"sadmin.php?module=$module&action=article_list&ps_name=$board_name_code");
    }
    
    $arr_tmp=array();
    $q=$db->query("SELECT board_title,board_name_code FROM `{$tablepre}badmin_table` WHERE supplier_id='$page_member_id'");
    while ($rtl=$db->fetch_array($q)) $arr_tmp[$rtl['board_name_code']]=$rtl['board_title'];
    $sel_code=drop_menu($arr_tmp,'board_name_code',$ps_name);
    
    $article['cover']='images/noimages/noproduct.jpg';
    
    require_once template('sadmin_article_add');
    footer();
}
else if($action=='article_edit')
{
    $uid=(int)$uid;
    $article=$db->get_one("SELECT * FROM `{$tablepre}bmain` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
    if(!$article) show_msg('检索不到您指定的文章');
    
    if($_POST && (int)$step==1)
    {
        $board_subject=dhtmlchars($board_subject);
        $board_name_code=dhtmlchars($board_name_code);
        $is_top=(int)$is_top;
        if(!$board_subject) show_msg('请填写资讯名称');
        $board=$db->get_one("SELECT uid FROM `{$tablepre}badmin_table` WHERE supplier_id='$page_member_id' AND board_name_code='$board_name_code' LIMIT 1");
        if(!$board) show_msg('检索不到您指定的资讯版块');
        
        $cover=$article['cover'];
        if ($_FILES['cover']['name']!='')
        {
        	
        	$article['cover']=str_replace('@!web_news', '', $article['cover']);
            file_unlink('union/'.$article['cover'],'buctket');
            require_once MVMMALL_ROOT.'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp',"union/images/news/$page_member_id/");
            $cover = $f->upload('cover');
            $cover=$cover.'@!web_news';
            $cover=str_replace('union/','',$cover);
        }
        
        $row=array(
            'ps_name'=>$board_name_code,
            'author'=>$m_check_id,
            'cover'=>$cover,
            'board_subject'=>$board_subject,
            'board_body'=>daddslashes($board_body),
            'register_date'=>$m_now_time,
            'supplier_id'=>$page_member_id,
            'od'=>$is_top?PHP_INT_MAX:$m_now_time
        );
        $db->update("`{$tablepre}bmain`",$row," uid='$article[uid]' ");
        
        show_msg('修改成功',"sadmin.php?module=$module&action=article_list&ps_name=$board_name_code");
    }
    
    $article['od']==PHP_INT_MAX && $is_top_checked='checked';
    if(!$article['cover']) $article['cover']='images/noimages/noproduct.jpg';
    else $article['cover']=ProcImgPath($article['cover']);
    $article['board_body']=stripslashes($article['board_body']);
    
    $arr_tmp=array();
    $q=$db->query("SELECT board_title,board_name_code FROM `{$tablepre}badmin_table` WHERE supplier_id='$page_member_id'");
    while ($rtl=$db->fetch_array($q)) $arr_tmp[$rtl['board_name_code']]=$rtl['board_title'];
    $sel_code=drop_menu($arr_tmp,'board_name_code',$article['ps_name']);
    
    require_once template('sadmin_article_add');
    footer();
}
else if($action=='article_del')
{
    $uid=(int)$uid;
    $article=$db->get_one("SELECT cover FROM `{$tablepre}bmain` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
    if(!$article) exit;
    if($article['cover']) {
    	$article['cover']=str_replace('@!web_news', '', $article['cover']);
        file_unlink('union/'.$article['cover'],'buctket');
    }
    
    $db->query("DELETE FROM `{$tablepre}bmain` WHERE uid='$uid' AND supplier_id='$page_member_id'");
    exit;
}
