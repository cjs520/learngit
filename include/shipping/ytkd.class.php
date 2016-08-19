<?php
if(!defined('MVMMALL')) exit('Access Denied');

/* 配送方式插件的代码必须和文件名保持一致 */
$shipping['ytkd']['name']    = '圆通快递';

/* 配送方式的描述 */
$shipping['ytkd']['desc']    = '圆通快递';

/* 版权信息*/
$shipping['ytkd']['license']  = '版权所有www.mvmmall.cn';

/* 配送接口需要的参数 */
$shipping['ytkd']['cfg'] = array(
array('name' => 'base_fee', 'value'=>15,'label'=>'1000克以内费用'),
array('name' => 'step_fee', 'value'=>2,'label'=>'续重每1000克或其零数'),
array('name' => 'free_money', 'value'=>1000,'label'=>'免费额度')
);

class ytkd
{
    var $cfg;
    
    /*配置信息*/
    function ytkd($cfg = array())
    {
         foreach ($cfg as $key=>$val)
        {
            $this->cfg[$val['name']] = $val['value'];
        }
    }
    /*计算费用*/
    function exes($kg,$price)
    {
        if ($this->cfg['free_money'] > 0 && $price >= $this->cfg['free_money'])
        {
            return 0;
        }
        else
        {
            $fee = $this->cfg['base_fee'];
            if ($kg > 1000)
            {
                $fee += (ceil(($kg - 1000)/1000)) * $this->cfg['step_fee'];
            }
            return $fee;
        }
    }
}