<?php
/**
 * CMyFrame 内核基类
 * @version 2.0.1 by 2012.7.3
 * @copyright 2012 uncleChen 
*/
require(FRAME_PATH.'/boot/CInitApplication.php');

Class CApplication extends CInitApplication
{
	/**
	 * 请求对象
	 */
	private $_objectRequest;
	
	/**
	 * 获取请求并处理
	 */
	public function GetRequest(){
		
		//处理请求
		ob_start();
		CRequest::getInstance()->run();
		
		//发送响应
		CResponse::getInstance()->send();
		ob_end_flush();
	}
	
	public function closeErrorsShow(){
		
	}
	
	/**
	 * 设置程序内存限制
	 */
	public static function setMemoryLimit($size = '1024M'){
		ini_set('memory_limit',$size);
	}
	
	/**
	 * 设置程序超时时间
	 */
	public static function setTimeLimit($time = 0){
		set_time_limit($time);
	}
} 