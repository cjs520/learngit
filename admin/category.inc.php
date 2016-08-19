<?php

/**
 * MVM_MALL 网上商店系统 商品分类管理
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author:  www.mvmmall.cn  $
 * $Date: 2008-02-03 $
 * $Id: category.inc.php  www.mvmmall.cn$
 * ---------------------------------------------
*/

if(!defined('MVMMALL') || !defined('IN_ADMINCP')) exit('Access Denied');


if ($action=='list')
{
    $arr_cat=array();
    $arr_cat=$db->get_all("SELECT uid,category_id,category_name,category_key,category_rank FROM `{$tablepre}category` 
                           WHERE supplier_id='0' AND category_id='0' 
                           ORDER BY category_rank");
    $db->free_result();
    require_once template('category');
    footer();
}
else if($action=='add')
{
    if($_POST && (int)$step==1)
    {
        $category_rank=(int)$category_rank;
        if ($_FILES['category_file1']['name']!='')
        {
            require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp',"images/category/");
            $category_file1_text = $f->upload('category_file1');
        }
        
        $category_id = 0;
        foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $category_id=$val;
        }
        
        $att_list=serialize_attr($att_name,$att_value);
        
        $rate=floatval($rate);
        if($rate<0 || $rate>1) $rate=0;
        
        $row=array(
            'category_id'=>$category_id,
            'category_name'=>$_POST['category_name'],
            'category_key'=>$category_key,
            'category_desc'=>$category_desc,
            'category_rank'=>$category_rank,
            'category_file1'=>$category_file1_text,
            'att_list'=>$att_list,
            'rate'=>$rate
        );
        $insert_id=$db->insert("`{$tablepre}category`",$row);
        $db->free_result();
        $cache->delete('right_tree',0);
        admin_log("添加分类：$_POST[category_name]");
        iframe_callback('添加成功',"add||$insert_id||$_POST[category_name]||$category_id||$category_rank");
    }

    $att_val=array();
    require_once template('category_add');
    footer(false);
} 
else if($action=='edit')
{
    $uid=(int)$uid;
    $cat_rt = $db->get_one("SELECT * FROM `{$tablepre}category` WHERE uid='$uid' LIMIT 1");
    if(!$cat_rt) show_msg('检索不到指定分类');
    
    if($_POST && (int)$step==1)
    {
        $category_rank=(int)$category_rank;
        $category_file1_text = $cat_rt['category_file1'];
        if ($_FILES['category_file1']['name']!='')
        {
        	file_unlink(str_replace(IMG_URL, '', $category_file1_text),'bucket');
            require_once 'include/upfile.class.php';
            $f = new upfile('gif,jpg,png,bmp',"images/category/");
            $category_file1_text = $f->upload('category_file1');
        }
        
        $category_id = 0;
        foreach ($goods_cat as $val)
        {
            $val=(int)$val;
            if($val>0) $category_id=$val;
        }
        if($category_id==$uid) show_msg('不能选择自己为上级分类');
        
        $att_list=serialize_attr($att_name,$att_value);
        
        $rate=floatval($rate);
        if($rate<0 || $rate>1) $rate=0;
        
        $sql = "UPDATE `{$tablepre}category` SET
                category_id = '$category_id',
                category_name = '$_POST[category_name]',
                category_key = '$category_key',
                category_desc = '$category_desc',
                category_rank = '$category_rank',
			    category_file1 = '$category_file1_text',
                att_list='$att_list',
                rate = '$rate' 
                WHERE uid = '$uid'";
        $db->query($sql);
        admin_log("编辑分类：$_POST[category_name]");
        $cache->delete('right_tree',0);
        iframe_callback('分类修改成功',"edit||$uid||$_POST[category_name]||$category_id||$category_rank");
    }
    
    @extract($cat_rt,EXTR_OVERWRITE);
    $cat_img = $category_file1 ? 
               "<a href='javascript:;' onclick=\"remove('$uid','admincp.php?module=category&action=del&type=delimg&uid=$uid');\"><img src=\"images/admincp/delet.gif\" border=\"0\"></a>":
               '';
    
    $att_list=unserialize($att_list);
    
    require_once template('category_add');
    footer(false);
}
else if($action=='load_sub')
{
    $uid=(int)$uid;
    if($uid<=0) exit($json->encode(array('err'=>'父类ID不正确')));
    $arr_cat=$db->get_all("SELECT uid,category_id,category_name,category_rank 
                           FROM `{$tablepre}category` WHERE category_id='$uid' ORDER BY category_rank DESC");
    
    exit(json_encode($arr_cat));
}
else if($action=='correct')
{
    $q=$db->query("SELECT uid,category_id FROM `{$tablepre}category` WHERE supplier_id='0'");
    $arr_cat=array();
    while($rtl=$db->fetch_array($q)) $arr_cat[$rtl['uid']]=$rtl['category_id'];
    
    $arr_err_uid=array();
    
    foreach ($arr_cat as $key=>$val)
    {
        $cat_uid=$key;
        $arr_chain=array();
        if($key==$val)
        {
            $arr_err_uid[]=$key;
            continue;
        }

        do
        {
            if(in_array($cat_uid,$arr_chain))
            {
                $arr_err_uid[]=$key;
                break;
            }

            array_push($arr_chain,$cat_uid);

            if($cat_uid>0 && !isset($arr_cat[$cat_uid]))
            {
                $arr_err_uid[]=$key;
                break;
            }
            $cat_uid=$arr_cat[$cat_uid];

            if($cat_uid<=0) break;
        }while (true);

        unset($arr_chain);
    }
    
    $msg='恭喜，当前数据完全正确';
    if($arr_err_uid)
    {
        $str_err_uid=implode(',',$arr_err_uid);
        $db->query("DELETE FROM `{$tablepre}category` WHERE uid IN ($str_err_uid)");
        
        $msg='发现'.sizeof($arr_err_uid).'错误数据，已清除。请重新生成缓存';
    }
    admin_log("分类自动纠错：$msg");
    echo $msg;
    exit;
}
else if ($action=='del')
{
    //删除分类图片
    if ($type=='delimg' && is_numeric($uid))
    {
        $rs = $db->get_one("SELECT category_name,category_file1,uid FROM `{$tablepre}category` WHERE uid='$uid'");
        if($rs['category_file1'])
        {
            file_unlink(str_replace(IMG_URL, '', $rs['category_file1']),'bucket');
            $db->query("UPDATE `{$tablepre}category` SET category_file1='' WHERE uid='$uid'");
        }
        admin_log("分删分类图标：$rs[category_name]");
        exit;
    }
    $uid=(int)$uid;
    if($uid<=0) exit('分类ID错误');
    //删除分类
    $cat_rt = $db->get_one("SELECT uid,category_name FROM `{$tablepre}category` WHERE category_id='$uid'");
    if($cat_rt) exit('有子分类存在，不能删除');
    //改动商品的分类
    
    $cat_rt=$db->get_one("SELECT category_name FROM `{$tablepre}category` WHERE uid='$uid' LIMIT 1");
    if($cat_rt)
    {
        admin_log("删除分类：$cat_rt[category_name]");
        $db->query("UPDATE `{$tablepre}goods_table` SET goods_category='0' WHERE goods_category='$uid'");
        $db->query("DELETE FROM `{$tablepre}category`  WHERE uid = '$uid'");//删除分类
        $db->free_result();
        $cache->delete('right_tree',0);
    }
    exit('OK，删除成功');
}
else if($action=='all_delete')
{
	!is_array($uid_check) && show_msg('pass_worng');
	foreach ($uid_check as $val) delete_menu($val);
	$cache->delete('right_tree',0);
	show_msg('success',"admincp.php?module=$module&action=list");
}
else if($action=='copy_parent')
{
	$uid=(int)$uid;
	$rtl=$db->get_one("SELECT * FROM `{$tablepre}category` WHERE uid='$uid' LIMIT 1");
	if(!$rtl) exit($json->encode(array('err'=>'指不到指定分类')));
	$rtl['category_id']=$rtl['uid'];
	$rtl['uid']=null;
	$rtl['category_name']="复制 $rtl[category_name]";
	$insert_id=$db->insert("`{$tablepre}category`",$rtl);
	$db->free_result();
	admin_log("添加分类：$rtl[category_name]");
    $arr_tmp=array(
        'uid'=>$uid,
        'category_id'=>$rtl['category_id'],
        'category_name'=>$rtl['category_name'],
        'category_rank'=>$rtl['category_rank']
    );
	exit(json_encode($arr_tmp));
}
else if($action=='ajax')
{
	$uid=(int)$uid;
	$uid==0 && exit;
	$field=dhtmlchars($field);
	$v=dhtmlchars($v);
	$db->query("UPDATE `{$tablepre}category` SET `$field`='$v' WHERE uid='$uid'");
}
else if($action=='create_level_cat')
{
    $start_time=microtime(true);
    $arr_rtl=array(    //以父节点为key
        0=>array('data'=>null,'child'=>array())
    );
    $uid_2_pid=array();

    $q=$db->query("SELECT uid,category_id,category_name FROM `{$tablepre}category` WHERE supplier_id='0'");
    while($rtl=$db->fetch_array($q))
    {
        
        if(isset($arr_rtl[$rtl['uid']]))    //判断自己是否是游离节点
        {
            $arr_rtl[$rtl['uid']]['data']=$rtl;
            $node=$arr_rtl[$rtl['uid']];
        }
        else
        {
            $node=create_node($rtl);
        }
        //判断自身父节点是否存在
        if(!isset($uid_2_pid[$rtl['category_id']]) && !isset($arr_rtl[$rtl['category_id']]))    //父节点不存在
        {
            if(!isset($arr_rtl[$rtl['category_id']]))
            {
                $arr_rtl[$rtl['category_id']]=array(
                    'data'=>null,
                    'child'=>array(),
                );
            }
        
            $arr_rtl[$rtl['category_id']]['child'][$rtl['uid']]=$node;
        }
        else if(isset($uid_2_pid[$rtl['category_id']]))    //父节点存在：登记
        {
            $parents=get_parents($rtl['category_id'],$uid_2_pid);
        
            $p_node=array();
            foreach ($parents as $val)
            {
                if(!$p_node) $p_node[]=&$arr_rtl[$val];
                else $p_node[]=&$p_node[sizeof($p_node)-1]['child'][$val];
            }
       
            if(!$p_node)
            {
                exit('p node error');
            }
            $p_node[sizeof($p_node)-1]['child'][$rtl['uid']]=$node;
            unset($p_node);    
        }
        else if(isset($arr_rtl[$rtl['category_id']]))    //父节点存在：游离
        {
            $arr_rtl[$rtl['category_id']]['child'][$rtl['uid']]=$node;
        }
        else    
        {
            exit('error');
        }
    
        //如果自己原本是游离结点，这时已找到挂的地方，删除那个游离节点 
        if(isset($arr_rtl[$rtl['uid']])) unset($arr_rtl[$rtl['uid']]);
    
        //登记本节点
        $uid_2_pid[$rtl['uid']]=(int)$rtl['category_id'];
    }

    //记录层级分类数据
    $data=var_export($arr_rtl,true);
    $fp=fopen('data/malldata/category.config.php','wb+');
    if($fp)
    {
        fwrite($fp,'<?php $cat=');
        fwrite($fp,$data);
        fwrite($fp,'; ?>');
    }
    fclose($fp);
    
    //记录父节点数据
    file_put_contents('data/malldata/category_pid.config.php','<?php $uid_2_pid='.var_export($uid_2_pid,true).'; ?>');
    admin_log("更新分类列表");
    echo '生成成功，总用时:',strval(microtime(true)-$start_time).'S';
    exit;
}
else show_msg('pass_worng');


function delete_menu($id=0)
{
	global $db,$tablepre;
	if((int)$id==0) return;
	$cat_rt = $db->get_all("SELECT uid,category_name FROM `{$tablepre}category` WHERE category_id='$id'");
	
    $rt = $db->get_one("SELECT uid,category_name,category_file1 FROM `{$tablepre}category` WHERE uid = '$id'");
    if($rt && $rt['category_file1']) file_unlink($rt['category_file1']);
    $db->query("DELETE FROM `{$tablepre}category` WHERE uid = '$id'");
    $db->free_result();
    admin_log("删除分类：$rt[category_name]");
    
    foreach($cat_rt as $val)
	{
		delete_menu($val['uid']);
	}
}

function create_node($data)
{
    return array('data'=>$data,'child'=>array());
}

function get_parents($uid,$uid_2_pid)
{
    $tmp_uid=$uid;
    $parents=array();
    while(isset($tmp_uid))
    {
        array_push($parents,$tmp_uid);
        $tmp_uid=$uid_2_pid[$tmp_uid];
    }
    $parents=array_reverse($parents);
    return $parents;
}

function serialize_attr($arr_name,$arr_value)
{
    $arr_rtl=array();
    if(!is_array($arr_name) || !is_array($arr_value)) return '';
    if(!$arr_name) return '';
    
    $arr_value_bucket=array();
    foreach ($arr_value as $key=>$val)
    {
        $arr_key_part=explode('_',$key);
        $arr_key_part[0]=(int)$arr_key_part[0];
        
        $arr_value_bucket[$arr_key_part[0]][]=$val;
    }
    foreach ($arr_name as $key=>$val)
    {    
        $arr_rtl[$val]=$arr_value_bucket[$key];
    }
    
    return serialize($arr_rtl);
}
?>