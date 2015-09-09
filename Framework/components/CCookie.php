<?php
/**
 * CMyFrame Cookie操作
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/
class CCookie
{
	/**
	 * 设置
	 */
	public static function set($key,$val,$limitTime = 3600){
		
		//对Val加密
		$val = CEncrypt::encode($val);	
		setcookie($key,$val,time() + $limitTime ,'/');
	}
	
	/**
	 * 返回
	 */
	public static function get($key){	
		
		if(isset($_COOKIE[$key])){
			return CEncrypt::decode($_COOKIE[$key]);
		}
		
		return null;
	}
	
	/**
	 * 删除
	 */
	public static function del($key){
		
		setcookie($key,'', time() - 3600);
	}
}