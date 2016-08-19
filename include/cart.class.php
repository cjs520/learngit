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
 * $Id: cart.class.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

class cart
{
	private $arr_grade_discount;
	private $err;
	
	private $my_cart_sql='';
	
    public function __construct()
	{
	    global $sessionID,$m_check_id;
	    
	    $this->my_cart_sql="(cart_sess='$sessionID'";
	    if($m_check_id) $this->my_cart_sql.=" OR m_id='$m_check_id' ";
	    $this->my_cart_sql.=')';
	    
		$this->arr_grade_discount=array();
	}//end construct
	
    public function add ($goods_id,$goods_table,$ps_num,$att_value,$from_mod,$refer_g_uid=0,$preorder=false)
    {
        global $db,$sessionID,$m_check_id,$m_now_time,$tablepre;
        $ps_num=(int)$ps_num;
        $ps_num<=0 && $ps_num=1;
        
        $fields='uid,goods_stock,type,goods_sale_price,supplier_id';
        if(strstr($goods_table,'goods_change')) $fields.=',goods_sale_point';    //积分换购特殊字段
        if(strstr($goods_table,'goods_table')) $fields.=',down_payment';    //预订特殊字段
        
        $goods_id=(int)$goods_id;
        $goods=$db->get_one("SELECT $fields FROM `$goods_table` WHERE uid='$goods_id' LIMIT 1");
        if(!$goods)
        {
            $this->set_last_error('检索不到指定的商品');
            return false;
        }
        if($ps_num>$goods['goods_stock'])
        {
            $this->set_last_error('您购买的数量超过商品库存');
            return false;
        }
        
        $price=$this->get_price($goods,$refer_g_uid);
        if($price<0) return false;
        $attr_price=$this->get_attr_price($att_value,$goods);
        if($goods['type']==9 && $preorder) $rest_price=$goods['goods_sale_price']+$attr_price-$price;
        else $price+=$attr_price;
        
        $db->query("DELETE FROM `{$tablepre}cart_table` 
                    WHERE ({$this->my_cart_sql}) AND goods_table='$goods_table' AND g_uid='$goods_id' AND refer_g_uid='$refer_g_uid' AND attr='$att_value'");
        $db->free_result();
        $row=array(
            'cart_sess'=>$m_check_id?'':$sessionID,
            'm_id'=>$m_check_id,
            'g_uid'=>$goods_id,
            'refer_g_uid'=>$refer_g_uid,
            'cart_price'=>$price,
            'cart_point'=>$goods['goods_sale_point'],
            'cart_num'=>$ps_num,
            'attr'=>$att_value,
            'g_type'=>$goods['type'],
            'goods_table'=>$goods_table,
            'module'=>$from_mod,
            'supplier_id'=>$goods['supplier_id'],
            'register_date'=>$m_now_time
        );
        $db->insert("`{$tablepre}cart_table`",$row);
        unset($_SESSION['mvm_cart']);
        
        return true;
    }//end add
    
    public function add_bat_cart($goods_id,$goods_table,$arr_ps_num,$arr_attr,$from_mod)
    {
        global $db,$sessionID,$m_check_id,$m_now_time,$tablepre;
        if(!is_array($arr_ps_num) || !is_array($arr_attr))
        {
            $this->set_last_error('购买参数错误');
            return false;
        }
        if(sizeof($arr_ps_num)!=sizeof($arr_attr))
        {
            $this->set_last_error('购买参数错误');
            return false;
        }
        $ps_num=0;
        foreach ($arr_ps_num as $key=>$val)
        {
            $val=(int)$val;
            if($val<=0)
            {
                $val=0;
                unset($arr_ps_num[$key]);
                unset($arr_attr[$key]);
            }
            $ps_num+=$val;
        }
        if($ps_num<=0)
        {
            $this->set_last_error('请正确填写批发商品数量');
            return false;
        }
        
        $goods_id=(int)$goods_id;
        $goods=$db->get_one("SELECT uid,goods_stock,type,goods_sale_price,supplier_id FROM `$goods_table` WHERE uid='$goods_id' LIMIT 1");
        if(!$goods)
        {
            $this->set_last_error('检索不到指定的商品');
            return false;
        }
        if($ps_num>$goods['goods_stock'])
        {
            $this->set_last_error('您购买的数量超过商品库存');
            return false;
        }
        $price=$this->get_price($goods,0,$ps_num);
        $db->query("DELETE FROM `{$tablepre}cart_table` WHERE ({$this->my_cart_sql}) AND goods_table='$goods_table' AND g_uid='$goods_id'");
        $db->free_result();
        foreach ($arr_ps_num as $key=>$val)
        {
            $attr_price=$this->get_attr_price($arr_attr[$key],$goods);
            $row=array(
                'cart_sess'=>$m_check_id?'':$sessionID,
                'm_id'=>$m_check_id,
                'g_uid'=>$goods_id,
                'refer_g_uid'=>0,
                'cart_price'=>$price+$attr_price,
                'cart_point'=>0,
                'cart_num'=>$val,
                'attr'=>$arr_attr[$key],
                'g_type'=>$goods['type'],
                'goods_table'=>$goods_table,
                'module'=>$from_mod,
                'supplier_id'=>$goods['supplier_id'],
                'register_date'=>$m_now_time
            );
            $db->insert("`{$tablepre}cart_table`",$row);
        }
        unset($_SESSION['mvm_cart']);
        
        return true;
    }//end add_bat_cart
    
    private function get_price($goods,$refer_g_uid=0,$ps_num=0)    //获取商品单价
    {
        global $mvm_member,$tablepre,$db;
        do    //处理组合购买
        {
            if($refer_g_uid<=0) break;
            $rtl=$db->get_one("SELECT price FROM `{$tablepre}goods_combine` WHERE g_uid='$refer_g_uid' AND com_uid='$goods[uid]' LIMIT 1");
            if($rtl) $price=floatval($rtl['price']);
            else
            {
                $this->set_last_error('检索不到指定的组合商品');
                $price=-1;
            }
            return $price;
        }while (0);
        
        switch ((int)$goods['type'])
        {
            case 2:    //批发
                $price=$goods['goods_sale_price'];
                $rtl=$db->get_one("SELECT wholesale_price FROM `{$tablepre}goods_detail` WHERE g_uid='$goods[uid]' LIMIT 1");
                if(!$rtl) return $price;
                $arr_wholesale_price=unserialize($rtl['wholesale_price']);
                if(!is_array($arr_wholesale_price) || !$arr_wholesale_price) return $price;
                foreach ($arr_wholesale_price as $key=>$val)
                {
                    $val[0]=intval($val[0]);
                    $val[1]=intval($val[1]);
                    $val[2]=floatval($val[2]);
                    if($val[1]==-1) return $ps_num>=$val[0]?$val[2]:$price;
                    else
                    {
                        if($val[0]<=$ps_num && $val[1]>=$ps_num)
                        {
                            $price=$val[2];
                            break;
                        }
                    }
                }
                return $price;
                break;
            case 3:    //会员折扣
                return $goods['goods_sale_price']*$this->get_discount($goods['supplier_id'],$mvm_member['member_class']);
                break;
            default:
                return $goods['goods_sale_price'];
                break;
        }
    }//end get_price
    
    private function get_attr_price($attr,$goods)
    {
        global $db,$tablepre;
        if($goods['type']==7) return 0;    //折卖不需要属性价格
        if($goods['type']<0 || $goods['type']>9) return 0;
        $goods_detail_table=goods_detail_table($goods['type']);
        $rtl=$db->get_one("SELECT attr_store FROM `$goods_detail_table` WHERE g_uid='$goods[uid]' LIMIT 1");
        if(!$rtl['attr_store']) return 0;
        
        $arr_attr=explode('|',$attr);
        $arr_attr_store=explode('||',$rtl['attr_store']);
        $attr_price=0;
        foreach ($arr_attr_store as $val)
        {
            $b_find=true;
            foreach ($arr_attr as $v)
            {
                if(!strstr($val,$v))
                {
                    $b_find=false;
                    break;
                }
            }
            if($b_find)
            {
                $arr_tmp=explode('|',$val);
                $attr_price=intval($arr_tmp[sizeof($arr_tmp)-2]);
                break;
            }
        }
        return $attr_price;
    }//end get_attr_price
    
    private function get_discount($supplier_id,$group_id)
    {
        global $db,$tablepre;
        $supplier_id=(int)$supplier_id;
        $group_id=(int)$group_id;
        if($group_id<=0) return 1;
        
        if(isset($this->arr_grade_discount[$supplier_id][$group_id]))
        {
            return floatval($this->arr_grade_discount[$supplier_id][$group_id]);
        }
        
        $rtl=$db->get_one("SELECT discount FROM `{$tablepre}grade_discount` WHERE supplier_id='$supplier_id' AND group_id='$group_id' LIMIT 1");
        $rtl['discount']=floatval($rtl['discount']);
        $rtl['discount']<=0 && $rtl['discount']=1;
        $this->arr_grade_discount[$supplier_id][$group_id]=$rtl['discount'];

        return $this->arr_grade_discount[$supplier_id][$group_id];
    }//end get_discount
    
    public function get_simple_info()
    {
        global $db,$tablepre,$m_check_id,$sessionID;
        
        if(isset($_SESSION['mvm_cart']))
        {
            $rtl=$_SESSION['mvm_cart'];
        }
        else
        {
            $_SESSION['mvm_cart']=array();
            $rtl=$db->get_one("SELECT SUM(cart_price*cart_num) AS total_price,SUM(cart_num) AS total_num,SUM(cart_point*cart_num) AS total_point FROM `{$tablepre}cart_table` 
                               WHERE {$this->my_cart_sql}");
        }
        $rtl['total_price']=floatval($rtl['total_price']);
        $rtl['total_price_txt']=currency($rtl['total_price']);
        $rtl['total_num']=(int)$rtl['total_num'];
        $rtl['total_point']=(int)$rtl['total_point'];
        
        $_SESSION['mvm_cart']['total_price']=$rtl['total_price'];
        $_SESSION['mvm_cart']['total_num']=$rtl['total_num'];
        $_SESSION['mvm_cart']['total_point']=$rtl['total_point'];
        
        return $rtl;
    }//end get_simple_info
    
    public function cart_list()
    {
        global $db,$tablepre,$m_check_id,$sessionID;
        $arr_cart=array(
            'cart_list'=>array(),
            'shop_info'=>array(),
        );
        $arr_supplier_id=array();
        
        $q=$db->query("SELECT uid,cart_price,rest_price,cart_point,g_uid,cart_num,goods_table,module,attr,g_type,refer_g_uid 
                       FROM `{$tablepre}cart_table` WHERE {$this->my_cart_sql}");
        while ($rtl=$db->fetch_array($q))
        {
            $g=$db->get_one("SELECT goods_name,goods_file1,supplier_id FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
            if(!$g) continue;
            if(!in_array($g['supplier_id'],$arr_supplier_id)) $arr_supplier_id[]=$g['supplier_id'];
            
            $arr_cart['cart_list'][]=array(
                'uid'=>$rtl['uid'],
                'cart_price'=>currency($rtl['cart_price']),
                'rest_price'=>currency($rtl['rest_price']),
                'ori_price'=>$rtl['cart_price'],
                'cart_point'=>$rtl['cart_point'],
                'cart_num'=>$rtl['cart_num'],
                'attr'=>$rtl['attr'],
                'goods_name'=>$g['goods_name'],
                'goods_file1'=>ProcImgPath($g['goods_file1']),
                'url'=>GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$g['supplier_id']),
                'g_uid'=>$rtl['g_uid'],
                'g_type'=>$rtl['g_type'],
                'refer_g_uid'=>$rtl['refer_g_uid'],
                'supplier_id'=>$g['supplier_id']
            );
        }
        $db->free_result();
        if(!$arr_supplier_id) return $arr_cart;
        
        $str_supplier_id=implode(',',$arr_supplier_id);
        $q=$db->query("SELECT m_uid,shop_name FROM `{$tablepre}member_shop` WHERE m_uid IN ($str_supplier_id)");
        while ($rtl=$db->fetch_array($q))
        {
            $rtl['url']=GetBaseUrl('index','','',$rtl['m_uid']);
            $cfg=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$rtl[m_uid]' AND cf_name='mm_client_qq1' LIMIT 1");
            $rtl['qq']=$cfg['cf_value'];
            $arr_cart['shop_info'][$rtl['m_uid']]=$rtl;
        }
        $db->free_result();
        $arr_cart=array_merge($arr_cart,$this->get_simple_info());
        
        return $arr_cart;
    }//end cart_list
    
    public function cart_simple_list($limit=0)
    {
        global $db,$tablepre,$m_check_id,$sessionID;
        $sql_limit='';
        if($limit>0) $sql_limit=" LIMIT $limit";
        $arr_cart=array();
        $q=$db->query("SELECT cart_price,g_uid,cart_num,goods_table,module,attr 
                       FROM `{$tablepre}cart_table` WHERE {$this->my_cart_sql} $sql_limit");
        while ($rtl=$db->fetch_array($q))
        {
            $g=$db->get_one("SELECT goods_name,goods_file1,supplier_id FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
            
            $arr_cart[]=array(
                'cart_price'=>currency($rtl['cart_price']),
                'cart_num'=>$rtl['cart_num'],
                'attr'=>$rtl['attr'],
                'goods_name'=>$g['goods_name'],
                'goods_file1'=>ProcImgPath($g['goods_file1']),
                'url'=>GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$g['supplier_id'])
            );
        }
        $db->free_result();
        return $arr_cart;
    }//end cart_simple_list
    
    public function cart_spec_list($str_uids,$with_shop_info=true)
    {
        global $db,$tablepre,$m_check_id,$sessionID,$m_now_time,$m_check_uid;
        if(!$m_check_id) return false;
        $str_uids=trim($str_uids);
        if(!$str_uids) return false;
        $arr_uids=explode(',',$str_uids);
        foreach ($arr_uids as $key=>$val)
        {
            $val=(int)$val;
            if($val<=0) unset($arr_uids[$key]);
            else $arr_uids[$key]=$val;
        }
        if(!$arr_uids) return false;
        $str_uids=implode(',',$arr_uids);
        
        $arr_cart=array();
        $arr_supplier_id=array();
        
        $q=$db->query("SELECT uid,cart_price,rest_price,cart_point,g_uid,cart_num,goods_table,module,attr,g_type,refer_g_uid,goods_table,module 
                       FROM `{$tablepre}cart_table` WHERE uid IN ($str_uids) AND ({$this->my_cart_sql})");
        while ($rtl=$db->fetch_array($q))
        {
            $g=$db->get_one("SELECT uid,goods_name,goods_file1,supplier_id,type,goods_status 
                             FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' 
                             LIMIT 1");
            if(!$g) continue;
            
            $g['goods_kg']=0;
            if(!($g['goods_status'] & 4))    //需要算邮费
            {
                $detail_table=goods_detail_table($g['type']);
                $detail=$db->get_one("SELECT goods_kg FROM `$detail_table` WHERE g_uid='$g[uid]' LIMIT 1");
                $g['goods_kg']=intval($detail['goods_kg']);
            }
            
            
            if(!in_array($g['supplier_id'],$arr_supplier_id)) $arr_supplier_id[]=$g['supplier_id'];
            
            $arr_cart['cart_list'][$g['supplier_id']][]=array(
                'uid'=>$rtl['uid'],
                'cart_price'=>currency($rtl['cart_price']),
                'rest_price'=>currency($rtl['rest_price']),
                'ori_price'=>$rtl['cart_price'],
                'ori_rest_price'=>$rtl['rest_price'],
                'cart_point'=>$rtl['cart_point'],
                'total_price'=>currency($rtl['cart_price']*$rtl['cart_num']),
                'cart_num'=>$rtl['cart_num'],
                'attr'=>$rtl['attr'],
                'goods_name'=>$g['goods_name'],
                'goods_file1'=>ProcImgPath($g['goods_file1']),
                'url'=>GetBaseUrl($rtl['module'],$rtl['g_uid'],'action',$g['supplier_id']),
                'g_uid'=>$rtl['g_uid'],
                'g_type'=>$rtl['g_type'],
                'refer_g_uid'=>$rtl['refer_g_uid'],
                'supplier_id'=>$g['supplier_id'],
                'goods_table'=>$rtl['goods_table'],
                'module'=>$rtl['module']
            );
            
            $arr_cart['cart_info'][$g['supplier_id']]['total_price']+=floatval($rtl['cart_price']*$rtl['cart_num']);
            $arr_cart['cart_info'][$g['supplier_id']]['total_point']+=intval($rtl['cart_point']*$rtl['cart_num']);
            $arr_cart['cart_info'][$g['supplier_id']]['total_kg']+=intval($g['goods_kg']*$rtl['cart_num']);
            if(!$arr_cart['cart_info'][$g['supplier_id']]['is_preorder']) $arr_cart['cart_info'][$g['supplier_id']]['is_preorder']=$rtl['rest_price']>0?true:false;
        }
        $db->free_result();
        if(!$with_shop_info) return $arr_cart;
        if(!$arr_supplier_id) return $arr_cart;
        
        $str_supplier_id=implode(',',$arr_supplier_id);
        $q=$db->query("SELECT m_uid,shop_name FROM `{$tablepre}member_shop` WHERE m_uid IN ($str_supplier_id)");
        while ($rtl=$db->fetch_array($q))
        {
            //get shop info
            $rtl['url']=GetBaseUrl('index','','',$rtl['m_uid']);
            $cfg=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE supplier_id='$rtl[m_uid]' AND cf_name='mm_client_qq1' LIMIT 1");
            $rtl['qq']=$cfg['cf_value'];
            $arr_cart['shop_info'][$rtl['m_uid']]=$rtl;
            
            //get coupon
            $arr_cart['coupon'][$rtl['m_uid']]=array();
            $q_tmp=$db->query("SELECT uid,name,discount FROM `{$tablepre}coupon`  
                               FORCE INDEX (`supplier_id`) 
                               WHERE m_uid='$m_check_uid' AND supplier_id='$rtl[m_uid]' AND start_date<='$m_now_time' AND end_date>='$m_now_time' 
                                     AND price_lbound<='{$arr_cart['cart_info'][$rtl['m_uid']]['total_price']}'");
            while ($rtl_tmp=$db->fetch_array($q_tmp))
            {
                $arr_cart['coupon'][$rtl['m_uid']][]=$rtl_tmp;
            }
        }
        $db->free_result();
        return $arr_cart;
    }//end cart_spec_list
    
    public function change_num($uid,$num)
    {
        global $db,$tablepre,$m_check_id,$sessionID;
        $this->set_last_error(false);
        $num=(int)$num;
        if($num<=0)
        {
            $num=1;
            $this->set_last_error('数量不能小于1');
            return $num;
        }
        
        $cart=$db->get_one("SELECT uid,g_uid,g_type,goods_table,cart_num,attr,cart_price 
                            FROM `{$tablepre}cart_table` 
                            WHERE uid='$uid' AND ({$this->my_cart_sql}) 
                            LIMIT 1");
        if(!$cart)
        {
            $this->set_last_error('检索不到指定的购物车记录');
            return $num;
        }
        $g=$db->get_one("SELECT uid,goods_stock,type,goods_sale_price,supplier_id 
                         FROM `$cart[goods_table]` WHERE uid='$cart[g_uid]' LIMIT 1");
        if(!$g)
        {
            $this->set_last_error('检索不到指定的商品');
            $db->query("DELETE FROM `{$tablepre}cart_table` WHERE uid='$uid'");
            return $num;
        }
        $detail_table=goods_detail_table($cart['g_type']);
        $detail=$db->get_one("SELECT attr_store FROM `$detail_table` WHERE g_uid='$cart[g_uid]' LIMIT 1");
        if(!$detail['attr_store'] || !$cart['attr'])    //no need to judge attr
        {
            if($num>$g['goods_stock'])
            {
                $num=$g['goods_stock'];
                $this->set_last_error('商品数量已达到库存最大值');
            }
        }
        else
        {
            $i_find=-1;
            $arr_attr=explode('|',$cart['attr']);
            $arr_attr_store=explode('||',$detail['attr_store']);
            foreach ($arr_attr_store as $key=>$val)
            {
                $b_find=true;
                foreach ($arr_attr as $v)
                {
                    if(!strstr($val,$v))
                    {
                        $b_find=false;
                        break;
                    }
                }
                if($b_find)
                {
                    $i_find=$key;
                    break;
                }
            }
            if($i_find==-1)    //attr matchable is not found
            {
                if($num>$g['goods_stock'])
                {
                    $num=$g['goods_stock'];
                    $this->set_last_error('商品数量已达到库存最大值');
                }
            }
            else
            {
                $arr_tmp=explode('|',$arr_attr_store[$i_find]);
                $store=(int)$arr_tmp[sizeof($arr_tmp)-1];
                if($num>$store)
                {
                    $num=$store;
                    $this->set_last_error('商品数量已达到库存最大值');
                }
            }
        }
        if($num<=0) $num=1;
        if($num!=$cart['cart_num'])
        {
            $db->query("UPDATE `{$tablepre}cart_table` SET cart_num='$num' WHERE uid='$uid'");
            $db->free_result();
        }
        if($cart['g_type']==2)    //update bat records
        {
            $rtl=$db->get_one("SELECT SUM(cart_num) AS total_num FROM `{$tablepre}cart_table` 
                               WHERE g_uid='$cart[g_uid]' AND ({$this->my_cart_sql}) AND goods_table='$cart[goods_table]'");
            $price=$this->get_price($g,0,(int)$rtl['total_num']);
            $arr_attr=explode('|',$cart['attr']);
            if($price!=$cart['cart_price']-intval($arr_attr[sizeof($arr_attr)-1])) $this->set_last_error('UPDATE');    //提示客户端更新页面
            $q=$db->query("SELECT uid,attr FROM `{$tablepre}cart_table` WHERE g_uid='$cart[g_uid]' AND ({$this->my_cart_sql}) AND goods_table='$cart[goods_table]'");
            while ($rtl=$db->fetch_array($q))
            {
                $arr_tmp=explode('|',$rtl['attr']);
                $cart_price=$price+intval($arr_tmp[sizeof($arr_tmp)-1]);
                $db->query("UPDATE `{$tablepre}cart_table` SET cart_price='$cart_price' WHERE uid='$rtl[uid]'");
                $db->free_result(1);
            }
            $db->free_result();
        }
        
        $this->destory_cache();
        return $num;
    }//end change_num
    
    public function update_discount()
    {
        global $db,$tablepre,$m_check_id,$sessionID;
        $q=$db->query("SELECT uid,g_uid,goods_table,g_type FROM `{$tablepre}cart_table` 
                       WHERE ({$this->my_cart_sql}) AND g_type='3' AND goods_table='{$tablepre}goods_table'");
        while ($rtl=$db->fetch_array($q))
        {
            $g=$db->get_one("SELECT uid,goods_stock,type,goods_sale_price,supplier_id FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]' LIMIT 1");
            if(!$g || $g['type']!=$rtl['g_type'])
            {
                $db->query("DELETE FROM `{$tablepre}cart_table` WHERE uid='$rtl[uid]'");
                continue;
            }
            $price=$this->get_price($g);
            $db->query("UPDATE `{$tablepre}cart_table` SET cart_price='$price' WHERE uid='$rtl[uid]'");
        }
        $db->free_result();
        $this->destory_cache();
    }//end update_discount
    
    public function del($uid,$return_cart=false)
    {
        global $db,$tablepre,$m_check_id,$sessionID;
        $rtl=$db->get_one("SELECT uid,g_type,g_uid,refer_g_uid,goods_table,module FROM `{$tablepre}cart_table` 
                           WHERE uid='$uid' AND ({$this->my_cart_sql}) 
                           LIMIT 1");
        if(!$rtl) return false;
        
        if($rtl['g_uid']==$rtl['refer_g_uid'])    //组合主商品
        {
            $db->query("DELETE FROM `{$tablepre}cart_table` WHERE uid='$rtl[uid]'");
            $db->query("DELETE FROM `{$tablepre}cart_table` WHERE refer_g_uid='$rtl[g_uid]' AND ({$this->my_cart_sql})");
            $this->destory_cache();
            return $return_cart?$rtl:true;
        }
        
        $db->query("DELETE FROM `{$tablepre}cart_table` WHERE uid='$rtl[uid]'");
        $db->free_result(1);
        do    //批发
        {
            if($rtl['g_type']!=2) break;
            $goods=$db->get_one("SELECT uid,goods_stock,type,goods_sale_price,supplier_id FROM `$rtl[goods_table]` WHERE uid='$rtl[g_uid]'");
            if(!$goods) break;
            $bat_rtl=$db->get_one("SELECT SUM(cart_num) AS sm FROM `{$tablepre}cart_table` WHERE g_uid='$rtl[g_uid]' AND ({$this->my_cart_sql})");
            $bat_rtl['sm']=(int)$bat_rtl['sm'];
            if($bat_rtl['sm']<=0) break;
            $price=$this->get_price($goods,0,$bat_rtl['sm']);
            
            $q=$db->query("SELECT uid,attr FROM `{$tablepre}cart_table` WHERE g_uid='$rtl[g_uid]' AND ({$this->my_cart_sql})");
            while ($rtl_tmp=$db->fetch_array($q))
            {
                $arr_tmp=explode('|',$rtl_tmp['attr']);
                $cart_price=$price+intval($arr_tmp[sizeof($arr_tmp)-1]);
                $db->query("UPDATE `{$tablepre}cart_table` SET cart_price='$cart_price' WHERE uid='$rtl_tmp[uid]'");
                $db->free_result(1);
            }
            $db->free_result();
        }while (0);
        $this->destory_cache();
        
        return $return_cart?$rtl:true;
    }//end function del
    
    public function destory_cache()
    {
        unset($_SESSION['mvm_cart']);
    }//end destory_history
    
    public function get_last_error()
    {
        return $this->err;
    }//end get_last_error
    
    public function set_last_error($err)
    {
        $this->err=$err;
    }//end set_last_error
}