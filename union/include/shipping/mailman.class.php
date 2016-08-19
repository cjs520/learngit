<?php
if(!defined('MVMMALL')) exit('Access Denied');

//配送方式插件的代码必须和文件名保持一致
$shipping['mailman']['name']    = '平 邮';

//配送方式的描
$shipping['mailman']['desc']    = '邮局平邮';

//版权信息
$shipping['mailman']['license']  = '版权所有www.mvmmall.cn';

//接口参数
$shipping['mailman']['cfg'] = array(
array('name' => 'begin_fee', 'value'=>3.5,'label'=>'1000克以内费用'),
array('name' => 'add_fee1', 'value'=>2,'label'=>'5000克以内续重每1000克费用'),
array('name' => 'add_fee2', 'value'=>2.5,'label'=>'5001克以上续重1000克费用'),
array('name' => 'pack_fee', 'value'=>0,'label'=>'包装费用'),
array('name' => 'free_money', 'value'=>1000,'label'=>'免费额度')
);
/**
 * 邮局平邮费用计算方式: 每公斤资费 × 包裹重量 + 挂号费3.00 + 邮单费0.5 + 包装费(按实际收取) ＋ 保价费
 *
 * 保价费 由客户自愿选择，保价费为订单产品价值的1％。客户选择不保价，则保价费＝0
 *
 */
class mailman
{
    var $cfg;
    
    /*配置信息*/
    function mailman($cfg = array())
    {
         foreach ($cfg as $key=>$val)
        {
            $this->cfg[$val['name']] = $val['value'];
        }
    }
    //计算费用 $kg重量,千克,$price金额
    function exes($kg,$price)
    {
        if ($this->cfg['free_money'] > 0 && $price >= $this->cfg['free_money'])
        {
            return 0;
        }
        //基本费用
        $fee = $this->cfg['begin_fee'] + $this->cfg['pack_fee'];
        if ($kg > 5000) $fee += (ceil(($kg - 1000)/1000)) * $this->cfg['add_fee2'];
        else if ($kg > 1000) $fee += (ceil(($kg - 1000)/1000)) * $this->cfg['add_fee1'];
        
        return $fee;
        
    }
}