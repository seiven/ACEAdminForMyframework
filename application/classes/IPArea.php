<?php
/**
 * UncleChen
 * IP归属地
 * @copyright UncleChen 2013
 * @author UncleChen
 * @version UncleChen v 0.0.1 2013/1/17
 */
class IPArea
{
	/**
	 * 使用sina接口
	 */
	public static function getArea($ip = null){
		
		//ip为空时使用请求ip
		if(empty($ip)){
			$ip = CRequest::getIp();
		}
		
		$api = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=';
		$requestUrl = $api.$ip;
		
		//发送http请求
		$content = CResponse::sendHttpRequest($requestUrl);
		
		//请求错误
		if($content['info']['http_code'] != 200){
			return '';
		}
		
		$content = json_decode($content['content'],true);
		$result = (isset($content['province']) ? $content['province'] : '').' '.(isset($content['city']) ? $content['city'] : '');
		
		return $result;
	}
	
	/**
	 * 判断是否内部IP
	 */
	public static function isInternalIP($ip = null){
		
		if(empty($ip)){
			$ip = CRequest::getIp();
		}
		
		$internalIp = array('125.71.211.185');
		
		//获取内部IP
		$setIP = CConfig::getInstance('site')->load('ipList');
		if(!empty($setIP) && is_array($setIP) ){
			$internalIp = $setIP;
		}
		
		if(in_array($ip,$setIP)){
			return true;
		}
		
		return false;
	}
	
}