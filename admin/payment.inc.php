<?php

/**
 * MVM_MALL 网上商店系统  支付插件管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-07-14 $
 * $Id: payment.inc.php www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');
if ($action=='list')
{
    $arr_php_version=explode('.',PHP_VERSION);
    $php_version=floatval($arr_php_version[0].'.'.$arr_php_version[1]);
    
    $list = get_dirinfo('include/payment','.class.php');
    $size_list=sizeof($list);
    for ($i = 0; $i < $size_list; $i++)
    {
        if($php_version<5.3 && $list[$i]=='jdpay.class.php') continue;
        require_once 'include/payment/'.$list[$i];
        $name = dhtmlchars(str_replace('.class.php','',$list[$i]));
        
        $payment[$name]['payname'] = $name;
        $payment[$name]['insert'] = "admincp.php?module=$module&action=add&name=$name";
        $pay = $db->get_one("SELECT id,class_name,pay_desc,name FROM `{$tablepre}payment_table` 
                             WHERE class_name='$name' AND supplier_id='0' LIMIT 1");
        if($pay['class_name'])
        {
            $payment[$pay['class_name']]['install'] = 1;
            $payment[$pay['class_name']]['id'] = $pay['id'];
            $payment[$pay['class_name']]['desc'] = $pay['pay_desc'];
            $payment[$pay['class_name']]['name'] = $pay['name'];
            $payment[$pay['class_name']]['edit'] = "admincp.php?module=$module&action=edit&uid=$pay[id]";
            $payment[$pay['class_name']]['del'] = "admincp.php?module=$module&action=del&uid=$pay[id]";
        }
    }
    require template('payment');
    footer();
}
elseif ($action=='add' && isset($name))
{
    if($_POST && (int)$step==1)
    {
        $pay = $db->get_one("SELECT id,class_name FROM `{$tablepre}payment_table` WHERE class_name='$name' AND supplier_id='0' LIMIT 1");
        if($pay['class_name']) sadmin_show_msg('支付方式已经存在',$p_url);
        if(!file_exists('include/payment/'.$name.'.class.php')) sadmin_show_msg('添加的支付文件不存在',$p_url);
        
        require_once  'include/payment/'.$name.'.class.php';
        $config = array();
        foreach ($payment[$name]['cfg'] as $key => $val)
        {
            $config[$key]['name'] = $val['name'];
            $config[$key]['value'] = $val['t']=='upload'?$val['value']:$$val['name'];
            $config[$key]['label'] = $val['label'];
            $config[$key]['t'] = $val['t'];
        }
        
        require_once 'include/upfile.class.php';
        foreach ($_FILES as $key=>$val)
        {
            if($val['size']<=0) continue;
            file_unlink("data/malldata/$val[name]");
            $f = new upfile('pem,pfx',"data/malldata/");
            $upload = $f->upload($val,$val['name']);
        }
        
        //针对支付宝wap支付进行处理
        if($name=='alipay')
        {
            //wap public key
            $public_key_data='-----BEGIN PUBLIC KEY-----'.PHP_EOL;
            foreach ($config as $key=>$val)
            {
                if($val['name']=='wap_public_key')
                {
                    $public_key_data.=$val['value'].PHP_EOL;
                    break;
                }
            }
            $public_key_data.='-----END PUBLIC KEY-----';
            file_put_contents('data/malldata/wap_alipay_public_key.pem',$public_key_data);

            //wap private key
            $private_key_data='-----BEGIN RSA PRIVATE KEY-----'.PHP_EOL;
            foreach ($config as $key=>$val)
            {
                if($val['name']=='wap_private_key')
                {
                    for($i=0;$i<strlen($val['value'])-1;$i+=64)
                    {
                        $private_key_data.=substr($val['value'],$i,64).PHP_EOL;
                    }
                    break;
                }
            }
            $private_key_data.='-----END RSA PRIVATE KEY-----';
            file_put_contents('data/malldata/wap_alipay_private_key.pem',$private_key_data);
        }

        $config = serialize($config);
        $db->query("INSERT INTO `{$tablepre}payment_table` SET
                    class_name = '$name',
                    name =  '$pay_name',
                    pay_desc =  '$pay_desc',
                    cfg = '$config'");
        $db->free_result();
        $cache->delete('payment',0);
        move_page(base64_decode($p_url));
    }
    
    $name = dhtmlchars($name);
    require_once 'include/payment/'.$name.'.class.php';
    $fields = array();
    foreach ($payment[$name]['cfg'] as $key => $val)
    {
        $fields[$key]['name'] = $val['name'];
        $fields[$key]['value'] = $val['value'];
        $fields[$key]['label'] = $val['label'];
        $fields[$key]['t'] = $val['t'];
    }
    @extract($payment[$name],EXTR_OVERWRITE);
    require template('payment_add');
    exit;
}
elseif ($action=='edit')
{
    $uid=(int)$uid;
    if($_POST && (int)$step==1)
    {
        $pay = $db->get_one("SELECT id,class_name,name FROM `{$tablepre}payment_table` WHERE id='$uid' LIMIT 1");
        $name = $pay['class_name'];
        $pay_name = dhtmlchars($pay_name);
        if(!file_exists('include/payment/'.$name.'.class.php')) sadmin_show_msg('该支付文件不存在',$p_url);
        
        require_once 'include/payment/'.$name.'.class.php';
        $config = array();
        foreach ($payment[$name]['cfg'] as $key => $val)
        {
            $config[$key]['name'] = $val['name'];
            $config[$key]['value'] = $val['t']=='upload'?$val['value']:$$val['name'];
            $config[$key]['label'] = $val['label'];
            $config[$key]['t'] = $val['t'];
        }
        
        require_once 'include/upfile.class.php';
        foreach ($_FILES as $key=>$val)
        {
            if($val['size']<=0) continue;
            file_unlink("data/malldata/$val[name]");
            $f = new upfile('pem,pfx',"data/malldata/");
            $upload = $f->upload($val,$val['name']);
        }
        
        //针对支付宝wap支付进行处理
        if($name=='alipay')
        {
            //wap public key
            $public_key_data='-----BEGIN PUBLIC KEY-----'.PHP_EOL;
            foreach ($config as $key=>$val)
            {
                if($val['name']=='wap_public_key')
                {
                    $public_key_data.=$val['value'].PHP_EOL;
                    break;
                }
            }
            $public_key_data.='-----END PUBLIC KEY-----';
            file_put_contents('data/malldata/wap_alipay_public_key.pem',$public_key_data);
            
            //wap private key
            $private_key_data='-----BEGIN RSA PRIVATE KEY-----'.PHP_EOL;
            foreach ($config as $key=>$val)
            {
                if($val['name']=='wap_private_key')
                {
                    for($i=0;$i<strlen($val['value'])-1;$i+=64)
                    {
                        $private_key_data.=substr($val['value'],$i,64).PHP_EOL;
                    }
                    //$private_key_data.=$val['value'].PHP_EOL;
                    break;
                }
            }
            $private_key_data.='-----END RSA PRIVATE KEY-----';
            file_put_contents('data/malldata/wap_alipay_private_key.pem',$private_key_data);
        }
        
        $config = serialize($config);
        $query = "UPDATE `{$tablepre}payment_table` SET
                  class_name = '$name',
                  name =  '$pay_name',
                  pay_desc =  '$pay_desc',
                  cfg = '$config'
                  WHERE id = '$uid' ";
        $db->query($query);
        $db->free_result();
        admin_log("修改支付方式：$pay_name");
        
        move_page(base64_decode($p_url));
    }
    $pay = $db->get_one("SELECT * FROM `{$tablepre}payment_table` WHERE id='$uid' LIMIT 1");
    $pay['cfg'] = unserialize($pay['cfg']);
    $fields = array();
    foreach ($pay['cfg'] as $key => $val)
    {
    	$fields[$key]['name'] = $val['name'];
        $fields[$key]['value'] = $val['value'];
        $fields[$key]['label'] = $val['label'];
        $fields[$key]['t'] = $val['t'];
        
        if($val['t']=='upload' && file_exists("data/malldata/$val[name]")) $fields[$key]['tag']='(已上传)';
    }
    $name = $pay['name'];
    $desc = $pay['pay_desc'];
    require template('payment_add');
    exit;
}
else if ($action=='del')
{
    $pay = $db->get_one("SELECT id,class_name,name FROM `{$tablepre}payment_table` WHERE id='$uid' LIMIT 1");
    if($pay)
    {
        admin_log("卸载支付方式：$pay[name]");
        $db->query("DELETE FROM `{$tablepre}payment_table` WHERE id='$uid'");
        $db->free_result();
        $cache->delete('payment',0);
    }
    
    show_msg('success','admincp.php?module=payment&action=list');
}
else show_msg('pass_worng');