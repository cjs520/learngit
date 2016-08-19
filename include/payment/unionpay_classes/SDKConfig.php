<?php
// cvn2加密 1：加密 0:不加密
define(SDK_CVN2_ENC , 0);
// 有效期加密 1:加密 0:不加密
define(SDK_DATE_ENC , 0);
// 卡号加密 1：加密 0:不加密
define(SDK_PAN_ENC , 0);
 

// 签名证书路径 （联系运营获取两码，在CFCA网站下载后配置，自行设置证书密码并配置）
define(SDK_SIGN_CERT_PATH , 'data/malldata/unionpay_cfca.pfx');
 
// 验签证书
define(SDK_VERIFY_CERT_PATH , 'data/malldata/UPOP_VERIFY.cer');     //这一项在代码里面没用到

// 密码加密证书
define(SDK_ENCRYPT_CERT_PATH , 'data/malldata/UPOP_VERIFY.cer');

// 验签证书路径
define(SDK_VERIFY_CERT_DIR , 'data/malldata/');

// 前台请求地址
define(SDK_FRONT_TRANS_URL , 'https://gateway.95516.com/gateway/api/frontTransReq.do');

// 后台请求地址
define(SDK_BACK_TRANS_URL , 'https://gateway.95516.com/gateway/api/backTransReq.do');

// 批量交易
define(SDK_BATCH_TRANS_URL , 'https://gateway.95516.com/gateway/api/batchTrans.do');

//单笔查询请求地址
define(SDK_SINGLE_QUERY_URL , 'https://gateway.95516.com/gateway/api/queryTrans.do');

//文件传输请求地址
define(SDK_FILE_QUERY_URL , 'https://filedownload.95516.com/');

//有卡交易地址
define(SDK_Card_Request_Url , 'https://gateway.95516.com/gateway/api/cardTransReq.do');

//App交易地址
define(SDK_App_Request_Url , 'https://gateway.95516.com/gateway/api/appTransReq.do');

//文件下载目录 
define(SDK_FILE_DOWN_PATH , 'data/malldata/');

//日志 目录 
define(SDK_LOG_FILE_PATH , 'data/malldata/');

//日志级别
define(SDK_LOG_LEVEL , 'DEBUG');
?>