<?php

/**
 * MVM_MALL 网上商店系统  购物车
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-02 $
 * $Id: order.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

class order
{
    public $form_code='';
	private $err;
	private $cart;
	private $payment;
	private $o_payment;
	
	private $province,$city,$county,$address,$consignee,$mobile,$zipcode;
	private $cart_uid,$memo,$invoice,$ship_uid,$ship_price,$coupon_uid;
	private $advance,$pay_pass,$pay_id;
	
    public function __construct()
	{
	    require_once 'include/cart.class.php';
	    $this->cart=new cart();
	}//end construct
	
	public function set_address_info($province,$city,$county,$address,$consignee,$mobile,$zipcode)
	{
	    $this->province=$province;
	    $this->city=$city;
	    $this->county=$county;
	    $this->address=$address;
	    $this->consignee=$consignee;
	    $this->mobile=$mobile;
	    $this->zipcode=$zipcode;
	}//end set_address_info
	
	public function set_cart_info($cart_uid,$memo,$invoice,$ship_uid,$ship_price,$coupon_uid)
	{
	    $this->cart_uid=$cart_uid;
	    $this->memo=$memo;
	    $this->invoice=$invoice;
	    $this->ship_uid=$ship_uid;
	    $this->ship_price=$ship_price;
	    $this->coupon_uid=$coupon_uid;
	}//end set_cart_info
	
	public function set_payment_info($advance,$pay_pass,$pay_id)
	{
	    $this->advance=(int)$advance;
	    $this->pay_pass=$pay_pass;
	    $this->pay_id=(int)$pay_id;
	}//end set_payment_info
	
	private function check_info()
	{
	    global $m_check_uid,$db,$tablepre,$m_check_uid;
	    if(!$m_check_uid)
	    {
	        $this->set_last_error('您还未登录');
	        return false;
	    }
	    
	    if(!$this->consignee || !$this->address || !$this->mobile)
	    {
	        $this->set_last_error('收货信息不完整');
	        return false;
	    }
	    if(!is_array($this->cart_uid) || !is_array($this->memo) || !is_array($this->invoice) || !is_array($this->ship_uid) || !is_array($this->ship_price) || !is_array($this->coupon_uid))
	    {
	        $this->set_last_error('购物车信息有错误，请联系管理员');
	        return false;
	    }
	    
	    if($this->advance==1 && !$this->pay_pass)
	    {
	        $this->set_last_error('没有填写支付密码');
	        return false;
	    }
	    
	    if($this->pay_pass)
	    {
	        $m=$db->get_one("SELECT pay_pass FROM `{$tablepre}member_table` WHERE uid='$m_check_uid' LIMIT 1");
	        if(md5($this->pay_pass)!=$m['pay_pass'])
	        {
	            $this->set_last_error('支付密码错误');
	            return false;
	        }
	    }
	    
	    $this->payment=$db->get_one("SELECT * FROM `{$tablepre}payment_table` WHERE id='{$this->pay_id}' AND supplier_id='0' LIMIT 1");
	    if(!$this->payment)
	    {
	        $this->set_last_error('检索不到指定的支付方式');
	        return false;
	    }
	    
	    $cls_file="include/payment/{$this->payment['class_name']}.class.php";
	    if(!file_exists($cls_file))
	    {
	        $this->set_last_error('指定的支付方式不存在');
	        return false;
	    }
	    require_once $cls_file;
	    $this->o_payment=new $this->payment['class_name'](unserialize($this->payment['cfg']));
	    
	    return true;
	}//end check_info
	
	public function cart_to_order()
	{
	    if(!$this->check_info()) return false;
	    
	    global $m_check_id,$m_check_uid,$m_now_time,$mvm_member,$mm_buy_point;
	    global $db,$tablepre;
	    global $mm_mail_order,$mm_mail_order_cnt,$mm_mall_title;
	    $arr_ordersn=array();
	    
	    $combine_tag='CT'.strval($m_now_time).strval(rand(10,99));
	    $salt=rand(1000,9999);
	    $arr_supplier_id=array_keys($this->cart_uid);
	    $total_price=0;
	    $total_point=0;
	    $code=rand(100000,999999);
	    $arr_order_info=array();
	    foreach ($arr_supplier_id as $supid)
	    {
	        $cart_rtl=$this->cart->cart_spec_list($this->cart_uid[$supid],false);
	        if(!$cart_rtl['cart_list'][$supid]) continue;
	        
	        $coupon=array();
	        do
	        {
	            if($this->coupon_uid[$supid]<=0) break;
	            $coupon=$db->get_one("SELECT uid,name,discount FROM `{$tablepre}coupon` 
	                                  WHERE uid='{$this->coupon_uid[$supid]}' AND start_date<='$m_now_time' AND end_date>='$m_now_time' 
	                                  LIMIT 1");
	            if(!$coupon) break;
	            $db->query("DELETE FROM `{$tablepre}coupon` WHERE uid='$coupon[uid]'");
	            $db->free_result();
	            
	            if($coupon['discount']>floatval($cart_rtl['cart_info'][$supid]['total_price'])+floatval($this->ship_price[$supid]))
	            {
	                $coupon['discount']=floatval($cart_rtl['cart_info'][$supid]['total_price'])+floatval($this->ship_price[$supid]);
	            }
	        }while (0);
	        
	        $ordersn='OD'.strval($m_now_time).strval(rand(10,99));
	        $arr_ordersn[]=$ordersn;
	        
	        $order_row=array(
	            'ordersn'=>$ordersn,
	            'supplier_id'=>$supid,
	            'username'=>$m_check_id,
	            'code'=>$code,
	            'addtime'=>$m_now_time,
	            'status'=>1,
	            'consignee'=>$this->consignee,
	            'address'=>$this->province.' '.$this->city.' '.$this->county.' '.$this->address,
	            'zipcode'=>$this->zipcode,
	            'mobile'=>$this->mobile,
	            'sh_uid'=>$this->ship_uid[$supid],
	            'sh_price'=>floatval($this->ship_price[$supid]),
	            'pay_id'=>$this->pay_id,
	            'pay_name'=>$this->payment['name'],
	            'goods_amount'=>floatval($cart_rtl['cart_info'][$supid]['total_price']),
	            'goods_point'=>(int)$cart_rtl['cart_info'][$supid]['total_point'],
	            'invoice'=>$this->invoice[$supid],
	            'remark'=>$this->memo[$supid],
	            'discount_name'=>$coupon['name'],
	            'discount'=>floatval($coupon['discount']),
	        );
	        if($order_row['discount']>0) $order_row['mark']=4;    //如果使用了优惠券，即使达到条件也不再赠送优惠券了
	        
	        //pre order may be occured with payment
	        if($order_row['goods_amount']<=0)
	        {
	            $order_row['status']=3;
	            $order_row['pay_id']=0;
	            $order_row['pay_name']='预付款';
	        }
	        $insert_id=$db->insert("`{$tablepre}order_info`",$order_row);
	        $db->free_result();
	        $order_row['uid']=$insert_id;
	        $arr_order_info[]=$order_row;
	        $total_price+=$order_row['goods_amount']+$order_row['sh_price']-$order_row['discount'];
	        $total_point+=intval($order_row['goods_point']);
	        
	        $combine_row=array(
	            'tag'=>$combine_tag,
	            'ordersn'=>$ordersn
	        );
	        $db->insert("`{$tablepre}order_combine`",$combine_row);
	        
	        $rest_price=0;
	        foreach ($cart_rtl['cart_list'][$supid] as $val)
	        {
	            $goods_row=array(
	                'order_id'=>$insert_id,
	                'g_uid'=>$val['g_uid'],
	                'goods_name'=>daddslashes($val['goods_name']),
	                'buy_number'=>$val['cart_num'],
	                'buy_price'=>$val['ori_price'],
	                'buy_point'=>$val['cart_point'],
	                'rest_price'=>$val['ori_rest_price'],
	                'goods_attr'=>$val['attr'],
	                'g_type'=>$val['g_type'],
	                'goods_table'=>$val['goods_table'],
	                'module'=>$val['module'],
	                'register_date'=>$m_now_time,
	                'supplier_id'=>$supid
	            );
	            $db->insert("`{$tablepre}order_goods`",$goods_row);
	            $db->query("DELETE FROM `{$tablepre}cart_table` WHERE uid='$val[uid]'");
	            $db->free_result();
	            
	            $rest_price+=$val['ori_rest_price']*$val['cart_num'];
	        }
	        if($rest_price>0)
	        {
	            $db->query("UPDATE `{$tablepre}order_info` SET goods_rest_amount='$rest_price' WHERE uid='$insert_id'");
	            $db->free_result();
	        }
	        $this->cart->destory_cache();
	    }
	    
	    //保存收货地址
	    $rtl=$db->get_one("SELECT COUNT(*) AS cnt FROM `{$tablepre}address` WHERE m_uid='$m_check_uid'");
	    do
	    {
	        if($rtl['cnt']>=3) break;
	        $rtl=$db->get_one("SELECT uid FROM `{$tablepre}address`
	                           WHERE m_uid='$m_check_uid' AND address='{$this->address}' 
	                           LIMIT 1");
	        if($rtl) break;
	        $row=array(
	            'province'=>$this->province,
	            'city'=>$this->city,
	            'county'=>$this->county,
	            'is_buy'=>0,
	            'address'=>$this->address,
	            'zipcode'=>$this->zipcode,
	            'consignee'=>$this->consignee,
	            'mobile'=>$this->mobile,
	            'm_uid'=>$m_check_uid
	        );
	        $db->insert("`{$tablepre}address`",$row);
	        $db->free_result();
	    }while (0);
	    
	    //发送邮件
	    if($mm_mail_order==1 && $arr_ordersn && $mvm_member['member_email'])
	    {
	        $ordersn=implode(',',$arr_ordersn);
	        smtp_mail($mvm_member['member_email'],
	                  str_replace('{ordersn}',$ordersn,"订单{ordersn}等待您付款"),
	                  str_replace(array('{mall_title}','{ordersn}'),array($mm_mall_title,$ordersn),'您在商城{mall_title}的订单{ordersn}已成功提交，请尽快付款'));
	    }
	    
	    if($mvm_member['member_point']<$total_point)    //不足以支付积分
	    {
	        $total_price+=($total_point-$mvm_member['member_point'])/intval($mm_buy_point);
	        $total_point=$mvm_member['member_point'];
	    }
	    
	    if($total_price<=0)    //不用进行任何支付，比如0元预定 或 全积分兑换
	    {
	        //发送短信
	        foreach ($arr_order_info as $val)
	        {
	            supplier_sms_send($val['mobile'], "您的订单：$val[ordersn], 订单密码：{$code}, 请妥善保管",$val['supplier_id']);
	        }
	        
	        if($total_point>0) add_score($m_check_uid,-$total_point,'购物','积分兑换商品',$arr_order_info[0]['ordersn']);
	        
	        $this->form_code='<div class="fct pay_way">'.
                             '<p class="mt10"><a href="member.php?action=order">订单已提交，点些查看详细信息</a></p>'.
                             "<p class='red f20'>订单密码：<strong>{$code}</strong></p>".
                             '</div>';
	        return true;
	    }
	    
	    if($this->advance==1)
	    {
	        if($mvm_member['member_money']<$total_price)
	        {
	            $total_price-=$mvm_member['member_money'];
	            $pay_form=$this->o_payment->pay_send($combine_tag.$salt,$total_price);
	        }
	        else
	        {
	            $pay_form="<a href='respond.php?sn=$combine_tag'><img src='images/pay/yufu.gif' /></a>";
	            $db->query("UPDATE `{$tablepre}order_info` SET pay_id=0,pay_name='预付款' WHERE uid='$insert_id'");
	        }
	    }
	    else $pay_form=$this->o_payment->pay_send($combine_tag.$salt,$total_price);
	    order::create_pay_log($combine_tag,$salt,$total_price);
	    
	    $this->form_code='<div class="fct pay_way"><p>支付方式：'.$this->payment['name'].'</p>'.
                         '<p>结算金额：<strong class="red f14">'.currency($total_price).' + '.$total_point.' 积分</strong></p>'.
                         "<p class='red f20'>订单密码：<strong>{$code}</strong></p>".
                         '<p class="mt10">'.$pay_form.'</p>'.
                         '</div>';
	    return true;
	}//end cart_to_order
    
    public static function create_pay_log($sn,$salt,$amount,$total_money=0,$other_info='')
    {
        global $db,$tablepre,$m_now_time;
        $db->query("REPLACE INTO `{$tablepre}pay_log` (tag,salt,amount,total_money,register_date,other_info) 
                    VALUES ('$sn','$salt','$amount','$total_money','$m_now_time','$other_info')");
    }//end create_pay_log
    
    public static function check_pay_log($sn,$salt,$amount)
    {
        global $db,$tablepre,$m_now_time;
        $b_rtl=false;
        do
        {
            $rtl=$db->get_one("SELECT tag,salt,amount,register_date,approval_date FROM `{$tablepre}pay_log` WHERE tag='$sn' LIMIT 1");
            if(!$rtl) break;
            if($rtl['approval_date']>10) break;
            $db->query("UPDATE `{$tablepre}pay_log` SET approval_date='$m_now_time' WHERE tag='$sn'");
            
            if($rtl['salt']!=$salt) break;
            if(round($amount,2)!=round($rtl['amount'],2)) break;
            if($m_now_time-$rtl['register_date']>300) break;
            
            $b_rtl=true;
        }while (0);
        
        return $b_rtl;
    }//end check_pay_log
    
    public static function get_order_goods($order_uid,$supplier_id=0)
    {
        global $db,$tablepre;
        
        $order_uid=(int)$order_uid;
        $filter_sql=" WHERE order_id='$order_uid'";
        if($supplier_id>0) $filter_sql.=" AND supplier_id='$supplier_id'";
        
        $total_goods_num=0;
        $arr_goods=array();
        $q=$db->query("SELECT uid,g_uid,goods_name,goods_attr,module,supplier_id,buy_price,rest_price,buy_number,buy_point,goods_table,status 
                       FROM `{$tablepre}order_goods` 
                       $filter_sql");
        while ($rtl=$db->fetch_array($q))
        {
            $rtl['buy_total_price']=currency($rtl['buy_price']*$rtl['buy_number']);
            $rtl['buy_rest_price']=currency($rtl['rest_price']*$rtl['buy_number']);
            $rtl['buy_total_point']=$rtl['buy_point']*$rtl['buy_number'];
            $rtl['buy_price']=currency($rtl['buy_price']);
            $rtl['rest_price']=currency($rtl['rest_price']);
            $rtl['url']=GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$rtl['supplier_id']);
            
            $g=$db->get_one("SELECT goods_file1 FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
            $rtl['goods_file1']=ProcImgPath($g['goods_file1']);
            
            $total_goods_num+=$rtl['buy_number'];
            $arr_goods[$rtl['uid']]=$rtl;
        }
        $db->free_result();
        return array($arr_goods,$total_goods_num);
    }//end get_order_goods
    
    public static function dispatch($order_uid)
    {
        global $db,$tablepre,$m_now_time;
        $order_uid=(int)$order_uid;
        $order_info=$db->get_one("SELECT uid,username,ordersn,goods_amount,goods_rest_amount,discount,goods_point,mark,supplier_id,sh_price,status 
                                  FROM `{$tablepre}order_info` 
                                  WHERE uid='$order_uid' 
                                  LIMIT 1");
        if(!$order_info) return ;
        if($order_info['mark'] & 1) return ;    //已分账
        
        $shop=$db->get_one("SELECT m_uid,m_id,supplier_cat,shop_name 
                            FROM `{$tablepre}member_shop` 
                            WHERE m_uid='$order_info[supplier_id]' 
                            LIMIT 1");
        if(!$shop) return ;
        do
        {
            $dispatch_money=0;
            $dispatch_point=0;
            $q=$db->query("SELECT g_uid,buy_number,buy_price,buy_point,goods_table FROM `{$tablepre}order_goods` WHERE order_id='$order_info[uid]'");
            while($rtl=$db->fetch_array($q))
            {
                $g=$db->get_one("SELECT goods_category FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
                $cat=$db->get_one("SELECT rate FROM `{$tablepre}category` WHERE uid='$g[goods_category]' LIMIT 1");
                if(!$cat || $cat['rate']<=0 || $cat['rate']>=1) $cat['rate']=0;
                $dispatch_money+=($rtl['buy_price']*$rtl['buy_number'])*(1-$cat['rate']);
                $dispatch_point+=($rtl['buy_point']*$rtl['buy_number'])*(1-$cat['rate']);
            }
            $db->free_result();
            
            add_score($shop['m_uid'],$dispatch_point,'分账',"订单$order_info[ordersn]分账",$order_info['ordersn']);
            add_money($shop['m_uid'],$dispatch_money+$order_info['sh_price'],'分账',"订单$order_info[ordersn]分账",$order_info['ordersn']);
            
            //分账以后将余款清零
            $db->query("UPDATE `{$tablepre}order_info` SET 
                        goods_amount=goods_amount+goods_rest_amount,
                        goods_rest_amount='0' 
                        WHERE uid='$order_info[uid]'");
            $db->query("UPDATE `{$tablepre}order_goods` SET 
                        buy_price=buy_price+rest_price,
                        rest_price=0 
                        WHERE order_id='$order_info[uid]'");
            $db->free_result();
        }while (0);
        $order_info['mark'] |=1;
        
        
        do    //allow comment
        {
            if($order_info['mark'] & 2) break;
            $order_info['mark'] |=2;
            if($order_info['mark']==6) break;
            
            $expire=$m_now_time+7*24*3600;
            $row=array(
                'from_id'=>$order_info['username'],
                'to_id'=>$shop['m_id'],
                'expire'=>$expire,
                'ordersn'=>$order_info['ordersn'],
                'shop_name'=>$shop['shop_name'],
                'supplier_id'=>$order_info['supplier_id'],
                'roll'=>0
            );
            $db->insert("`{$tablepre}comment_allow`",$row);
            $row=array(
                'from_id'=>$shop['m_id'],
                'to_id'=>$order_info['username'],
                'expire'=>$expire,
                'ordersn'=>$order_info['ordersn'],
                'shop_name'=>$shop['shop_name'],
                'supplier_id'=>$order_info['supplier_id'],
                'roll'=>1
            );
            $db->insert("`{$tablepre}comment_allow`",$row);
            $db->free_result();
        }while (0);
        
        //send coupon
        do
        {
            if($order_info['mark'] & 4) break;
            $order_info['mark'] |=4;
            if($order_info['mark']==6) break;
            
            $coupon_line=$order_info['goods_amount']-$order_info['discount'];
            $coupon_cat=$db->get_one("SELECT uid,supplier_id,name,start_date,end_date,discount,price_lbound 
                                      FROM `{$tablepre}coupon_cat` 
                                      WHERE supplier_id='$order_info[supplier_id]' AND start_date<='$m_now_time' AND end_date>='$m_now_time' AND handout_type='2' AND sale_price<='$coupon_line' 
                                      ORDER BY od DESC 
                                      LIMIT 1");
            if(!$coupon_cat) break;
            $m=$db->get_one("SELECT uid FROM `{$tablepre}member_table` WHERE member_id='$order_info[username]' LIMIT 1");
            if(!$m) break;
            $coupon=array(
                'm_uid'=>$m['uid'],
                'supplier_id'=>$order_info['supplier_id'],
                'cc_uid'=>$coupon_cat['uid'],
                'name'=>$coupon_cat['name'],
                'start_date'=>$coupon_cat['start_date'],
                'end_date'=>$coupon_cat['end_date'],
                'discount'=>$coupon_cat['discount'],
                'price_lbound'=>$coupon_cat['price_lbound'],
                'register_date'=>$m_now_time
            );
            $db->insert("`{$tablepre}coupon`",$coupon);
            $db->query("UPDATE `{$tablepre}coupon_cat` SET member_num=member_num+1 WHERE uid='$coupon_cat[uid]'");
            $db->free_result();
        }while (0);
        
        $db->query("UPDATE `{$tablepre}order_info` SET mark='$order_info[mark]',status='5' WHERE uid='$order_uid'");
        $db->free_result();
    }//end dispatch
    
    public static function change_stock($order_g_uid,$is_minus=true)
    {
        global $db,$tablepre;
        $order_g_uid=(int)$order_g_uid;
        if($order_g_uid<=0) return ;
        $og=$db->get_one("SELECT g_uid,goods_attr,g_type,goods_table,buy_number FROM `{$tablepre}order_goods` WHERE uid='$order_g_uid' LIMIT 1");
        if(!$og) return ;
        if($og['g_type']==7) return ;    //auction goods
        if($og['goods_attr'])
        {
            $detail_table=goods_detail_table($og['g_type']);
            $detail=$db->get_one("SELECT attr_store FROM `$detail_table` WHERE g_uid='$og[g_uid]' LIMIT 1");
        }
        $product=$db->get_one("SELECT goods_stock FROM `$og[goods_table]` WHERE uid='$og[g_uid]' LIMIT 1");
        if(!$product) return ;
        
        if(!$detail['attr_store'])    //没有属性
        {
            $stock=$product['goods_stock']-$og['buy_number'];
            if($stock<0) $stock=0;
            $db->query("UPDATE `$og[goods_table]` SET goods_stock='$stock' WHERE uid='$og[g_uid]'");
        }
        else if($detail['attr_store'] && $og['goods_attr'])
        {
            $arr_attr_store=explode('||',$detail['attr_store']);
            $arr_buy_attr=explode('|',$og['goods_attr']);
            $idx=-1;
            foreach ($arr_attr_store as $key=>$val)
            {
                $is_match=true;
                foreach ($arr_buy_attr as $attr)
                {
                    if(!strstr($val,$attr))
                    {
                        $is_match=false;
                        break;
                    }
                }
                if($is_match)
                {
                    $idx=$key;
                    break;
                }
            }
            if($idx>=0)    //找到了对应的属性
            {
                foreach ($arr_attr_store as $key=>$val)
                {
                    $arr_attr_store[$key]=explode('|',$val);
                }
                $size=sizeof($arr_attr_store[$idx]);
                $arr_attr_store[$idx][$size-1]=$is_minus?(int)$arr_attr_store[$idx][$size-1]-$og['buy_number']:(int)$arr_attr_store[$idx][$size-1]+$og['buy_number'];
                if($arr_attr_store[$idx][$size-1]<0) $arr_attr_store[$idx][$size-1]=0;
                
                $goods_stock=0;
                foreach ($arr_attr_store as $key=>$val)
                {
                    $goods_stock+=(int)$val[sizeof($val)-1];
                    $arr_attr_store[$key]=implode('|',$val);
                }
                $arr_attr_store=implode('||',$arr_attr_store);
                $db->query("UPDATE `$detail_table` SET attr_store='$arr_attr_store' WHERE g_uid='$og[g_uid]'");
                $db->query("UPDATE `$og[goods_table]` SET goods_stock='$goods_stock' WHERE uid='$og[g_uid]'");
            }
        }
    }//end change_stock
    
    public function get_last_error()
    {
        return $this->err;
    }//end get_last_error
    
    public function set_last_error($err)
    {
        $this->err=$err;
    }//end set_last_err
}