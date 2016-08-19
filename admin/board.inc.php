<?php

/**
 * MVM_MALL 网上商店系统  公告牌管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-15 $
 * $Id: board.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/
if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
require_once 'include/pic.class.php';

if ($action=='list')
{
    $q = $db->query("SELECT board_name_code,uid,board_title,od 
                     FROM `{$tablepre}badmin_table` 
                     WHERE `supplier_id`='0' 
                     ORDER BY od DESC");
    while ($rtl = $db->fetch_array($q)) $board_rt[] = $rtl;
    $db->free_result();
    
    require_once template('board');
    footer();
}
else if ($action=='add')
{
    if($_POST && (int)$step==1)
    {
        !$board_code && show_msg('existence_code');
        if(!preg_match('/^[0-9a-zA-Z_]+$/',$board_code)) show_msg('系统编码只能是数字和字母组合');
        $board_code = dhtmlchars($board_code);
        $rtl = $db->get_one("SELECT uid FROM `{$tablepre}badmin_table` WHERE board_name_code = '$board_code' AND supplier_id=0 LIMIT 1");
        if($rtl) show_msg('指定的系统统码已经使用过，请更换一个');
        $db->query("INSERT INTO `{$tablepre}badmin_table` SET
                    board_name_code = '$board_code',
                    board_title = '$board_name',
                    register_date = '$m_now_time',
                    supplier_id = '0'");
        $db->free_result();
        
        admin_log("添加资讯系统：$board_code-$board_name");
    }
    
    show_msg('添加成功',"admincp.php?module=$module&action=list");
}
else if ($action=='edit')
{
    $uid=(int)$uid;
    if($_POST && (int)$step==1)
    {
        if(!$board_code) sadmin_show_msg('系统编码未填写',$p_url);
        if(!preg_match('/^[0-9a-zA-Z_]+$/',$board_code)) sadmin_show_msg('系统编码只能是数字和字母组合',$p_url);
        $board_code = dhtmlchars($board_code);
        $rtl = $db->get_one("SELECT uid FROM `{$tablepre}badmin_table` WHERE board_name_code = '$board_code' AND supplier_id=0 AND uid<>$uid LIMIT 1");
        if($rtl) sadmin_show_msg('指定的系统统码已经使用过，请更换一个',$p_url);
        $row = array(
            'board_name_code' => $board_code,
            'board_title' => $board_title,
            'od'=>(int)$od
        );
        $db->update("{$tablepre}badmin_table",$row,"uid='$uid'");
        $db->free_result();
        admin_log("修改资讯系统：$board_code-$board_title");
        
        move_page(base64_decode($p_url));
    }
    $rtl = $db->get_one("SELECT * FROM `{$tablepre}badmin_table` WHERE uid = $uid LIMIT 1");
    @extract($rtl,EXTR_OVERWRITE);
    require_once template('board_add');
    exit;
}
else if($action=='ajax')
{
	$uid=(int)$uid;
	$od=(int)$od;
	$db->query("UPDATE `{$tablepre}badmin_table` SET od='$od' WHERE uid=$uid");
	exit;
}
else if ($action=='del')
{
    $uid=(int)$uid;
    $rtl_board = $db->get_one("SELECT uid,board_name_code,board_title FROM `{$tablepre}badmin_table` WHERE uid=$uid AND supplier_id=0 LIMIT 1");
    if(!$rtl_board) exit;
    admin_log("删除资讯系统：$rtl_board[board_name_code]-$rtl_board[board_title]");
    
    $q=$db->query("SELECT cover FROM `{$tablepre}bmain` WHERE ps_name='$rtl_board[board_name_code]' AND supplier_id=0");
    while ($rtl=$db->fetch_array($q)) file_unlink($rtl['cover']);
    $db->free_result();
    
    $db->query("DELETE FROM `{$tablepre}bmain` WHERE ps_name = '$rtl_board[board_name_code]' AND supplier_id=0");    //删除文章表
    $db->query("DELETE FROM `{$tablepre}badmin_table` WHERE uid = $uid AND supplier_id=0");    //删除公告牌
    $db->free_result();
    exit;
}
else if($action=='article_list')
{
    $ps_name=dhtmlchars($ps_name);
    $board=$db->get_one("SELECT uid,board_name_code,board_title FROM `{$tablepre}badmin_table` WHERE supplier_id=0 AND board_name_code='$ps_name' LIMIT 1");
    if(!$board) show_msg('检索不到指定的资讯版块');
    
    $arr_article=array();
    $search_sql=" WHERE supplier_id=0 AND ps_name='$board[board_name_code]'";
    require_once 'include/pager.class.php';
    $total_count = $db->counter("{$tablepre}bmain",$search_sql);
    $page = $page ? (int)$page:1;
    $list_num = 10;
    $rowset = new Pager($total_count,$list_num,$page);
    $from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,author,board_subject,board_hit,register_date FROM `{$tablepre}bmain` 
                     $search_sql 
                     ORDER BY od DESC
                     LIMIT $from_record, $list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $rtl['register_date']=date('Y-m-d',$rtl['register_date']);
        $arr_article[]=$rtl;
    }
    $db->free_result();
    $page_list = $rowset->link("admincp.php?module=$module&action=$action&ps_name=$ps_name&page=");
    require_once template('article');
    footer();
}
else if($action=='article_add')
{
    if($_POST && (int)$step==1)
    {
        $board_subject=dhtmlchars($board_subject);
        $board_name_code=dhtmlchars($board_name_code);
        $is_top=(int)$is_top;
        if(!$board_subject) show_msg('请填写资讯名称');
        $board=$db->get_one("SELECT uid FROM `{$tablepre}badmin_table` WHERE supplier_id=0 AND board_name_code='$board_name_code' LIMIT 1");
        if(!$board) show_msg('检索不到您指定的资讯版块');
        
        $cover='';
        if ($_FILES['cover']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp',"images/news/");
            $cover = $f->upload('cover');
            $cover=$cover.'@!web_article';
        }
        
        $row=array(
            'ps_name'=>$board_name_code,
            'author'=>$m_check_id,
            'cover'=>$cover,
            'board_subject'=>$board_subject,
            'board_body'=>$board_body,
            'register_date'=>$m_now_time,
            'supplier_id'=>0,
            'od'=>$is_top?PHP_INT_MAX:$m_now_time
        );
        $db->insert("`{$tablepre}bmain`",$row);
        admin_log("添加资讯：$board_subject");
        
        show_msg('发布成功',"admincp.php?module=$module&action=article_list&ps_name=$board_name_code");
    }
    
    $arr_tmp=array();
    $q=$db->query("SELECT board_title,board_name_code FROM `{$tablepre}badmin_table` WHERE supplier_id=0");
    while ($rtl=$db->fetch_array($q)) $arr_tmp[$rtl['board_name_code']]=$rtl['board_title'];
    $sel_code=drop_menu($arr_tmp,'board_name_code',$ps_name);
    
    $article['cover']='images/noimages/noproduct.jpg';
    
    require_once template('article_add');
    footer();
}
else if($action=='article_edit')
{
    $uid=(int)$uid;
    $article=$db->get_one("SELECT * FROM `{$tablepre}bmain` WHERE uid='$uid' AND supplier_id=0 LIMIT 1");
    if(!$article) show_msg('检索不到您指定的文章');
    
    if($_POST && (int)$step==1)
    {
        $board_subject=dhtmlchars($board_subject);
        $board_name_code=dhtmlchars($board_name_code);
        $is_top=(int)$is_top;
        if(!$board_subject) show_msg('请填写资讯名称');
        $board=$db->get_one("SELECT uid FROM `{$tablepre}badmin_table` WHERE supplier_id=0 AND board_name_code='$board_name_code' LIMIT 1");
        if(!$board) show_msg('检索不到您指定的资讯版块');
        
        $cover=$article['cover'];
        if ($_FILES['cover']['name']!='')
        {
            file_unlink(str_replace('@!web_article', '', $cover),'buctket');
            require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp',"images/news/");
            $cover = $f->upload('cover');
            $cover=$cover.'@!web_article';
        }
        
        $row=array(
            'ps_name'=>$board_name_code,
            'author'=>$m_check_id,
            'cover'=>$cover,
            'board_subject'=>$board_subject,
            'board_body'=>$board_body,
            'supplier_id'=>0,
            'od'=>$is_top?PHP_INT_MAX:$m_now_time
        );
        $db->update("`{$tablepre}bmain`",$row," uid='$article[uid]' ");
        $db->free_result();
        admin_log("修改资讯：$board_subject");
        
        show_msg('修改成功',"admincp.php?module=$module&action=article_list&ps_name=$board_name_code");
    }
    $article['cover']=IMG_URL.$article['cover'];
    if(!@fopen($article['cover'],'r') || !$article['cover']) $article['cover']='images/noimages/noproduct.jpg';
    if($article['od']==PHP_INT_MAX) $is_top_checked='checked';
    
    $arr_tmp=array();
    $q=$db->query("SELECT board_title,board_name_code FROM `{$tablepre}badmin_table` WHERE supplier_id=0");
    while ($rtl=$db->fetch_array($q)) $arr_tmp[$rtl['board_name_code']]=$rtl['board_title'];
    $sel_code=drop_menu($arr_tmp,'board_name_code',$article['ps_name']);
    
    require_once template('article_add');
    footer();
}
else if($action=='article_del')
{
    $uid=(int)$uid;
    $article=$db->get_one("SELECT cover,board_subject FROM `{$tablepre}bmain` WHERE uid='$uid' AND supplier_id=0 LIMIT 1");
    if(!$article) exit;
    file_unlink(str_replace('@!web_article', '', $article['cover']),'buctket');
    admin_log("删除资讯：$article[board_subject]");
    
    $db->query("DELETE FROM `{$tablepre}bmain` WHERE uid='$uid' AND supplier_id=0");
    exit;
}
else show_msg('pass_worng');

