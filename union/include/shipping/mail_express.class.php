<?php
if(!defined('MVMMALL')) exit('Access Denied');
    
    
$shipping['mail_express']['name'] = '邮政快递包裹';    //配送方式插件的代码必须和文件名保持一致
$shipping['mail_express']['desc'] = '邮政快递包裹';    //配送方式的描
$shipping['mail_express']['license'] = '版权所有www.mvmmall.cn';    //版权信息
//接口参数
$shipping['mail_express']['cfg'] = array(
    array('name' => 'begin_fee', 'value'=>5,'label'=>'1000克以内费用'),
    array('name' => 'add_fee1', 'value'=>2,'label'=>'5000克以内续重每1000克费用'),
    array('name' => 'add_fee2', 'value'=>1,'label'=>'5001克以上续重1000克费用'),
    array('name' => 'free_money', 'value'=>1000,'label'=>'免费额度')
);

/**
 * 邮政快递包裹费用计算方式
 * ====================================================================================
 * 运距                     首重1000克      5000克以内续重每500克   5001克以上续重500克
 * -------------------------------------------------------------------------------------
 * 500公里及500公里以内     5.00            2.00                    1.00
 * 500公里以上至1000公里    6.00            2.50                    1.30
 * 1000公里以上至1500公里   7.00            3.00                    1.60
 * 1500公里以上至2000公里   8.00            3.50                    1.90
 * 2000公里以上至2500公里   9.00            4.00                    2.20
 * 2500公里以上至3000公里   10.00           4.50                    2.50
 * 3000公里以上至4000公里   12.00           5.50                    3.10
 * 4000公里以上至5000公里   14.00           6.50                    3.70
 * 5000公里以上至6000公里   16.00           7.50                    4.30
 * 6000公里以上             20.00           9.00                    6.00
 * -------------------------------------------------------------------------------------
 * 每件挂号费               3.00
 */
class mail_express
{
    var $cfg;
    
    function mail_express($cfg = array())    //配置信息
    {
    	foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }
    
    function exes($kg,$price)    //计算费用 $kg重量,千克,$price金额
    {
        if ($this->cfg['free_money'] > 0 && $price >= $this->cfg['free_money']) return 0;
        
        $fee = $this->cfg['begin_fee'];
        if ($kg > 5000)
        {
        	$fee += 8 * $this->configure['add_fee1'];
            $fee += (ceil(($kg - 5000) / 500)) * $this->cfg['add_fee2'];
        }
        else if ($kg > 1000) $fee += (ceil(($kg- 1000) / 500)) * $this->cfg['add_fee1'];

        return $fee;
    }
}