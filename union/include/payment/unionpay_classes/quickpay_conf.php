<?php

define ('QUICKPAY_PAY_SEVICE', 1);
define ('QUICKPAY_NOTIFY_SEVICE', 2);

// set the default timezone to use. Available since PHP 5.1
if (function_exists("date_default_timezone_set")) {
	date_default_timezone_set('Asia/Shanghai');
}

class quickpay_conf
{
    //public static $GATE_WAY        = "https://www.epay.lxdns.com/UpopWeb/api/Pay.action";
    public static $GATE_WAY        = "https://unionpaysecure.com/api/Pay.action";
    public static $SIGN_TYPE       = "MD5";
    public static $SECURITY_KEY    = "88888888";
    public static $FILE_CHARSET    = "UTF-8";

    public static $ARR_SP_CONF = array(
        'version'       => '1.0.0',
        'charset'       => 'UTF-8',
        'merId'         => '422130148990006',
        'merAbbr'       => 'merAbbr',
        'acqCode'       => '',
        'merCode'       => '',
        );

    public static $ARR_PAY_PARAMETER = array(
            "transType",
            "origQid",
            "commodityUrl",
            "commodityName",
            "commodityUnitPrice",
            "commodityQuantity",
            "commodityDiscount",
            "transferFee",
            "orderNumber",
            "orderAmount",
            "orderCurrency",
            "orderTime",
            "customerIp",
            "customerName",
            "defaultPayType",
            "defaultBankNumber",
            "transTimeout",
            "frontEndUrl",
            "backEndUrl",
            "merReserved",
            );
    public static $ARR_NOTIFY_PARAMETER = array(
            "charset",
            "cupReserved",
            "exchangeRate",
            "exchangeDate",
            "merAbbr",
            "merId",
            "orderAmount",
            "orderCurrency",
            "orderNumber",
            "qid",
            "respCode",
            "respMsg",
            "respTime",
            "settleAmount",
            "settleCurrency",
            "settleDate",
            "transType",
            "version",
            "traceNumber",
            "traceTime",
            );
}

?>
