<?php
class CAd
{
	private $module,$cache_path;
	private $db,$tablepre;
	
	public function __construct($db,$tablepre)
	{
		$this->cache_path='data/ad_cache/';
		$this->db=$db;
		$this->tablepre=$tablepre;
		
		$this->module=$GLOBALS['script'];
	}
	
	public function __destruct(){}
	
	public function GetAd($pos,$ad_type,$other_param='',$module='')
	{
		if($module=='') $module=$this->module;
		$ad_type=(int)$ad_type;
		
		$cache_file=$this->cache_path.md5($pos.$ad_type.$other_param.$module);
		$ad=$GLOBALS['cache']->read_cache($cache_file,'CreateNewAd',array('pos'=>$pos,'ad_type'=>$ad_type,'other_param'=>$other_param,'module'=>$module),'ad',$this);
		
		return $ad;
	}
	
	public function CreateNewAd($arr_param)
	{
	    global $m_now_time;
		$filter_sql="WHERE module='$arr_param[module]' AND pos='$arr_param[pos]' AND ad_type='$arr_param[ad_type]'";
		if($arr_param['other_param']!='') $filter_sql.=" AND other_param='$arr_param[other_param]'";

		//$filter_sql.=" AND expire>='$m_now_time' ";
		$ad_list=array();
		$q=$this->db->query("SELECT * FROM `{$this->tablepre}ad_table` $filter_sql ORDER BY ad_order DESC");
		while($rtl=$this->db->fetch_array($q))
		{
			do
			{
				if($rtl['m_id'] && $rtl['expire']>=$m_now_time)    //当前存在展示数据
				{
					$rtl['info']=$rtl['cur_info'];
					break;
				}
				
				$arr_tmp=unserialize($rtl['cur_info']);
				if(is_array($arr_tmp))    //删除旧数据中的图片
				{
					if($rtl['ad_type']==0) file_unlink($arr_tmp['goods_pic'],'bucket');
					else if($rtl['ad_type']==1) file_unlink($arr_tmp['shop_logo'],'bucket');
					else file_unlink($arr_tmp['pic'],'bucket');
				}
				
				$apply_rtl=$this->db->get_one("SELECT uid,m_id,m_uid,info,days FROM `{$this->tablepre}ad_apply` 
				                               WHERE ad_uid='$rtl[uid]' AND approval_date>0 
				                               ORDER BY approval_date 
				                               LIMIT 1");
				if(!$apply_rtl)    //不存在已申核的申请数据
				{
					$this->db->query("UPDATE `{$this->tablepre}ad_table` SET 
					                  cur_info='',
					                  expire='0',
					                  m_id='',
					                  m_uid='0' 
					                  WHERE uid='$rtl[uid]'");
					break;
				}
				
				$expire=$m_now_time+$apply_rtl['days']*24*3600;
				$this->db->query("UPDATE `{$this->tablepre}ad_table` SET 
				                  cur_info='$apply_rtl[info]',
				                  expire='$expire',
				                  m_id='$apply_rtl[m_id]',
				                  m_uid='$apply_rtl[m_uid]' 
				                  WHERE uid='$rtl[uid]'");
				$this->db->query("DELETE FROM `{$this->tablepre}ad_apply` WHERE uid='$apply_rtl[uid]'");
				$rtl['info']=$apply_rtl['info'];
			}while (0);
			
			$ad_info=unserialize($rtl['info']);
			if($rtl['ad_type']==0)    //商品广告
			{
			    if((int)$ad_info['goods_type']==8) $ad_info['goods_price']='展示商品';
				$ad_info['goods_link']=GetBaseUrl(goods_detail_script((int)$ad_info['goods_type']),$ad_info['goods_id'],'action',$ad_info['supplier_id']);
				$ad_info['shop_link']=GetBaseUrl('index','','',$ad_info['supplier_id']);
					
				if(!$ad_info['goods_pic']) $ad_info['goods_pic']='images/noimages/noproduct.jpg';
				else $ad_info['goods_pic']= IMG_URL.$ad_info['goods_pic'];
			}
			else if($rtl['ad_type']==1)    //商铺广告
			{
				$ad_info['shop_link']=GetBaseUrl('index','','',$ad_info['supplier_id']?$ad_info['supplier_id']:$ad_info['member_uid']);
				
				if(!$ad_info['shop_logo']) $ad_info['shop_logo']='images/noimages/nologo.jpg';
				else $ad_info['shop_logo']= IMG_URL.$ad_info['shop_logo'];
			}
			else if($rtl['ad_type']==2)
			{
			    if(!$ad_info['pic']) $ad_info['pic']='images/noimages/noproduct.jpg';
			    else $ad_info['pic']= IMG_URL.$ad_info['pic'];
			}
			
			$ad_list[]=$ad_info;
		}
		
		return $ad_list;
	}
}
?>