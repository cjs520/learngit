<?php

namespace wepay\join\demo\common;

include dirname(__FILE__).'/RSAUtils.php';

/**
 * 签名
 *
 * @author wylitu
 *        
 */
class SignUtil {
	
	public static $unSignKeyList = array (
			"merchantSign",
			"version", 
			"successCallbackUrl",
			"forPayLayerUrl"
	);
	public static function signWithoutToHex($params) {
		ksort($params);
  		$sourceSignString = SignUtil::signString ( $params, SignUtil::$unSignKeyList );
  		error_log($sourceSignString, 0);
  		$sha256SourceSignString = hash ( "sha256", $sourceSignString,true);	
  		error_log($sha256SourceSignString, 0);
        return RSAUtils::encryptByPrivateKey ($sha256SourceSignString);
	}
	
	public static function sign($params,$unSignKeyList=false) {
		ksort($params);
		$sourceSignString = SignUtil::signString ( $params,$unSignKeyList?$unSignKeyList:SignUtil::$unSignKeyList );
		error_log($sourceSignString, 0);
		$sha256SourceSignString = hash ( "sha256", $sourceSignString);
		error_log($sha256SourceSignString, 0);
		return RSAUtils::encryptByPrivateKey ($sha256SourceSignString);
	}
	
	public static function signString($params, $unSignKeyList) {
		
		// 拼原String
		$sb = "";
		// 删除不需要参与签名的属性
		foreach ( $params as $k => $arc ) {
			for($i = 0; $i < count ( $unSignKeyList ); $i ++) {
				
				if ($k == $unSignKeyList [$i]) {
					unset ( $params [$k] );
				}
			}
		}
		
		foreach ( $params as $k => $arc ) {
			
			$sb = $sb . $k . "=" . ($arc == null ? "" : $arc) . "&";
		}
		// 去掉最后一个&
		$sb = substr ( $sb, 0, - 1 );
		
		return $sb;
	}
}

//echo SignUtil::sign ( $params );

?>