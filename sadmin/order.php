<?php

/**
 * MVM_MALL 网上商店系统 商城订单处理文件
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-06-28 $
 * $Id: order.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if($action=='list' && $cmd=='')
{
	require_once 'include/pager.class.php';
    $search_sql = " WHERE supplier_id='$page_member_id'";
    
    $start_time && $search_sql.=" AND addtime>='".strtotime($start_time)."'";
    $end_time && $search_sql.= " AND addtime <= '".strtotime($end_time)."' ";
    $ordersn && $search_sql .= " AND ordersn = '".dhtmlchars($ordersn)."'";
    $consignee && $search_sql .= " AND consignee = '".dhtmlchars($consignee)."'";
    $status && $search_sql .= " AND status = '".dhtmlchars($status)."'";
    $username && $search_sql .= " AND username = '".dhtmlchars($username)."'";  
    $wait_for_ready=(int)$wait_for_ready;
    if($wait_for_ready==1)
    {
        $search_sql.=" AND goods_rest_amount>0 AND !(mark & 8)";
        $ready_checked='checked';
    }
    
    
    $arr_order=array();
    $total_count = $db->counter("{$tablepre}order_info", $search_sql);
    $page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,addtime,status,ordersn,username,sh_price,goods_amount,goods_point,discount,delivery_code,mark,goods_rest_amount,code 
                     FROM `{$tablepre}order_info`
                     $search_sql 
                     ORDER BY addtime DESC 
                     LIMIT $from_record, $list_num");
    
    while ($rtl = $db->fetch_array($q))
    {
        $rtl['addtime'] = date('Y-m-d',$rtl['addtime']);
        $rtl['status_code']=$rtl['status'];
        $rtl['status'] = $m_order_array[$rtl['status']];
        $rtl['goods_amount']=currency($rtl['goods_amount']);
        $rtl['goods_rest_amount_txt']=currency($rtl['goods_rest_amount']);
        $rtl['sh_price']=currency($rtl['sh_price']);
        $rtl['discount']=currency($rtl['discount']);
        
        if($rtl['goods_rest_amount']>0 && !($rtl['mark'] & 8))
        {
            if($rtl['status_code']==1) {
            	$rtl['status']='定金待付款';
            	$rtl['call']="<a href=\"#\" rel=\"call_mobile\" uid=\"$rtl[uid]\" title='手机短信提醒'><img src=\"$imgpath/sj.png\" /></a>";
            }
        }
        else if($rtl['goods_rest_amount']>0 && ($rtl['mark'] & 8))
        {
            $rtl['status']='余额待付款';
            $rtl['call']="<a href=\"#\" rel=\"call_mobile\" uid=\"$rtl[uid]\" title='手机短信提醒'><img src=\"$imgpath/sj.png\" /></a>";
        }
        
		$rtl['url'] = "sadmin.php?module=$module&action=edit&per=view&uid=$rtl[uid]";
    	$arr_order[] = $rtl;
    }
    $db->free_result();
    $order_menu = drop_menu($m_order_array,'status',$status);
    $page_list = $rowset->link("sadmin.php?module=order&action=list&start_time=$start_time&end_time=$end_time&ordersn=$ordersn&consignee=$consignee&status=$status&username=$username&wait_for_ready=$wait_for_ready&page=");
    
    include template('sadmin_order');
}
else if($action=='list' && $cmd=='delay')
{
    require_once 'include/pager.class.php';
    $search_sql = " WHERE supplier_id='$page_member_id'";
    $ordersn && $search_sql .= " AND ordersn = '".dhtmlchars($ordersn)."'";
    
    $arr_order=array();
    $total_count = $db->counter("{$tablepre}order_delay", $search_sql);
    $page = $page ? (int)$page:1;
	$list_num = 10;
	$rowset = new Pager($total_count,$list_num,$page);
	$from_record = $rowset->_offset();
    $q = $db->query("SELECT uid,ordersn,day,reason,approval_date FROM `{$tablepre}order_delay`
                     $search_sql 
                     ORDER BY register_date DESC 
                     LIMIT $from_record, $list_num");
    while ($rtl=$db->fetch_array($q))
    {
        $order_info=$db->get_one("SELECT uid,addtime,status,username,sh_price,goods_amount,goods_point,discount 
                                  FROM `{$tablepre}order_info` 
                                  WHERE ordersn='$rtl[ordersn]' 
                                  LIMIT 1");
        if(!$order_info) continue;
        $order_info['addtime']=date('Y-m-d H:i',$order_info['addtime']);
        $order_info['url']="sadmin.php?module=$module&action=edit&per=view&uid=$order_info[uid]";
        $rtl['status']=$rtl['approval_date']>10?'已审核':'未审核';
        $rtl=array_merge($order_info,$rtl);
        $arr_order[]=$rtl;
    }
    $db->free_result();
	$page_list = $rowset->link("sadmin.php?module=order&action=list&cmd=$cmd&ordersn=$ordersn&page=");
    
    include template('sadmin_order_delay');
}
else if($action=='list' && $cmd=='group_check')
{
    if($_POST && (int)$step==1)
    {
        $ordersn=dhtmlchars($ordersn);
        $code=dhtmlchars($code);
        if(!$ordersn) exit('ERR:验证失败，订单号未填写');
        if(!$code) exit('ERR:验证失败，订单密码未填写');
        $order_info=$db->get_one("SELECT uid,ordersn,code,status,mobile,supplier_id,goods_amount,goods_rest_amount,discount,goods_point,username 
                                  FROM `{$tablepre}order_info` 
                                  WHERE ordersn='$ordersn' AND supplier_id='$page_member_id' 
                                  LIMIT 1");
        if(!$order_info) exit('ERR:验证失败，检索不到指订的订单');
        if($order_info['code']!=$code) exit('ERR:验证失败，订单密码错误');
        if(!in_array($order_info['status'],array(1,3))) exit('ERR:验证失败，当前订单状态为'.$m_order_array[$order_info['status']].'，无法验证');
        
        require_once 'include/order.class.php';
        
        //dispatch
        $m=$db->get_one("SELECT uid,member_id,member_money,member_point FROM `{$tablepre}member_table` WHERE member_id='$order_info[username]' LIMIT 1");
        if(!$m) exit('ERR:检索不到指定订单用户'.$order_info['username']);
        $shop=$db->get_one("SELECT m_uid,m_id,xb_money,supplier_cat FROM `{$tablepre}member_shop` WHERE m_uid='$order_info[supplier_id]' LIMIT 1");
        if(!$shop) exit('ERR:检索不到订单商铺资料，请联系管理员');
        $cat=$db->get_one("SELECT rate FROM `{$tablepre}category` WHERE uid='$shop[supplier_cat]' LIMIT 1");
        if(!$cat) $cat=array('rate'=>0);
        $commission=0;
        if($cat['rate']>0 && $cat['rate']<1)    
        {
            $commission=($order_info['goods_amount']+$order_info['goods_rest_amount']-$order_info['discount'])*$cat['rate'];
            if($shop['xb_money']<$commission) exit('ERR:商铺消保金额不足以支付佣金，订单无法验证');
        }
        
        if($order_info['goods_point']>0 && $order_info['status']==1)
        {
            if($m['member_point']<$order_info['goods_point']) exit('ERR:会员积分不足，订单无法验证');
        }
        
        //从消保中扣除佣金
        add_xb($shop['m_uid'],-$commission,'订单验证',"验证订单$order_info[ordersn]",$order_info['ordersn']);
        add_score($m['uid'],-$order_info['goods_point'],'购物',"支付订单$order_info[ordersn]",$order_info['ordersn']);
        if($order_info['status']==3) add_money($shop['m_uid'],$order_info['goods_amount']-$order_info['discount'],"预付款","订单支付$order_info[ordersn]",$order_info['ordersn']);
        if($order_info['status']==1) add_score($m['uid'],intval($order_info['goods_amount']),'订单赠送积分','订单赠送积分',$order_info['ordersn']);
        
        $order_info['goods_amount']+=$order_info['goods_rest_amount'];
        $order_info['goods_rest_amount']=0;
        
        //order::dispatch($order_info['uid']);
        $db->query("UPDATE `{$tablepre}order_info` SET 
                    status='5',
                    mark=(mark | 8),
                    goods_amount='$order_info[goods_amount]',
                    goods_rest_amount='$order_info[goods_rest_amount]' 
                    WHERE uid='$order_info[uid]'");
        $db->query("UPDATE `{$tablepre}order_goods` SET status='1' WHERE order_id='$order_info[uid]'");
        $db->free_result();
        supplier_sms_send($order_info['mobile'],"您的订单{$order_info[ordersn]}已确认，祝您购物愉快",$order_info['supplier_id']);
        
        exit('OK:验证成功');
    }
    
    include template('sadmin_order_check');
}
else if($action=='edit')
{
	$uid = (int)$uid;
    $order_info = $db->get_one("SELECT * FROM `{$tablepre}order_info` WHERE uid = '$uid' AND supplier_id='$page_member_id' LIMIT 1");
	if(!$order_info) exit('检索不到指定的订单');
    $rtl=$db->get_one("SELECT cf_value FROM `{$tablepre}config` 
        	           WHERE cf_name='mm_cess' AND supplier_id='$order_info[supplier_id]' 
        	           LIMIT 1");
    
	if($_POST && (int)$step==1)
    {
        if($order_info['status']!=1) sadmin_show_msg('该订单已付款，您无权修改',$p_url);
        
        $db->update("`{$tablepre}order_info`",$order_row," uid='$order_info[uid]'");
        
        move_page(base64_decode($p_url));
    } 
    //显示编辑
    require_once 'include/order.class.php';
    $total_goods_num=0;
    $arr_goods=array();
    list($arr_goods,$total_goods_num)=order::get_order_goods($order_info['uid'],$page_member_id);

    $sh_price=$order_info['sh_price'];
    $discount=$order_info['discount'];
    $goods_amount=$order_info['goods_amount'];
    $order_info['goods_rest_amount']=currency($order_info['goods_rest_amount']);
    $order_info['order_amount']=currency($order_info['goods_amount']+$order_info['sh_price']-$order_info['discount']);
    $order_info['goods_amount']=currency($order_info['goods_amount']);
    $order_info['sh_price']=currency($order_info['sh_price']);
    $order_info['discount']=currency($order_info['discount']);
    
    if($per=='view')
    {
        $ship=$db->get_one("SELECT name FROM `{$tablepre}ship_table` WHERE uid='$order_info[sh_uid]' LIMIT 1");
        $order_info['sh_name']=$ship['name'];
    }
    else
    {
        $arr_ship=array();
        $q=$db->query("SELECT uid,name FROM `{$tablepre}ship_table` WHERE supplier_id='$page_member_id'");
        while ($rtl=$db->fetch_array($q)) $arr_ship[$rtl['uid']]=$rtl['name'];
        $db->free_result();
        $sel_ship=drop_menu($arr_ship,'order_row[sh_uid]',$order_info['sh_uid']);
        $sel_status=drop_menu($m_order_array,'status',$order_info['status']);
    }
    
    $per=='view' ? include template('sadmin_order_view') : include template('sadmin_order_add');
	footer(false);
}
else if($action=='delivery_code')
{
    require_once 'include/order.class.php';
    if($_POST && (int)$step==1)
    {
        $uid=(int)$uid;
        $v=trim(dhtmlchars($_POST['v']));
        if($uid<=0) exit('ERR:参数错误，请联系管理员');
        $order_info=$db->get_one("SELECT uid,status,delivery_code,username,ordersn,mobile,pay_id,goods_amount,sh_price,discount,goods_point,goods_rest_amount,supplier_id 
                                  FROM `{$tablepre}order_info` 
                                  WHERE uid='$uid' AND supplier_id='$page_member_id' 
                                  LIMIT 1");
        if(!$order_info) exit;
        if(!in_array($order_info['status'],array(1,3))) exit('ERR:订单状态无法发货');
        if($order_info['goods_rest_amount']>0) exit('ERR:订单尚有余额未结清，禁止发货');
        
        $payment=$db->get_one("SELECT class_name FROM `{$tablepre}payment_table` WHERE id='$order_info[pay_id]' LIMIT 1");
        if($payment['class_name']=='COD' || $order_info['status']==1)    //货到付款（或者所有未发货状态），扣除商家金额，否则无法发货
        {
            $total_price=$order_info['goods_amount']+$order_info['sh_price']-$order_info['discount'];
            if($mvm_member['member_money']<$total_price) exit('ERR:你的预存款余额不足，无法发货');
            if($mvm_member['member_point']<$order_info['goods_point']) exit('ERR:您的积分不足，无法发货');
            
            add_money($page_member_id,-$total_price,'购物',"订单$order_info[ordersn]发货",$order_info['ordersn']);
            add_score($page_member_id,-$order_info['goods_point'],'发货',"订单$order_info[ordersn]发货",$order_info['ordersn']);
        }
 
        $check_time=$m_now_time+intval($mm_order_confirm)*24*3600;
        $db->query("UPDATE `{$tablepre}order_info` SET 
                    delivery_code='$v',
                    status='4',
                    checktime='$check_time' 
                    WHERE uid='$order_info[uid]'");
       $db->query("UPDATE `{$tablepre}order_goods` SET status=1 WHERE order_id='$order_info[uid]'");
        $db->free_result();
        
        //发送站内短消息
        inner_sms_send($m_check_id,
    	               $order_info['username'],
    	               "{$order_info[ordersn]}订单已发货",
    	               "您的订单{$order_info['ordersn']}已成功发货，请注意查收与确认",
    	               0);
    

        //发送邮件收货提示
    	do
    	{
    	    if ((int)$mm_mail_delivery!=1) break;
    	    $m=$db->get_one("SELECT member_email FROM `{$tablepre}member_table` WHERE member_id='$order_info[username]' LIMIT 1");
    	    if(!$m) break;
    	    if(!$m['member_email']) break;
    	    
         	smtp_mail($m['member_email'],
		        str_replace('{ordersn}',$order_info['ordersn'],"订单{ordersn}已成功发货"),
		        str_replace(array('{mall_title}','{ordersn}'),array($mm_mall_title,$order_info['ordersn']),$mm_mail_delivery_cnt)
		    );
    	}while (0);
    	//@end
    	
    	//商品发货完成发送短信   
        if((int)$ucfg['mm_sms_use'] == 1 && (int)$ucfg['mm_sms_delivery'] == 1 && $order_info['mobile']!='')
           {   
           	$send_message = str_replace('{ordersn}',$order_info['ordersn'],$ucfg['mm_sms_message3']);
            supplier_sms_send($order_info['mobile'], $send_message,$order_info['supplier_id']); 
           }
    }
    exit('OK:设置成功');
}
else if($action=='delay')
{
    $uid=(int)$uid;
    $order_delay=$db->get_one("SELECT day,ordersn,approval_date FROM `{$tablepre}order_delay` WHERE uid='$uid' AND supplier_id='$page_member_id' LIMIT 1");
    if(!$order_delay) exit('ERR:检索不到指定的延迟申请');
    if($order_delay['approval_date']>10) exit('ERR:延迟申请已审核，无法重复审核');
    
    $delay_time=(int)$order_delay['day']*24*3600;
    $db->query("UPDATE `{$tablepre}order_info` SET checktime=checktime+'$delay_time' WHERE ordersn='$order_delay[ordersn]'");
    $db->query("UPDATE `{$tablepre}order_delay` SET approval_date='$m_now_time' WHERE uid='$uid'");
    $db->free_result();
    exit('OK:延迟收货审核成功');
}
else if($action=='ready')
{

    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,username,ordersn,goods_amount,goods_rest_amount,mark,mobile 
                       FROM `{$tablepre}order_info` 
                       WHERE uid='$uid' AND supplier_id='$page_member_id' 
                       LIMIT 1");
    if(!$rtl) exit('ERR:检索不到指定的订单');
    if($rtl['goods_rest_amount']<=0) exit('ERR:指定的订单不是预定订单，无需事先备货');
    if($rtl['mark'] & 8) exit('ERR:指定的订单已备货，无需重新备货');
    $db->query("UPDATE `{$tablepre}order_info` SET mark=(mark | 8) WHERE uid='$uid'");
    $db->free_result();
    
    $mm_order_preorder=(int)$mm_order_preorder;
    if($mm_order_preorder<=0) $mm_order_preorder=10;
    $expire=$m_now_time+$mm_order_preorder*24*3600;
    $db->query("INSERT INTO `{$tablepre}task_list` (task_name,param,expire) VALUES ('preorder','$rtl[uid]','$expire')");
    $db->free_result();
     
    $info="您的订单：$rtl[ordersn],商家备货已完成，请及时付清余款。如{$mm_order_preorder}天之内您尚未付清余款，程序将定金补偿给商家。";
    if($ucfg['mm_sms_order_stock_up_shop']==1 && $rtl['mobile']!=''){
    	supplier_sms_send($rtl['mobile'],$info,$page_member_id);
    }
    inner_sms_send($m_check_id,$rtl['username'],'订单已备货',$info);
    
    exit('OK:备货成功');
}

else if($action=='call_mobile')
{

    $uid=(int)$uid;
    $rtl=$db->get_one("SELECT uid,username,status,ordersn,goods_amount,goods_rest_amount,mark,mobile 
                       FROM `{$tablepre}order_info` 
                       WHERE uid='$uid' AND supplier_id='$page_member_id' 
                       LIMIT 1");
    if(!$rtl) exit('ERR:检索不到指定的订单');
    if($rtl['status']>3) exit('ERR:该订单已经支付完成了！');
    
     
    if($ucfg['mm_sms_order']==1 && $rtl['mobile']!=''){
    	$info = str_replace('{ordersn}',$rtl['ordersn'],$ucfg['mm_sms_message1']);
    	supplier_sms_send($rtl['mobile'],$info,$page_member_id);
    }else {
    	exit('OK:您没开通短信功能提醒功能或者您的手机号码没填写完整！');
    }
    
    exit('OK:提醒成功！');
}