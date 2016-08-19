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

$mvm_version= '&copy; 4.0.0（多用户标准企业版）';
$lang_powered_mvmmall  = '<a href="http://www.mvmmall.cn" target="_blank" style="font-size:11px">Powered by <strong style="color:#36c;">MvMmall</strong></a>';

function daddslashes(&$string, $force = 0)
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

function show_msg($title,$url="")
{
    if(in_array($_GET['code'],array('wxpay'))) exit;    //微信支付无需显示出任何信息
    
	global $lang,$mm_logo;
	$count_down=4000;
	$str = $lang[$title];
	!$str && $str=$title;
    if($_GET['ajax']=='ajax') exit($str.'||'.$url);
    
	require_once template('showmsg');
	$output = str_replace(array('<!--<!---->','<!---->',"\r"),'',ob_get_contents());
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

function file_unlink($file_name,$typ='host')
{   if($typ=='host'){
	if(!file_exists($file_name)) return false;
	if(strstr($file_name,'noimages')) return false;
	@chmod($file_name,0777);
	return  @unlink($file_name);   }else {   	  require_once __DIR__ . '/oss-sdk/Common.php';      $bucket = Common::getBucketName();      $ossClient = Common::getOssClient();      $file_name=str_replace(OSS_IMG, '',$file_name);      if(!$ossClient->doesObjectExist($bucket,$file_name)) return ;      else $rs=$ossClient->deleteObject($bucket,$file_name);   }
}
function get_dirinfo($dir_path,$file='',$exclude_file='')
{
	$dir_file_name=array();
	if(!is_dir($dir_path) || !($area_lord = @opendir($dir_path))) return $dir_file_name;

	while($dir_info = @readdir($area_lord))
	{
		if(in_array($dir_info,array('.','..'))) continue;
		if ($file && !strstr($dir_info,$file)) continue;
		if($exclude_file!='' && strstr($dir_info,$exclude_file)) continue;
		$dir_file_name[] = $dir_info;
	}
	closedir($area_lord);
	return $dir_file_name;
}

function smtp_mail($email,$mail_subject,$mail_content,$is_group=false)    //邮件发送
{
	require_once 'include/class.phpmailer.php';
	$mail = new PHPMailer();
	$mail->CharSet = "utf-8";    // 设置编码
	$mail->IsSMTP();    // 使用 SMTP

	$mail->Host = $GLOBALS['mm_mail_smtphost'];    // 比如：smtp.163.com
	$GLOBALS['mm_mail_smtpauth'] && $mail->SMTPAuth = true;    // 认证功能
	$mail->Username = $GLOBALS['mm_mail_smtpuser'];    // 用户名
	if($is_group && $mail->Host=='smtpcloud.sohu.com')
	{
	    $mail->Username = $GLOBALS['mm_mail_smtpuser_ad'];    //群发用户名
	}
	$mail->Password = $GLOBALS['mm_mail_smtppass'];    // 密码
	$mail->From = $GLOBALS['mm_mail_smtpfrom'];    //设置发件人的邮箱地址
	$mail->FromName = $GLOBALS['mm_mail_name'];    //设置发件人的姓名
	$arr_email=explode('|',$email);
	foreach ($arr_email as $val) $mail->AddAddress($val,'');    //设置收件的地址
	$mail->AddReplyTo($GLOBALS['mm_mail_smtpfrom'],$GLOBALS['mm_mail_name']);//回复人
	$mail->WordWrap = 50;    //50字换行
	//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // 附件
	$mail->Subject = $mail_subject;
	$mail->IsHTML(true);    // set email format to HTML
	$mail->Body = $mail_content;

	if($mail->Send()) return true;
	return false;
}

function GetBoard($ps_name='notice',$from_record=0,$list_num=10,$cut_nums=20)
{
	global $rewrite,$tablepre,$db;
	$row = array();
	$search_sql=" WHERE supplier_id='0'";
	$order_sql=" ORDER BY register_date DESC";
	
	if($ps_name)
	{
	    $search_sql.=" AND ps_name='$ps_name'";
	    $order_sql=" ORDER BY od DESC";
	}

	$q = $db->query("SELECT uid,board_subject,register_date,author,ps_name,cover FROM `{$tablepre}bmain`
	                 $search_sql 
	                 $order_sql 
	                 LIMIT $from_record,$list_num");
	while($list = $db->fetch_array($q))
	{       $list['cover']=IMG_URL.$list['cover'];
		if(!$list['cover']) $list['cover']='images/noimages/noproduct.jpg';
		$list['url'] = $rewrite==1 ? "article-$list[ps_name]-$list[uid].html":"article.php?action=$list[ps_name]&id=$list[uid]";
		$list['board_subject']= mb_substr($list['board_subject'],0,$cut_nums,'UTF-8');
		$list['register_date'] = date('Y-m-d',$list['register_date']);
		$row[]=$list;
	}
	return $row;
}

//商品列表处理
function goods_array($list)
{
	$list['url'] = GetBaseUrl('product',$list['uid'],'action',$list['supplier_id']);

	$list['goods_file1'] = ProcImgPath($list['goods_file1']);
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

function currency($price)
{
	global $mm_price_sign;
	!$mm_price_sign && $mm_price_sign='￥';
	$price=floatval($price);
	$price=number_format($price,2);
	
	return $mm_price_sign.$price;
}

function sms_send($mobile,$send_str,$query='sms_send2.do')
{
    global $mm_sms_system_id,$mm_sms_system_pass,$mm_sms_tel;
	if(!function_exists('curl_init')) return '请先加载curl函数库';
	if(!function_exists('mb_convert_encoding')) return '请先加载MB编码转换库';
	$send_str=trim($send_str);
	if(!$send_str) return '请填写短信发送内容';
	$corp_service = '1069003270222';
    $msg_id='';//date('ymdhis',time()).rand(10000,99999);//随机生成一个唯一字符串
	$ext='';
	//$send_str.="【{$mm_sms_tel}】";
	$send_str=mb_convert_encoding($send_str,'GBK','UTF-8');
	$host='http://cloud.hbsmservice.com:8080/'.$query;  
    $data_sms='corp_id='.$mm_sms_system_id.'&corp_pwd='.$mm_sms_system_pass.'&corp_service='.$corp_service.'&mobile='.$mobile.'&msg_content='.$send_str.'&corp_msg_id='.$msg_id.'&ext='.$ext;
    $opts = array (
     'http' => array (
     'method' => 'POST',
     'header'=> 'Content-type: application/x-www-form-urlencoded;charset=GBK',//此处的编码和内容编码保持一直
     'Content-Length: ' . strlen($data_sms) . 'rn',
     'content' => $data_sms,
     'timeout'=>60,
      )
     );
     
    $context = stream_context_create($opts);
    $str_rec = file_get_contents($host, false, $context);
    
    //$str_rec=$str_rec;
    if($str_rec>8) $str_rec="发送失败，失败代码：$str_rec 请联系管理员";
    elseif($query=='sms_send2.do') $str_rec="发送成功";
    
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
	$db->free_result(1);
	//发送短信
	return sms_send($send_tel,$send_str);
}

function inner_sms_send($from_id,$to_id,$title,$content,$is_broadcast=0)
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

function template($template,$EXT="html")
{
	global $mm_skin_name,$tpl_path,$is_mobile,$imgpath;
	$path = "templates/$mm_skin_name/$template.$EXT";
	if(!file_exists($path))
	{
	    if($is_mobile)
	    {
	        $path="templates/default_wap/$template.$EXT";
	    }
	    else $path="templates/default/$template.$EXT";
	}
	return $path;
}

function GetBaseUrl($name,$key='',$act='action',$supid='')
{
	global $rewrite,$mm_subdomain,$mm_mall_url,$db_mem_cache;
	$linkurl='';
	$supid=intval($supid);
	if($mm_subdomain==1)    //使用二级域名
	{
		$url=$db_mem_cache->op(0,'Id2Domain',$supid);
		if($rewrite==1) $linkurl=$key==''?"$name.html":"$name-{$key}-1.html";
		else $linkurl=$key==''?"$name.php":"$name.php?$act=$key";

		$linkurl=$url.$linkurl;
	}
	else    //不使用二级域名
	{
		if($supid==0 || $supid=='')    //主站的链接
		{
			if($rewrite==1)    //静态化
			{
				if($key!=='') $linkurl="$name-$key-1.html";
				else $linkurl="$name.html";
			}
			else
			{
				$linkurl="$name.php";
				if($key!=='') $linkurl.="?$act=$key";
			}
		}
		else    //商铺的链接
		{
			if($rewrite==1)    //静态化
			{
				if($key!=='') $linkurl.="union/$name-$key-$supid-1.html";
				else $linkurl="union/$name-$supid.html";
			}
			else
			{
				$linkurl="union/$name.php?supid=$supid";
				if($key!=='') $linkurl.="&$act=$key";
			}
		}
		$linkurl=$mm_mall_url.'/'.$linkurl;
	}
	return $linkurl;
}

function drop_menu($date,$name,$id='')
{
	$str = " <select name='$name' id='$name'>";
	foreach ($date as $key=>$val)
	{
		$chk = $key==$id ? 'selected=selected':'';
		$str.="<option value='$key'  $chk >$val</option>";
	}
	$str .= '</select>';
	return $str;
}

function footer($require_footer=true)
{
	global $mm_mall_address,$mm_company_num,$lang_powered_mvmmall,$nav_foot,$nav2_help,$mm_footer_code,$mm_wx_logo;
	$mm_company_num && $mm_company_num = " <a href=\"http://www.miibeian.gov.cn\" target=\"_blank\">$mm_company_num</a>";
    if(!$mm_wx_logo || !file_exists($mm_wx_logo)) $mm_wx_logo='images/noimages/nowx.jpg';
	$mm_footer_code = stripslashes($mm_footer_code);
	if($require_footer) include_once template('footer');
	$output = str_replace(array('<!--<!---->','<!---->-->','<!---->','<!--'.PHP_EOL.'-->-->','<!--'.PHP_EOL.'-->'.PHP_EOL.'-->'),'',ob_get_contents());
	ob_end_clean();
	exit($output);
}

function cat_menu($name,$select=0,$supplier_id=0,$cat_data=false)
{
	global $ucache;
	$category=$cat_data;
	if(!is_array($category)) $category = $ucache->get_cache('tree',$supplier_id);
	$all_str = "<select name=\"$name\" id=\"$name\"><option selected value=0>----</option>";
	foreach ($category as $key=>$val)
	{
		$all_str.= " <option value=".$key.' ';
		$key==$select && $all_str.= '  selected';
		$all_str.= ">" .str_repeat( "---", $val[2]) . " " . $val[0] . "</option>";
	}
	$all_str .= '</select> ';
	return $all_str;
}

function is_install()
{
	if(!file_exists('config/install.lock')) move_page('install');
}

function ProcImgPath($img_path,$tag='product',$website=IMG_URL)    //处理路片路径
{
    //if(strstr($img_path,'http://')) return $img_path;
	$img_path=strstr($img_path,'union/')?$img_path:'union/'.$img_path;	$img_path=$website!=''?$website.$img_path:$img_path;
	if(!$img_path) $img_path="images/noimages/no{$tag}.jpg";
	return $img_path;
}

function DeleteDir($dir)
{
	$dh=opendir($dir);
	while ($file=readdir($dh))
	{
		if($file=='.' || $file=='..') continue;
		$fullpath=$dir.$file;
		file_unlink($fullpath);
		if(is_dir($fullpath))
		{
			$fullpath.='/';
			DeleteDir($fullpath);
			rmdir($fullpath);
		}
	}
	closedir($dh);
}

function getSupplierFolderSize($page_member_id)
{
	return getDirSize("union/shopimg/user_img/$page_member_id/")+getDirSize("union/shopimg/gallery/$page_member_id/");
}

function getDirSize($dir)
{
	$sizeResult=0;
	$handle = opendir($dir);
	while ($FolderOrFile = readdir($handle))
	{
		if($FolderOrFile != "." && $FolderOrFile != "..")
		{
			if(is_dir("$dir/$FolderOrFile")) $sizeResult += getDirSize("$dir/$FolderOrFile");
			else $sizeResult += filesize("$dir/$FolderOrFile");
		}
	}
	closedir($handle);
	return $sizeResult;
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
	$db->free_result(1);
	
	$db->update("`{$tablepre}member_table`",$member_row," uid='$member[uid]'");
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
	$db->free_result(1);
	
	$db->update("`{$tablepre}member_table`",$member_row," uid='$member[uid]'");
}

function add_xb($m_uid,$money,$type,$reason,$money_sess='',$other_info='')
{
    global $db,$tablepre,$m_now_time,$m_user_ip,$m_check_id;
    
    $money=floatval($money);
    if($money==0) return ;
    
    $m_uid=(int)$m_uid;
	if($m_uid<=0) return ;
	$member=$db->get_one("SELECT m_uid,m_id,xb_money,certified_type FROM `{$tablepre}member_shop` WHERE m_uid='$m_uid' LIMIT 1");
	if(!$member) return ;    //检索不到会员
	
	$member_money=$member['xb_money']+$money;
	if($member_money<0) $member_money=0;
	$member_row=array(
	    'xb_money'=>$member_money,
	    'certified_type'=>$member['certified_type'] | 4
	);
	if(!$money_sess) $money_sess=$m_now_time;

	//加预付款记录
	$sql = "INSERT INTO `{$tablepre}xb_money` (type,money_id,money_add,money_reason,money_sess,money_left,modify_id,modify_ip,register_date,approval_date,other_info) 
	        VALUES ('$type','$member[m_id]','$money','$reason','$money_sess','$member_money','$m_check_id','$m_user_ip','$m_now_time','$m_now_time','$other_info')";
	$db->query($sql);
	$db->free_result(1);
	
	$db->update("`{$tablepre}member_shop`",$member_row," m_uid='$member[m_uid]'");
}

function cur_member_info($m_uid)
{
    global $db,$tablepre;
    
    $m=$db->get_one("SELECT uid,member_id,member_money,member_point,member_email FROM `{$tablepre}member_table` 
                     WHERE uid='$m_uid' 
                     LIMIT 1");
    return $m;
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

function goods_table($type)
{
    global $tablepre;
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

function get_brands_by_cat($cat_uid)
{
    global $cache;
    $cat_uid=(int)$cat_uid;
    if($cat_uid<=0) return array();
    
    $cat_2_brand=$cache->read_cache("data/cache/cat_2_brand_{$cat_uid}.cache.php",
                                    'get_brands_by_cat_from_db',
                                    $cat_uid,
                                    "cat_2_brand");
    
    return $cat_2_brand;
}

function get_brands_by_cat_from_db($cat_uid)
{
    global $db,$tablepre;
    if(file_exists($cache_file))
    {
        include $cache_file;
        return $cat_2_brand;
    }
    
    if(!isset($GLOBALS['uid_2_pid']))
    {
        include 'data/malldata/category.config.php';
        include 'data/malldata/category_pid.config.php';
        require_once 'include/cat_func.func.php';
        
        $GLOBALS['cat']=$cat;
        $GLOBALS['uid_2_pid']=$uid_2_pid;
    }
    
    $arr_cat_uid=get_chidldren_uids($cat_uid,$GLOBALS['uid_2_pid'],$GLOBALS['cat']);
    array_push($arr_cat_uid,$cat_uid);
    $str_cat_uid=implode(',',$arr_cat_uid);
    $cat_2_brand=array();
    $q=$db->query("SELECT id,brandname,logo FROM `{$tablepre}brand_table` FORCE INDEX (`isCheck`)
                   WHERE isCheck=1 AND category_id IN($str_cat_uid) 
                   ORDER BY category_id DESC");
    while ($rtl=$db->fetch_array($q))
    {
        if(!$rtl['logo'] || !file_exists($rtl['logo'])) $rtl['logo']='images/noimages/noproduct.jpg';
        $rtl['url']="brand.php?action=view&id=$rtl[id]";
        $cat_2_brand[]=$rtl;
    }
    $db->free_result();
    return $cat_2_brand;
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
        $v_tmp=substr_replace($v_tmp,'',$start,$end-$start-6);
        $cnt=str_replace($val,$v_tmp,$cnt);
        
    }
    return $cnt;
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

function create_key($key)
{
    global $sessionID,$m_user_ip;
    return "{$m_user_ip}_{$sessionID}_$key";
}

function page_shutdown()
{
    global $db_mem_cache;
    
    foreach ($GLOBALS['mvm_lock'] as $key=>$val)
    {
        $db_mem_cache->op(0,'lock',$key,'lock',db_memory_cache::UNLOCK );
    }
}