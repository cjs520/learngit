<?php
/**
 * MVM_MALL 网上商店系统
 * ============================================================================
 * 版权所有 (C) 2007-2018 www.mvmmall.cn，并保留所有权利。
 * 网站地址: http://www.mvmmall.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 
 * @author :     www.mvmmall.cn <admin@mvmmall.cn> 
 * @version :    v4.X
---------------------------------------------
 */
if(!defined('MVMMALL')) exit('Access Denied');

$payment['COD']['name'] = '货到付款';    //插件的代码必须和文件名保持一致
$payment['COD']['desc'] = '货到付款';    //描述
$payment['COD']['reg'] = '0';    //申请地址
$payment['COD']['license'] = 'www.mvmmall.cn';    //版权信息
$payment['COD']['cfg'] ='';    //接口需要的参数
    
class COD
{
    var $cfg;
    
    function  COD($cfg = array())
    {
        foreach ($cfg as $key=>$val) $this->cfg[$val['name']] = $val['value'];
    }

    function pay_send($sn,$amount){}    //提交支付请求
    
    function pay_receive(){}    //提交返回处理
}