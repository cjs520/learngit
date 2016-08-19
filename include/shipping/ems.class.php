<?php

if(!defined('MVMMALL')) exit('Access Denied');

$shipping['ems']['name'] = 'EMS国内邮政特快专递';    //配送方式插件的代码必须和文件名保持一致
$shipping['ems']['desc'] = 'EMS 国内邮政特快专递';    //配送方式的描述
$shipping['ems']['license'] = '版权所有www.mvmmall.cn';    //版权信息
/* 配送接口需要的参数 */
$shipping['ems']['cfg'] = array(
    array('name' => 'base_fee', 'value'=>20,'label'=>'1000克以内费用'),
    array('name' => 'step_fee', 'value'=>10,'label'=>'续重每1000克或其零数'),
    array('name' => 'free_money', 'value'=>1000,'label'=>'免费额度')
);

class ems
{
    var $cfg;
    
    function ems($cfg = array())
    {
    	foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    
    function exes($kg,$price)    //计算费用
    {
        if ($this->cfg['free_money'] > 0 && $price >= $this->cfg['free_money']) return 0;
        
        $fee = $this->cfg['base_fee'];
        if ($kg > 1000)  $fee += (ceil(($kg - 1000) / 1000)) * $this->cfg['step_fee'];
        
        return $fee;
    }
}