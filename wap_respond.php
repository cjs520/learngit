<?php

/**
 * MVM_MALL 网上商店系统 支付处理结果
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-01 $
 * $Id: wap_respond.php  www.mvmmall.cn$
 * ---------------------------------------------
 */

require_once 'include/common.inc.php';

$is_mobile = true;
$code      = dhtmlchars($_GET['code']);
if (!$_POST && !$GLOBALS['HTTP_RAW_POST_DATA'])
    move_page('member.php?action=order');

$pay = $db->get_one("SELECT class_name,cfg FROM `{$tablepre}payment_table` WHERE class_name='$code' LIMIT 1");
if (!$pay)
    show_msg('pass_worng', 'close');
else
    require_once 'include/payment/' . $pay['class_name'] . '.class.php';
$rowst = new $pay['class_name'](unserialize($pay['cfg']));
$rowst->pay_receive();
show_msg('支付成功，祝您购物愉快', 'member.php?action=index');


function order_prepare($sn) //检测是否已经支付过
{
    global $db, $tablepre, $m_now_time, $m_check_id, $m_check_uid, $mvm_member;
    if (!$m_check_uid)
        show_msg('您还未登录，请先登录', GetBaseUrl('logging', 'login'));
    
    $ms_flag = strtolower(substr($sn, 0, 2));
    switch ($ms_flag) {
        case 'up': //商铺升级订单
            $order = $db->get_one("SELECT approval_date FROM `{$tablepre}update_table` WHERE ordersn='$sn' LIMIT 1");
            if (!$order)
                show_msg('检索不到指定的订单');
            if ($order['approval_date'] > 10)
                show_msg('指定的升级记录已支付', "sadmin.php?module=shop_update&action=list");
            break;
        case 'od': //普通购物订单
        case 'au': //拍卖订单
            $order = $db->get_one("SELECT status,goods_rest_amount FROM `{$tablepre}order_info` WHERE ordersn='$sn' LIMIT 1");
            if (!$order)
                show_msg('检索不到指定的订单', './');
            if ($order['status'] != 1 && $order['goods_rest_amount'] <= 0)
                show_msg('指定的订单已付款', 'member.php?action=index');
            break;
        case 'ct': //普通购物订单组合购买
            $order = $db->get_one("SELECT approval_date FROM `{$tablepre}order_combine` WHERE tag='$sn' LIMIT 1");
            if (!$order)
                show_msg('检索不到指定的订单', './');
            if ($order['approval_date'] > 10)
                show_msg('指定的订单已付款', 'member.php?action=index');
            break;
    }
    
} //end order_prepare

function change_order($sn)
{
    global $db, $tablepre, $m_now_time, $m_check_id, $m_check_uid, $mvm_member, $code;
    
    $ms_flag = strtolower(substr($sn, 0, 2));
    
    if ($ms_flag == 'up') //商铺升级
        {
        global $lang;
        $order = $db->get_one("SELECT uid,amount,approval_date,shop_level,supplier_id FROM `{$tablepre}update_table` WHERE ordersn='$sn' LIMIT 1");
        
        $m    = cur_member_info($m_check_uid);
        $shop = $db->get_one("SELECT shop_name FROM `{$tablepre}member_shop` WHERE m_uid='$m[uid]' LIMIT 1");
        if ($m['member_money'] < $order['amount'])
            show_msg('您的预付款余额不足，请充值');
        add_money($m_check_uid, -$order['amount'], '商铺升级', '商铺升级', $sn);
        $db->query("UPDATE `{$tablepre}member_shop` SET shop_level='$order[shop_level]' WHERE m_uid='$order[supplier_id]'");
        $db->query("UPDATE `{$tablepre}update_table` SET approval_date='$m_now_time'");
        $db->free_result();
        
        if ((int) $GLOBALS['mm_mail_shop_update'] == 1 && $m['member_email']) {
            smtp_mail($m['member_email'], str_replace(array(
                '{shop_name}'
            ), array(
                $shop['shop_name']
            ), '您的商铺{shop_name}升级成功'), str_replace(array(
                '{shop_name}',
                '{shop_level}'
            ), array(
                $shop['shop_name'],
                $lang['shop_level'][$order['shop_level']]
            ), '您的商铺{shop_name}已经升级为{shop_level}，恭喜'));
        }
        
        show_msg('商铺升级成功', 'sadmin.php?module=shop_update&action=list');
        return;
    }
    if ($ms_flag == 'rn') //商铺续费
        {
        $pay_log = $db->get_one("SELECT total_money,other_info FROM `{$tablepre}pay_log` WHERE tag='$sn' LIMIT 1");
        if (!$pay_log)
            show_msg('检索不到指定的支付记录');
        $db->query("DELETE FROM `{$tablepre}pay_log` WHERE tag='$sn'");
        
        $m = cur_member_info($m_check_uid);
        if ($m['member_money'] < $pay_log['total_money'])
            show_msg('您的预付款余额不足，请充值');
        
        $arr_info = explode('|', $pay_log['other_info']);
        if (sizeof($arr_info) != 3)
            show_msg('信息错误');
        $shop_level  = (int) $arr_info[1];
        $year        = (int) $arr_info[0];
        $supplier_id = (int) $arr_info[2];
        if ($year <= 0)
            show_msg('续费年限错误', 'sadmin.php?module=shop_renew&action=list');
        if ($shop_level <= 0 || $shop_level > 4)
            show_msg('续费等级错误', 'sadmin.php?module=shop_renew&action=list');
        $shop = $db->get_one("SELECT shop_level,shop_expire FROM `{$tablepre}member_shop` WHERE m_uid='$supplier_id' LIMIT 1");
        if (!$shop)
            show_msg('检索不到指定的商铺', 'sadmin.php?module=shop_renew&action=list');
        
        add_money($m_check_uid, -$pay_log['total_money'], '商铺续费', '商铺续费', $sn);
        $expire = $shop['shop_level'] > 0 ? $shop['shop_expire'] + $year * 365 * 24 * 3600 : $m_now_time + $year * 365 * 24 * 3600;
        $db->query("UPDATE `{$tablepre}member_shop` SET 
                    shop_level='$shop_level',
                    shop_expire='$expire' 
                    WHERE m_uid='$supplier_id'");
        $db->free_result();
        show_msg('商铺续费成功', 'sadmin.php?module=shop_renew&action=list');
        return;
    }
    if ($ms_flag == 'pm') //预付款充值
        {
        show_msg('成功充值', 'account.php?action=money');
        return;
    }
    if ($ms_flag == 'xb') //申请消保
        {
        $total_money = 0;
        $pay_log     = $db->get_one("SELECT total_money FROM `{$tablepre}pay_log` WHERE tag='$sn' LIMIT 1");
        if (!$pay_log)
            show_msg('检索不到指定的支付记录');
        $total_money = floatval($pay_log['total_money']);
        $db->query("DELETE FROM `{$tablepre}pay_log` WHERE tag='$sn'");
        $db->free_result();
        
        $m = cur_member_info($m_check_uid);
        if ($m['member_money'] < $total_money)
            show_msg('您的余额不足以支付消保申请');
        add_money($m_check_uid, -$total_money, '消保申请', "支付消保申请$sn", $sn);
        add_xb($m_check_uid, $total_money, '消保充值', "消保充值$sn", $sn);
        
        $GLOBALS['cache']->delete('cfg', $m_check_uid);
        show_msg('消保金额支付成功', 'sadmin.php?module=sup_check&action=list');
        return;
    }
    if ($ms_flag == 'bp') //积分购买
        {
        global $mm_buy_point;
        $mm_buy_point = (int) $mm_buy_point;
        $pay_log      = $db->get_one("SELECT total_money FROM `{$tablepre}pay_log` WHERE tag='$sn' LIMIT 1");
        if (!$pay_log)
            show_msg('检索不到指定的支付记录');
        $db->query("DELETE FROM `{$tablepre}pay_log` WHERE tag='$sn'");
        
        $point_nums = $mm_buy_point * $pay_log['total_money'];
        $m          = cur_member_info($m_check_uid);
        if ($m['member_money'] < $pay_log['total_money'])
            show_msg('您的预付款账户余额不足', 'account.php?action=money');
        add_money($m_check_uid, -$pay_log['total_money'], '预付款', "购买{$point_nums}积分", $sn);
        add_score($m_check_uid, $point_nums, '积分购买', "购买{$point_nums}积分", $sn);
        show_msg('积分购买成功', 'account.php?action=point');
        return;
    }
    if ($ms_flag == 'tp') //模板购买
        {
        $pay_log = $db->get_one("SELECT total_money,other_info FROM `{$tablepre}pay_log` WHERE tag='$sn' LIMIT 1");
        if (!$pay_log)
            show_msg('检索不到指定的支付记录');
        if (!$pay_log['other_info'])
            show_msg('支付模板信息出错，请联系管理员');
        $db->query("DELETE FROM `{$tablepre}pay_log` WHERE tag='$sn'");
        
        add_money($m_check_uid, -$pay_log['total_money'], '购买模板', '购买模板', $sn);
        
        $shop      = $db->get_one("SELECT allow_tpl FROM `{$tablepre}member_shop` WHERE m_uid='$m_check_uid' LIMIT 1");
        $arr_tmp   = explode('|', $shop['allow_tpl']);
        $arr_tmp[] = $pay_log['other_info'];
        foreach ($arr_tmp as $key => $val) {
            $val = trim($val);
            if (!$val)
                unset($arr_tmp[$key]);
        }
        $arr_tmp = array_unique($arr_tmp);
        $str_tmp = implode('|', $arr_tmp);
        $db->query("UPDATE `{$tablepre}member_shop` SET allow_tpl='$str_tmp' WHERE m_uid='$m_check_uid'");
        show_msg('购买模板成功,您现在可以去装修商铺', 'sadmin.php?module=tpl&action=list');
        return;
    } else if (in_array($ms_flag, array(
            'od',
            'ct',
            'au'
        ))) {
        global $mm_buy_point;
        /*************** 处理普通订单 ****************/
        $arr_orders = array();
        if ($ms_flag == 'od' || $ms_flag == 'au') {
            $order_info = $db->get_one("SELECT uid,ordersn,status,goods_amount,goods_rest_amount,sh_price,discount,goods_point,mobile,supplier_id 
                                      FROM `{$tablepre}order_info` WHERE ordersn='$sn' 
                                      LIMIT 1");
            if ($order_info)
                $arr_orders[] = $order_info;
        } else {
            $q = $db->query("SELECT ordersn FROM `{$tablepre}order_combine` WHERE tag='$sn'");
            while ($rtl = $db->fetch_array($q)) {
                $order_info = $db->get_one("SELECT uid,ordersn,status,goods_amount,goods_rest_amount,sh_price,discount,goods_point,mobile,supplier_id 
                                          FROM `{$tablepre}order_info` WHERE ordersn='$rtl[ordersn]' 
                                          LIMIT 1");
                if ($order_info)
                    $arr_orders[] = $order_info;
            }
            $db->free_result();
        }
        
        if (!$arr_orders)
            show_msg('检索不到指定的订单数据', './');
        $total_point = 0;
        $total_money = 0;
        $send_point  = 0;
        $other_info  = '';
        foreach ($arr_orders as $val) {
            if ($val['status'] == 3 && $val['goods_rest_amount'] > 0) //支付余额
                {
                $total_money = $val['goods_rest_amount'];
            } else {
                $total_point += (int) $val['goods_point'];
                $total_money += $val['goods_amount'] + $val['sh_price'] - $val['discount'];
                $send_point += intval($val['goods_amount'] - $val['discount']);
                $other_info .= ",$val[ordersn]";
                $db->query("UPDATE `{$tablepre}order_goods` SET status=1 WHERE order_id='$val[uid]'");
            }
        }
        $db->free_result();
        
        $m = cur_member_info($m_check_uid);
        if ($m['member_point'] < $total_point) {
            $total_money += ($total_point - $m['member_point']) / intval($mm_buy_point);
            $total_point = $m['member_point'];
        }
        if ($m['member_money'] < $total_money)
            show_msg('您的余额不足，请重新支付', 'member.php?action=order');
        
        if ($ms_flag == 'od')
            $other_info = "订单号：$sn";
        else
            $other_info = '订单号：' . substr($other_info, 1);
        add_score($m_check_uid, -$total_point, '购物', "支付订单", $sn, $other_info);
        add_money($m_check_uid, -$total_money, '购物', "支付订单", $sn, $other_info);
        //送积分
        add_score($m_check_uid, $send_point, '购物赠送', "订单赠送积分", $sn, $other_info);
        
        foreach ($arr_orders as $val) {
            if ($val['status'] == 3 && $val['goods_rest_amount'] > 0) //支付余款
                {
                $db->query("UPDATE `{$tablepre}order_info` SET 
                            goods_amount=goods_amount+goods_rest_amount,
                            goods_rest_amount=0 
                            WHERE uid='$val[uid]'");
                $db->query("UPDATE `{$tablepre}order_goods` SET 
                            buy_price=buy_price+rest_price,
                            rest_price=0,
                            status=1 
                            WHERE order_id='$val[uid]'");
                $db->free_result();
            } else {
                $order_row = array(
                    'status' => 3
                );
                if (!$code) {
                    $order_row['pay_id']   = 0;
                    $order_row['pay_name'] = '预付款';
                }
                $db->update("`{$tablepre}order_info`", $order_row, " uid='$val[uid]' ");
                $db->free_result();
            }
            
        }
        if ($ms_flag == 'ct')
            $db->query("UPDATE `{$tablepre}order_combine` SET approval_date='$m_now_time' WHERE tag='$sn'");
        $db->free_result();
        
        //已付款阶段发送短信
        require_once 'include/order.class.php';
        require_once 'include/user_cache.class.php';
        $ucache = new ucache($db, $tablepre);
        
        $arr_supplier_id = array();
        $arr_ordersn     = array();
        foreach ($arr_orders as $val) {
            //扣库存
            $q = $db->query("SELECT uid FROM `{$tablepre}order_goods` WHERE order_id='$val[uid]'");
            while ($rtl = $db->fetch_array($q))
                order::change_stock($rtl['uid']);
            $db->free_result();
            
            $arr_ordersn[] = $val['ordersn'];
            if (in_array($val['supplier_id'], $arr_supplier_id))
                continue;
            $arr_supplier_id[] = $val['supplier_id'];
            $ucfg = $ucache->get_cache('cfg', $val['supplier_id']);
            
           //发送给店主
	        if((int)$ucfg['mm_sms_order_pay_to_shop']==1 && $ucfg['mm_mobile']!='')
	        {
	            supplier_sms_send($ucfg['mm_mobile'],"你有订单$val[ordersn]已成功支付，请尽快发货",$val['supplier_id']);
	        }
            
            
            //判断是否团购或预定订单
            $rtl = $db->get_one("SELECT uid,goods_name FROM `{$tablepre}order_goods` WHERE order_id='$val[uid]' AND g_type IN(5,9) LIMIT 1");
            if ($rtl) {
                $rtl = $db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$val[supplier_id]' AND cf_name='mm_sms_group_order'");
                if ((int) $rtl['cf_value'] != 1)
                    continue;
                
                $order_code = rand(100000, 999999);
                $db->query("UPDATE `{$tablepre}order_info` SET code='$order_code' WHERE uid='$val[uid]'");
                supplier_sms_send($val['mobile'], "您的商品$rtl[goods_name], 订单号：$val[ordersn], 订单密码：{$order_code}, 请妥善保管", $val['supplier_id']);
            } else {
                //发送短信
                if (((int) $ucfg['mm_sms_receipt'] != 1 || !$val['mobile']))
                    continue;
                $send_message = str_replace(array(
                    '{ordersn}',
                    '{orderpass}'
                ), array(
                    $val['ordersn'],
                    $val['code']
                ), $ucfg['mm_sms_message2']);
                supplier_sms_send($val['mobile'], $send_message, $val['supplier_id']);
            }
        }
        
        do //发邮件
            {
            if ((int) $GLOBALS['mm_mail_receipt'] != 1)
                break;
            if (!$mvm_member['member_email'])
                break;
            if (!$arr_ordersn)
                break;
            
            $ordersn = implode(',', $arr_ordersn);
            smtp_mail($mvm_member['member_email'], str_replace('{ordersn}', $ordersn, "订单{ordersn}已付款成功"), str_replace(array(
                '{mall_title}',
                '{ordersn}'
            ), array(
                $GLOBALS['mm_mall_title'],
                $ordersn
            ), $GLOBALS['mm_mail_receipt_cnt']));
        } while (0);
        
        show_msg('支付成功，祝您购物愉快', 'member.php?action=order');
    } else
        show_msg('订单类型错误，请联系管理员');
}