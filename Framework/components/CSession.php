<?php
/**
 * CMyFrame Session操作
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/
class CSession
{
	/**
	 * 设置
	 */
	public static function set($key,$val){
		$_SESSION[$key] = $val;
	}
	
	/**
	 * 返回
	 */
	public static function get($key){	
		
		//存在子键
		if(false != strpos($key,'.')){
			$keyArr = explode('.',$key);
			
			$session = $_SESSION;
			foreach($keyArr as $val){
				if(isset($session[$val])){
					$session = $session[$val];
				}else{
					return null;
				}
			}
			
			return $session;
		}else{
			return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
		}	
	}
	
	/**
	 * 删除Session
	 */
	public static function del($key){
		if(isset($_SESSION[$key])){
			unset($_SESSION[$key]);
		}
	}
}