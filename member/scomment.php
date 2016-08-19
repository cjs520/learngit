<?php
require_once 'include/pager.class.php';
$roll      = 0;
$allow_uid = (int) $allow_uid;

if ($allow_uid > 0) {
    require_once 'include/order.class.php';
    $comment_allow = $db->get_one("SELECT uid,from_id,to_id,ordersn,roll FROM `{$tablepre}comment_allow`
                                 WHERE uid='$allow_uid' AND from_id='$m_check_id' AND roll='$roll' 
                                 LIMIT 1");
    if (!$comment_allow)
        show_msg('检索不到指定的可评论记录');
    $order_info = $db->get_one("SELECT uid,ordersn,supplier_id,mobile FROM `{$tablepre}order_info` WHERE ordersn='$comment_allow[ordersn]' AND username='$m_check_id' LIMIT 1");
    if (!$order_info)
        show_msg('检索不到指定订单');
    list($order_goods, $order_goods_num) = order::get_order_goods($order_info['uid']);
    
    if ($_POST && (int) $step == 1) {
        foreach ($og_comment as $key => $val) {
            if (!$order_goods[$key])
                continue;
            if (!$mm_comment_level[$val])
                continue;
            
            $row = array(
                'from_id' => $comment_allow['from_id'],
                'to_id' => $comment_allow['to_id'],
                'comment' => $og_detail[$key] ? strip_tags($og_detail[$key]) : $mm_comment_level[$key],
                'level' => $val,
                'g_uid' => $order_goods[$key]['g_uid'],
                'module' => $order_goods[$key]['module'],
                'goods_table' => $order_goods[$key]['goods_table'],
                'ip' => $m_user_ip,
                'reg_date' => $m_now_time
            );
            $db->insert("`{$tablepre}order_goods_comment`", $row);
        }
        
        $match          = (int) $match;
        $seller_service = (int) $seller_service;
        $seller_ship    = (int) $seller_ship;
        $ship_service   = (int) $ship_service;
        
        if ($match < 1 || $match > 5)
            $match = 5;
        if ($seller_service < 1 || $seller_service > 5)
            $seller_service = 5;
        if ($seller_ship < 1 || $seller_ship > 5)
            $seller_ship = 5;
        if ($ship_service < 1 || $ship_service > 5)
            $ship_service = 5;
        
        $row = array(
            'from_id' => $comment_allow['from_id'],
            'to_id' => $comment_allow['to_id'],
            'roll' => $comment_allow['roll'],
            'level' => 0,
            'comment' => "$match|$seller_service|$seller_ship|$ship_service",
            'reg_date' => $m_now_time
        );
        $db->insert("`{$tablepre}order_user_comment`", $row);
        $db->query("DELETE FROM `{$tablepre}comment_allow` WHERE uid='$comment_allow[uid]'");
        
       
        	 //评价短信发送
        require_once 'include/user_cache.class.php';
        $ucache = new ucache($db, $tablepre);
        $ucfg   = $ucache->get_cache('cfg', $order_info['supplier_id']);
        if ($ucfg['mm_sms_comment'] == 1 && $order_info['mobile']!='') {
            $send_message = str_replace('{ordersn}', $order_info['ordersn'], $ucfg['mm_sms_message4']);
            supplier_sms_send($order_info['mobile'], $send_message, $order_info['supplier_id']);
        }
        //@end
        
        show_msg('评论成功', "member.php?action=order");
    }
    
    require 'header.php';
    require_once template('member_scomment_detail');
} else {
    $rtl         = $db->get_one("SELECT COUNT(*) AS cnt
                       FROM `{$tablepre}comment_allow` 
                       WHERE `from_id`='$m_check_id' AND roll='$roll'
                       ORDER BY uid DESC");
    $total_count = (int) $rtl['cnt'];
    $page        = (int) $page > 0 ? (int) $page : 1;
    $list_num    = 10;
    $rowset      = new Pager($total_count, $list_num, $page);
    $from_record = $rowset->_offset();
    
    $q            = $db->query("SELECT uid,roll,ordersn,shop_name,to_id,supplier_id
                   FROM `{$tablepre}comment_allow` 
                   WHERE `from_id`='$m_check_id' AND roll='$roll' 
                   ORDER BY uid DESC 
                   LIMIT $from_record,$list_num");
    $comment_list = array();
    while ($rtl = $db->fetch_array($q)) {
        $rtl['shop_url'] = GetBaseUrl('index', '', '', $rtl['supplier_id']);
        $comment_list[]  = $rtl;
    }
    $page_list = $rowset->link("member.php?action=$action&page=");
    
    require 'header.php';
    require_once template('member_scomment');
}