<?php

/**
 * MVM_MALL 网上商店系统  系统函数库
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-04-12 $
 * $Id: global.func.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL')) exit('Access Denied');

//$mvm_version= '&copy; 4.0.0（多用户）';
//$lang_powered_mvmmall  = '<a href="http://www.mvmmall.cn" target="_blank" style="font-size:11px">Powered by <strong style="color:#36c;">Mvmmall</strong></a>';

function daddslashes($string, $force = 0)
{
	if(!get_magic_quotes_gpc() || $force)
	{
		if(is_array($string))
		{
			foreach($string as $key => $val) $string[$key] = daddslashes($val, $force);
		}
		else $string = addslashes($string);
	}
	return $string;
}

function show_msg($title,$url='')    //提示信息
{
	@extract($GLOBALS, EXTR_SKIP);
    global $lang;
    $count_down=4000;
    
    $str = $lang[$title];
    !$str && $str=$title;
    require_once template('showmsg');
    $output = str_replace(array('<!--<!---->','<!---->',"\r",substr(MVMMALL_ROOT,0,-1)),'',ob_get_contents());
    ob_end_clean();
    exit($output); 
}

function move_page($url)
{
    header("Location: {$url}");
    exit;
}

function dhtmlchars(&$string)
{
	if(is_array($string))
	{
	    foreach($string as $key => $val) $string[$key] = dhtmlchars($val);
	}
	else
	{
		$string=htmlspecialchars($string);
	}
	return $string;
}

function file_unlink($file_name)
{
	if(!file_exists($file_name)) return false;
	if(strstr($file_name,'noimages')) return false;
	
	@chmod($file_name,0777);
	return  @unlink($file_name);
}

//邮件发送
function smtp_mail($email,$mail_subject,$mail_content,$type='html')
{   
    global $main_settings;
    require_once 'include/class.phpmailer.php';
    $mail = new PHPMailer();
    $mail->CharSet = "utf-8"; // 设置编码
    if ($main_settings['mm_mail_method']==1) $mail->IsMail();// 使用 mail
    elseif ($main_settings['mm_mail_method']==2) $mail->IsSendmail();// 使用 sendmail
    else $mail->IsSMTP();// 使用 SMTP 
    
    $mail->Host = $main_settings['mm_mail_smtphost'];  // 比如：smtp.163.com;mail.tsingfeng.com
    $main_settings['mm_mail_smtpauth'] && $mail->SMTPAuth = true;  // 认证功能  
    $mail->Username = $main_settings['mm_mail_smtpuser'];  // 用户名
    $mail->Password = $main_settings['mm_mail_smtppass']; // 密码
    $mail->From = $main_settings['mm_mail_smtpfrom']; //设置发件人的邮箱地址
    $mail->FromName = $main_settings['mm_mail_name']; //设置发件人的姓名
    $mail->AddAddress($email,''); //设置收件的地址
    $mail->AddReplyTo($main_settings['mm_mail_smtpfrom'],$main_settings['mm_mail_name']);//回复人
    $mail->WordWrap = 50;    //50字换行
    //$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // 附件
    $mail->Subject = $mail_subject;
    if ($type=='html')
    {
      $mail->IsHTML(true); // set email format to HTML 
      $mail->Body = $mail_content; 
    }
    else $mail->AltBody = dhtmlchars($mail_content);//不支持html时，显示的文本 
    
    if($mail->Send()) return true;
}

// banner处理,$status 广告位置 $num 第几个 $limit_width 宽 $limit_height 高
function banner($status='index_left',$num=1,$limit_width=0,$limit_height=0)
{
    global $db,$tablepre,$m_now_time,$cache,$page_member_id,$_URL,$banner_config;
    
    !$GLOBALS['banner_array'] && $GLOBALS['banner_array']=$cache->get_cache('banner_array',$page_member_id);
    $banner_array=$GLOBALS['banner_array'];
    $rt=$banner_array[$status][$num-1];
    if(!$rt) return false;
    
    $banner_config[$status]['w'] && $rt['banner_width']=$banner_config[$status]['w'];
    $banner_config[$status]['h'] && $rt['banner_height']=$banner_config[$status]['h'];
    $limit_width && $rt['banner_width'] = $limit_width;
    $limit_height && $rt['banner_height'] = $limit_height;
    $rt['banner_width']>0 && $width="width='$rt[banner_width]'";
    $rt['banner_height']>0 && $height="height='$rt[banner_height]'";
    
    $rt['banner_file1']=$rt['banner_file1']?ProcImgPath($rt['banner_file1']):$rt['banner_link1'];
    if(!$rt['banner_file1']) $rt['banner_file1']='images/noimages/noproduct.jpg';
    
    $show_file = '';
    if($rt['banner_class'] == 0)
    {
        $show_file = "<a href='$rt[banner_url]' target='_blank'><img src='$rt[banner_file1]' $width $height border='0'></a>";
    }
    else
    {
    	$show_file = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0' $width $height>
					  <param name='movie' value='$rt[banner_file1]'>
					  <param name='play' value='true'>
					  <param name='loop' value='true'>
					  <param name='quality' value='high'>
					  <embed $width $height src='$rt[banner_file1]' play='true' loop='true' quality='high' pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash'></embed>
					  </object>";
    	
    }
    return $show_file ;
}

function goods_array($list)
{
	$list['url'] = GetBaseUrl('product',$list['uid'],'action',$list['supplier_id']);
	$list['goods_file1']=ProcImgPath($list['goods_file1']);
	//if(!$list['goods_file1'] || !file_exists($list['goods_file1'])) $list['goods_file1']='images/noimages/noproduct.jpg';

	$list['goods_sale_price'] = currency($list['goods_sale_price']);
	$list['goods_market_price'] = currency($list['goods_market_price']);
	$list['addoption'] = '';
	$list['addoption'] .= ($list['goods_status'] & 1) ? '<span class="good_hot"></span>' : '';
	$list['addoption'] .= ($list['goods_status'] & 2) ? '<span class="good_sale"></span>' : '';
	$list['addoption'] .= ($list['goods_status'] & 4) ? '<span class="free_deliver"></span>':'';
	
	$list['t']='普通商品';
	if($list['type']==2) $list['t']='批发商品';
	else if($list['type']==3) $list['t']='会员折扣商品';
	
	return $list;
}

function currency($price)    //货币符号替换
{
	global $main_settings;
    $mm_price_sign=$main_settings['mm_price_sign'];
	!$mm_price_sign && $mm_price_sign='￥';
    $price=floatval($price);
	$price=number_format($price,2);
	
    return $mm_price_sign.$price;
}

function sms_send($send_tel,$send_str)
{
    global $main_settings;
	$mm_sms_system_id=$main_settings['mm_sms_system_id'];
	$mm_sms_system_pass=$main_settings['mm_sms_system_pass'];
	$mm_sms_tel=$main_settings['mm_sms_tel'];
	if(!function_exists('curl_init')) return '请先加载curl函数库';
	if(!function_exists('mb_convert_encoding')) return '请先加载MB编码转换库';
	$send_str=trim($send_str);
	if(!$send_str) return '请填写短信发送内容';
	
	$send_str.="【{$mm_sms_tel}】";
	$send_str=mb_convert_encoding($send_str,'GBK','UTF-8');
	$host='120.132.132.102';  
    $path="/ws/BatchSend2.aspx?";
    
    $post_fields=array(
        'CorpID'=>$mm_sms_system_id,
        'Pwd'=>$mm_sms_system_pass,
        'Mobile'=>$send_tel,
        'Content'=>$send_str,
        'Cell'=>'',
        'SendTime'=>''
    );
    $str_rec = '';
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://'.$host.$path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$post_fields);
    $str_rec = curl_exec($ch);
    curl_close($ch);
    
    return $str_rec;
}

function supplier_sms_send($send_tel,$send_str,$supplier_id)
{
	global $db,$tablepre;
	$supplier_id=(int)$supplier_id;
	if($supplier_id<=0) return ;
	$rtl=$db->get_one("SELECT cf_value FROM `{$tablepre}config` WHERE cf_name='mm_shop_sms' AND supplier_id='$supplier_id' LIMIT 1");
	$arr_send_tel=explode(';',$send_tel);
	if((int)$rtl['cf_value']<sizeof($arr_send_tel)) return ;
	//扣除用户短信
	$sms_left=(int)$rtl['cf_value']-sizeof($arr_send_tel);
	$db->query("UPDATE `{$tablepre}config` SET cf_value='$sms_left' WHERE cf_name='mm_shop_sms' AND supplier_id='$supplier_id'");
	
	//发送短信
	return sms_send($send_tel,$send_str);
}

function inner_sms_send($from_id,$to_id,$title,$content,$is_broadcast)
{
    global $m_now_time,$db,$tablepre;
    if(!$from_id || !$to_id || !$title || !$content) return ;
    $content=mb_substr($content,0,100,'UTF-8');
    $row=array(
        'from_id'=>$from_id,
        'to_id'=>$to_id,
        'title'=>$title,
        'content'=>$content,
        'is_broadcast'=>(int)$is_broadcast,
        'reg_date'=>$m_now_time
    );
    $db->insert("`{$tablepre}sms`",$row);
}

function template($template,$EXT='html',$get_path=false)
{
	global $mm_skin_name,$shop_file,$is_mobile;
	$path = $shop_file['sellshow']==1?"templates/$mm_skin_name/":"show_templates/$mm_skin_name/";
	if($get_path) return $path;
	
	$path.="$template.$EXT";
	if(!file_exists($path))
	{
	    if($is_mobile) $path=$shop_file['sellshow']==1?"templates/default_wap/$template.$EXT":"show_templates/default_wap/$template.$EXT";
	    else $path=$shop_file['sellshow']==1?"templates/default/$template.$EXT":"show_templates/default/$template.$EXT";
	}
	return  $path;
}

function LoadBannerConfig()
{
    $banner_config_path=template('','',true);
    $banner_config_path.='banner_config.php';
    if(file_exists($banner_config_path)) include($banner_config_path);
    return $banner_config?$banner_config:array();
}

function GetBaseUrl($name,$key='',$act='action',$tail='1',$supid=0,$is_main=false)    //常见伪静态url处理函数
{
	//当$is_main为true时，$supid则不被使用
	//$tail参数暂时没用上
	global $page_member_id,$main_settings,$_URL,$db_mem_cache;
	$rewrite=$main_settings['rewrite'];
	$mm_subdomain=$main_settings['mm_subdomain'];
	if(!$is_main && $supid<=0) $supid=$page_member_id;
	
	if($mm_subdomain==1)    //启用二级域名
	{
		if($rewrite==1) $linkurl = $key==''?"$name.html":"$name-$key-$tail.html";
        else $linkurl = $key==''?"$name.php":"$name.php?$act=$key";  
	}
	else    //不使用二级域名
	{
		if($rewrite==1)    //静态化
		{
			if($supid==0 && !$is_main) $linkurl = $key==''?"$name-$page_member_id.html":"$name-$key-$page_member_id-1.html";
			else if($is_main) $linkurl = $key==''?"$name.html":"$name-$key-1.html";
            else $linkurl = $key==''?"$name-$supid.html":"$name-$key-$supid-1.html";
		}
		else
		{
			if($supid==0 && !$is_main) $linkurl = $key==''?"$name.php?supid=$page_member_id":"$name.php?$act=$key&supid=$page_member_id";
            else if($is_main) $linkurl = $key==''?"$name.php":"$name.php?$act=$key";
			else $linkurl = $key==''?"$name.php?supid=$supid":"$name.php?$act=$key&supid=$supid";
		}
	}
	if($is_main) $linkurl=$_URL[0].'/'.$linkurl;
	else if($mm_subdomain==1) $linkurl=$db_mem_cache->op(0,'Id2Domain',$supid).$linkurl;
	else $linkurl=$_URL[0].'/union/'.$linkurl;
	
	return $linkurl;
}

function drop_menu($date,$name,$id='',$js='')
{
    $str = " <select name='$name' id='$name' $js>";
  	foreach ($date as $key=>$val)
  	{
  		$chk = $key==$id ? 'selected=selected':'';
  		$str.="<option value='$key'  $chk >$val</option>";
  	}
  	$str .= '</select>';
  	return $str;
}

/**底部处理**/
function footer($require_footer=true)
{
	global $mm_mall_address,$nav2_help,$_URL,$mm_company_num,$nav_foot;
	global $main_settings,$script,$script_param;
	
	$mm_company_num && $mm_company_num = " <a href=\"http://www.miibeian.gov.cn\" target=\"_blank\">$mm_company_num</a>";
	$mm_footer_code = stripslashes("$GLOBALS[mm_footer_code]");
	if($require_footer) require template('footer');
	$output = str_replace(array('<!--<!---->','<!---->-->','<!---->','<!--'.PHP_EOL.'-->-->','<!--'.PHP_EOL.'-->'.PHP_EOL.'-->'),'',ob_get_contents());
	ob_end_clean();
	exit($output);
}

function add_score($m_uid,$point,$type,$reason,$point_sess='',$other_info='')
{
	global $db,$tablepre,$m_now_time,$m_user_ip,$m_check_id;

	$point=intval($point);
	if($point==0) return ;    //没有分值
	
	$m_uid=(int)$m_uid;
	if($m_uid<=0) return ;
	$member=$db->get_one("SELECT uid,member_id,member_class,member_point,member_point_acc FROM `{$tablepre}member_table` WHERE uid='$m_uid' LIMIT 1");
	if(!$member) return ;    //检索不到会员
	
	$member_point=$member['member_point']+$point;
	if($member_point<0) $member_point=0;
	$member_row=array(
	    'member_point'=>$member_point,
	    'member_point_acc'=>$member['member_point_acc']
	);
	if(!$point_sess) $point_sess=$m_now_time;
	if($point>0) $member_row['member_point_acc']+=$point;
	
	//加分记录
	$sql = "INSERT INTO `{$tablepre}point_table` (type,point_id,point_add,point_reason,point_sess,point_left,modify_id,modify_ip,register_date,approval_date,other_info) 
	        VALUES ('$type','$member[member_id]','$point','$reason','$point_sess','$member_point','$m_check_id','$m_user_ip','$m_now_time','$m_now_time','$other_info')";
	$db->query($sql);
	
	$db->update("`{$tablepre}member_table`",$member_row," uid='$member[uid]'");
    $db->free_result();
}

function add_money($m_uid,$money,$type,$reason,$money_sess='',$other_info='')
{
    global $db,$tablepre,$m_now_time,$m_user_ip,$m_check_id;
    
    $money=floatval($money);
    if($money==0) return ;
    
    $m_uid=(int)$m_uid;
	if($m_uid<=0) return ;
	$member=$db->get_one("SELECT uid,member_id,member_money FROM `{$tablepre}member_table` WHERE uid='$m_uid' LIMIT 1");
	if(!$member) return ;    //检索不到会员
	
	$member_money=$member['member_money']+$money;
	if($member_money<0) $member_money=0;
	$member_row=array(
	    'member_money'=>$member_money
	);
	if(!$money_sess) $money_sess=$m_now_time;

	//加预付款记录
	$sql = "INSERT INTO `{$tablepre}money_table` (type,money_id,money_add,money_reason,money_sess,money_left,modify_id,modify_ip,register_date,approval_date,other_info) 
	        VALUES ('$type','$member[member_id]','$money','$reason','$money_sess','$member_money','$m_check_id','$m_user_ip','$m_now_time','$m_now_time','$other_info')";
	$db->query($sql);
	$db->update("`{$tablepre}member_table`",$member_row," uid='$member[uid]'");
	$db->free_result();
}

function get_rate_class($score,$prefix='buyer_class_')
{
    $level=0;
    $score=(int)$score;
    if($score>=1 && $score<=10) $level=1;
    else if($score>=11 && $score<=40) $level=2;
    else if($score>=41 && $score<=90) $level=3;
    else if($score>=91 && $score<=150) $level=4;
    else if($score>=151 && $score<=250) $level=5;
    else if($score>=251 && $score<=500) $level=6;
    else if($score>=501 && $score<=1000) $level=7;
    else if($score>=1001 && $score<=2000) $level=8;
    else if($score>=2001 && $score<=5000) $level=9;
    else if($score>=5001 && $score<=10000) $level=10;
    else if($score>=10001 && $score<=20000) $level=11;
    else if($score>=20001 && $score<=50000) $level=12;
    else if($score>=50001 && $score<=100000) $level=13;
    else if($score>=100001 && $score<=200000) $level=14;
    else if($score>=200001 && $score<=500000) $level=15;
    else if($score>=500001 && $score<=1000000) $level=16;
    else if($score>=1000001 && $score<=2000000) $level=17;
    else if($score>=2000001 && $score<=5000000) $level=18;
    else if($score>=5000001 && $score<=10000000) $level=19;
    else if($score>=10000001) $level=20;
    
    return $prefix.strval($level);   
}

function get_category_chidlren($uid,$level=0)
{
    global $shop_category;
    if(!isset($shop_category)) $GLOBALS['shop_category']=$shop_category=$GLOBALS['cache']->get_cache('shop_category',$GLOBALS['page_member_id']);
    
    $uid=(int)$uid;
    $arr=array();
    if($level==0) array_push($arr,$uid);
    foreach ($shop_category as $val)
    {
        if($val['category_id']==$uid)
        {
            $arr[$val['uid']]=$val['uid'];
            $arr=array_merge($arr,get_category_chidlren($val['uid'],$level+1));
        }
    }
    return $arr;
}

function filter_editor_img($cnt)
{
    $arr_imgs=array();
    preg_match_all('/<img[^>]*?>/',$cnt,$arr_imgs);
    foreach ($arr_imgs[0] as $key=>$val)
    {
        $v_tmp=$val;
        $src_offset=stripos($v_tmp,'src=');
        if($src_offset===false) continue;
        $union_offset=stripos($v_tmp,'union/',$src_offset);
        if($union_offset===false) continue;
        $start=$src_offset+5;
        $end=$union_offset+6;
        $v_tmp=substr_replace($v_tmp,'',$start,$end-$start);
        $cnt=str_replace($val,$v_tmp,$cnt);
        
    }
    return $cnt;
}

function SplitAttr($str_attr)
{
	$arr_rtn=array();
	if(!$str_attr) return $arr_rtn;
	$arr_tmp=explode('||',$str_attr);
	foreach ($arr_tmp as $val)
	{
	    $arr_tmp2=explode('|',$val);
	    $len=sizeof($arr_tmp2);
	    if($len<=0) continue;
	    $key=$arr_tmp2[0];
	    $arr_rtn[$key]=array();
	    for($i=1;$i<$len;$i++)
	    {
	        $embrace_start=mb_strpos($arr_tmp2[$i],'(',0,'UTF-8');
	        $embrace_end=mb_strpos($arr_tmp2[$i],')',$embrace_start,'UTF-8');
	        
	        $str_val=mb_substr($arr_tmp2[$i],0,$embrace_start,'UTF-8');
	        $str_img=mb_substr($arr_tmp2[$i],$embrace_start+1,$embrace_end-$embrace_start-1,'UTF-8');
	        $arr_rtn[$key][]=array($str_val,$str_img);
	    }
	}
	
	return $arr_rtn;
}

function SplitAttrStore($str_attr_store)
{
    $arr_rtn=array();
    if(!$str_attr_store) return $arr_rtn;
    $arr_tmp=explode('||',$str_attr_store);
    foreach ($arr_tmp as $val)
    {
        $arr_rtn[]=explode('|',$val);
    }
    
    return $arr_rtn;
}

function SplitFilterAttr($str_attr)
{
    $arr_rtn=array();
    if(!$str_attr) return $arr_rtn;
    $arr_tmp=explode('|',$str_attr);
    foreach ($arr_tmp as $val)
    {
        $val=base64_decode($val);
        $arr=explode(':',$val);
        $arr_rtn[$arr[0]]=$arr[1];
    }
    
    return $arr_rtn;
}

function GetBoard($ps_name='notice',$from_record=0,$list_num=10,$cut_nums=20)
{
	global $rewrite,$tablepre,$db,$page_member_id;
	$row = array();
	$search_sql=" WHERE supplier_id='$page_member_id' AND ps_name='$ps_name'";
	$order_sql=" ORDER BY od DESC";

	$q = $db->query("SELECT uid,board_subject,register_date,author,ps_name,cover FROM `{$tablepre}bmain` FORCE INDEX(`supplier_id_2`)
	                 $search_sql 
	                 $order_sql 
	                 LIMIT $from_record,$list_num");
	while($list = $db->fetch_array($q))
	{
		if(!$list['cover'] || !file_exists($list['cover'])) $list['cover']='images/noimages/noproduct.jpg';
		$list['url'] = $rewrite==1 ? "article-$list[ps_name]-$list[uid].html":"article.php?action=$list[ps_name]&id=$list[uid]";
		$list['board_subject']= mb_substr($list['board_subject'],0,$cut_nums,'UTF-8');
		$list['register_date'] = date('Y-m-d',$list['register_date']);
		$row[]=$list;
	}
	return $row;
}

function get_goods_discount($arr_discount)
{
    global $m_check_id,$mvm_member;
    $discount=1;
    
    if($m_check_id && isset($arr_discount[$mvm_member['member_class']])) $discount=floatval($arr_discount[$mvm_member['member_class']]);
    
    return $discount;
}

function goods_table($type)
{
    global $shop_file,$tablepre;
    $type=(int)$type;
    
    if(in_array($type,array(0,1,2,3,9))) return "{$tablepre}goods_table";
    else if($type==4) return "{$tablepre}goods_onsale";
    else if($type==5) return "{$tablepre}goods_group";
    else if($type==6) return "{$tablepre}goods_change";
    else if($type==7) return "{$tablepre}goods_auction";
    else if($type==8) return "{$tablepre}goods_show";
    
    return "{$tablepre}goods_table";
}

function goods_detail_table($type,$by_table=false)
{
    global $tablepre;
    $type=(int)$type;
    if($by_table)
    {
        if($type=="{$tablepre}goods_table") return "{$tablepre}goods_detail";
        else if($type=="{$tablepre}goods_onsale") return "{$tablepre}goods_onsale_detail";
        else if($type=="{$tablepre}goods_group") return "{$tablepre}goods_group_detail";
        else if($type=="{$tablepre}goods_change") return "{$tablepre}goods_change_detail";
        else if($type=="{$tablepre}goods_auction") return "{$tablepre}goods_auction_detail";
        else if($type=="{$tablepre}goods_show") return "{$tablepre}goods_show_detail";
    }
    else
    {
        if(in_array($type,array(0,1,2,3,9))) return "{$tablepre}goods_detail";
        else if($type==4) return "{$tablepre}goods_onsale_detail";
        else if($type==5) return "{$tablepre}goods_group_detail";
        else if($type==6) return "{$tablepre}goods_change_detail";
        else if($type==7) return "{$tablepre}goods_auction_detail";
        else if($type==8) return "{$tablepre}goods_show_detail";
    }
    
    return "{$tablepre}goods_detail";
}

function goods_detail_script($type)
{
    $scrtip='product';
    switch ((int)$type)
    {
        case 0:
        case 1:
        case 2:
        case 3:
        case 8:
            $script='product';
            break;
        case 4:
            $script='salegd_detail';
            break;
        case 5:
            $script='group_detail';
            break;
        case 6:
            $script='changegd_detail';
            break;
        case 7:
            $script='auction_detail';
            break;
        default:
            $script='product';
            break;
    }
    return $script;
}

function isMobile()
{
    //return true;
    
    if($_GET['ismobile']=='ismobile') return  true;
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) return true;
    
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = 'nokia|sony|ericsson|mot|samsung|htc|sgh|lg|sharp|sie-|philips|panasonic|alcatel|lenovo'.
                          '|iphone|ipod|blackberry|meizu|android|netfront|symbian|ucweb|windowsce|palm|operamini|operamobi|openwave|nexusone|cldc|midp|wap|mobile'.
                          '|micromessenger';
        if (preg_match("/($clientkeywords)/i", strtolower($_SERVER['HTTP_USER_AGENT']))) return true;
    }
    
    if (isset ($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) return true;
    
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && 
            (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || 
            (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
    
    return false;
}

function BrowserType()
{
    $arr_browser_type=array('chrome','firefox','msie');
    $type='other';
    
    foreach ($arr_browser_type as $val)
    {
        if(stristr($_SERVER['HTTP_USER_AGENT'],$val))
        {
            $type=$val;
            break;
        }
    }
    return $type;
}

function page_shutdown()
{
    global $db_mem_cache;
    
    foreach ($GLOBALS['mvm_lock'] as $key=>$val)
    {
        $db_mem_cache->op(0,'lock',$key,'lock',db_memory_cache::UNLOCK );
    }
}

function ProcImgPath($img_path,$tag='product',$website=IMG_URL)    //处理路片路径

{
    //if(strstr($img_path,'http://')) return $img_path;
	$img_path=strstr($img_path,'union/')?$img_path:'union/'.$img_path;
	$img_path=$website!=''?$website.$img_path:$img_path;
	if(!$img_path) $img_path="images/noimages/no{$tag}.jpg";
	return $img_path;

}